<?php
namespace cmsgears\paypal\rest\common\services\system;

// Yii Imports
use \Yii;

// Paypal Imports
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Refund;
use PayPal\Api\Sale;
use PayPal\Api\ShippingAddress;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

// CMG Imports
use cmsgears\paypal\rest\common\config\PaypalRestProperties;

use cmsgears\paypal\rest\common\services\interfaces\system\IPaypalRestService;

class PaypalRestService implements IPaypalRestService {

	private $properties;
	private $successUrl;
	private $failureUrl;

	public function __construct( $baseUrl = null ) {

		$this->properties = PaypalRestProperties::getInstance();

		if( isset( $baseUrl ) ) {

			$this->successUrl = $baseUrl . '/payment-success';
			$this->failureUrl = $baseUrl . '/payment-failure';
		}
	}

	// Sample Payment ---------------------------------------

    /*
        // Shipping Address
        $shipping_address   = new ShippingAddress();
        $shipping_address->setRecipientName("Test Receipient");
        $shipping_address->setLine1("hi 1");
        $shipping_address->setLine2("hi 2");
        $shipping_address->setCity("Ontario");
        $shipping_address->setState("Toronto");
        $shipping_address->setCountryCode("CA");
        $shipping_address->setPostalCode("M5A4E9");

        // Items
        $items   = array();

        $item   = new Item();
        $item->setName( "Test Item" );
        $item->setQuantity( 2 );
        $item->setCurrency( $currency );
        $item->setPrice( 10.00 );
        $item->setSku(1);

        $items[] = $item;

        $item   = new Item();
        $item->setName( "Test Item" );
        $item->setQuantity( 2 );
        $item->setCurrency( $currency );
        $item->setPrice( 10.00 );
        $item->setSku(1);

        $items[] = $item;

        $item_list = new ItemList();

        $item_list->setItems($items);
        $item_list->setShippingAddress($shipping_address);

        // Details
        $details = new Details();
        $details->setSubtotal( 40.00 );
        $details->setShipping( 5.00 );
        $details->setTax( 2.00 );

        // Amount
        $amount = new Amount();
        $amount->setCurrency($currency);
        $amount->setTotal( 47.00 );
        $amount->setDetails($details);
    */

	// PaypalRestService ------------------------------------

	function isPaymentActive() {

		return $this->properties->isPaymentActive();
	}

	function generateShippingAddress( $addressee, $address ) {

        $address   = new ShippingAddress();

        $address->setRecipientName( $addressee );
        $address->setLine1( $address->line1 );
        $address->setLine2( $address->line2 );
        $address->setCity( $address->city );
        $address->setState( $address->province->name );
        $address->setCountryCode( $address->country->name );
        $address->setPostalCode( $address->zip );

		return $address;
	}

	function generateItemsList( $order ) {

		$currency	= $this->properties->getCurrency();
		$orderItems	= $order->items;
        $items   	= array();

		foreach ( $orderItems as $orderItem ) {

	        $item   = new Item();
	        $item->setName( $orderItem->name );
	        $item->setQuantity( $orderItem->quantity );
	        $item->setCurrency( $currency );
	        $item->setPrice( $orderItem->price );

			if( isset( $orderItem->sku ) ) {

				$item->setSku( $orderItem->sku );
			}

	        $items[] = $item;
		}

        $itemList = new ItemList();

        $itemList->setItems( $items );

		return $itemList;
	}

	public function createPayment( $addressee, $order ) {

		// Order Totals
	    $subTotal     		= $order->total;
		$shippingCharges	= $order->shipping;
		$tax				= $order->tax;
		$grandTotal   		= $order->grandTotal;

		// Get Context
		$context 	= $this->getApiContext();

		// Payer
		$payer 		= new Payer();
		$payer->setPaymentMethod( 'paypal' );

        // Cart Items
        $itemList   = $this->generateItemsList( $order );

        // Shipping Address
        if( $this->properties->isSendAddress() ) {

	        $shippingAddress	= $this->generateShippingAddress( $addressee, $cart );

			$itemList->setShippingAddress( $shippingAddress );
		}

        // Details
        $details = new Details();
        $details->setSubtotal( $subTotal );
        $details->setShipping( $shippingCharges );
        $details->setTax( $tax );

        // Amount
        $amount = new Amount();
        $amount->setCurrency( $this->properties->getCurrency() );
        $amount->setTotal( $grandTotal );
        $amount->setDetails( $details );

		// Transaction
		$transaction = new Transaction();
		$transaction->setAmount( $amount );

		if( isset( $order->description ) ) {

			$transaction->setDescription( $order->description );
		}

		$transaction->setItemList( $itemList );

		// Status URLs
		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl( $this->successUrl );
		$redirectUrls->setCancelUrl( $this->failureUrl );

		// Payment
		$payment = new Payment();
		$payment->setRedirectUrls( $redirectUrls );
		$payment->setIntent( "sale" );
		$payment->setPayer( $payer );
		$payment->setTransactions( [ $transaction ] );

		$payment->create( $context );

		return $payment;
	}

	public function executePayment( $paymentId, $token, $payerId ) {

		$apiContext 		= $this->getApiContext();
		$payment 			= Payment::get( $paymentId, $apiContext );
		$paymentExecution 	= new PaymentExecution();

		$paymentExecution->setPayerId( $payerId );

		try {

			// Execute Payment
			$payment->execute( $paymentExecution, $apiContext );

			// Get Payment
			$payment = Payment::get( $paymentId, $apiContext );

			return $payment;
		}
		catch( Exception $ex ) {

		}

		return null;
	}

	function getSaleId( $payment ) {

		$transactions 	= $payment->getTransactions();

		$transaction	= $transactions[0];

		$resources 		= $transaction->getRelatedResources();
		$sale			= $resources[0]->getSale();
		$saleId 		= $sale->getId();

		/*
		$tran_amount	= $transaction->getAmount();
		$tran_total		= $tran_amount->getTotal();
		$tran_currency	= $tran_amount->getCurrency();
		*/

		return $saleId;
	}

	function getPayment( $paymentId ) {

		$apiContext 	= $this->getApiContext();

		$payment 		= Payment::get( $paymentId, $apiContext );

		return $payment;
	}

	function refundPayment( $saleId, $amount, $currency ) {

		$amount	 		= number_format( $amount, 2 );

		$apiContext 	= $this->getApiContext();

		$sale 			= Sale::get( $saleId, $apiContext );

		$refund 		= new Refund();

		if( isset($amount) && strlen($amount) > 0 ) {

			$amt = new Amount();

			$amt->setCurrency( $currency );
			$amt->setTotal( $amount );

			$refund->setAmount($amt);
		}

		$refund = $sale->refund($refund, $apiContext);

		return $refund;
	}

	function getSale( $saleId ) {

		$apiContext 	= $this->getApiContext();

		$sale 			= Sale::get( $saleId, $apiContext );

		return $sale;
	}

	function isSaleComplete( $saleId ) {

		$apiContext 	= $this->getApiContext();

		$sale 			= Sale::get( $saleId, $apiContext );

		$completed		= strcmp( $sale->getState(), 'completed' ) == 0;

		return $completed;
	}

	function getLink( array $links, $type ) {

		foreach( $links as $link ) {

			if( $link->getRel() == $type ) {

				return $link->getHref();
			}
		}

		return "";
	}

	function parseApiError($errorJson) {

		$msg 	= '';
		$data 	= json_decode($errorJson, true);

		if( isset( $data['name'] ) && isset( $data['message'] ) ) {

			$msg .= $data['name'] . " : " .  $data['message'] . "<br/>";
		}

		if( isset($data['details']) ) {

			$msg .= "<ul>";

			foreach( $data['details'] as $detail ) {

				$msg .= "<li>" . $detail['field'] . " : " . $detail['issue'] . "</li>";
			}

			$msg .= "</ul>";
		}

		if($msg == '') {

			$msg = $errorJson;
		}

		return $msg;
	}

	// SDK Configuration ------------------------------------

	private function getApiContext() {

		$apiContext = null;

		if( strcmp( $this->properties->getStatus(), 'sandbox' ) == 0 ) {

			$apiContext = new ApiContext( new OAuthTokenCredential( $this->properties->getSandboxClientId(),  $this->properties->getSandboxSecret() ) );
		}
		else if( strcmp( $this->properties->getStatus(), 'live' ) == 0 ) {

			$apiContext = new ApiContext( new OAuthTokenCredential( $this->properties->getLiveClientId(),  $this->properties->getLiveSecret() ) );
		}

		$apiContext->setConfig([
			'http.ConnectionTimeOut' => 90,
			'http.Retry' => 1,
			'mode' => $this->properties->getStatus(),
			'log.LogEnabled' => true,
			'log.FileName' => Yii::getAlias( '@frontend' ) . '/runtime/paypal.log',
			'log.LogLevel' => 'INFO'
		]);

		return $apiContext;
	}
}
