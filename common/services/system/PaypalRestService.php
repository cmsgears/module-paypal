<?php
namespace cmsgears\paypal\rest\common\services\system;

// Yii Imports
use Yii;

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

class PaypalRestService extends \yii\base\Component implements IPaypalRestService {

	// Variables ---------------------------------------------------

	// Globals -------------------------------

	// Constants --------------

	// Public -----------------

	// Protected --------------

	// Variables -----------------------------

	// Public -----------------

	// Protected --------------

	// Private ----------------

	private $properties;

	private $successUrl;
	private $failureUrl;

	// Traits ------------------------------------------------------

	// Constructor and Initialisation ------------------------------

	public function __construct( $config = [] ) {

		$this->properties = PaypalRestProperties::getInstance();

		parent::__construct( $config );
	}

	public function setBaseUrl( $baseUrl, $params ) {

		$this->successUrl	= "$baseUrl/payment/success?$params";
		$this->failureUrl	= "$baseUrl/payment/failed?$params";
	}

	// Instance methods --------------------------------------------

	// Yii parent classes --------------------

	// yii\base\Component -----

	// CMG interfaces ------------------------

	// CMG parent classes --------------------

	// PaypalRestService ---------------------

	// Data Provider ------

	// Read ---------------

	// Read - Models ---

	public function getPayment( $paymentId ) {

		$apiContext = $this->getApiContext();

		$payment 	= Payment::get( $paymentId, $apiContext );

		return $payment;
	}

	public function getSale( $saleId ) {

		$apiContext = $this->getApiContext();

		$sale 		= Sale::get( $saleId, $apiContext );

		return $sale;
	}

	// Read - Lists ----

	// Read - Maps -----

	// Read - Others ---

	public function getSaleId( $payment ) {

		$transactions 	= $payment->getTransactions();

		$transaction	= $transactions[ 0 ];

		$resources 		= $transaction->getRelatedResources();
		$sale			= $resources[ 0 ]->getSale();
		$saleId 		= $sale->getId();

		/*
		$tran_amount	= $transaction->getAmount();
		$tran_total		= $tran_amount->getTotal();
		$tran_currency	= $tran_amount->getCurrency();
		*/

		return $saleId;
	}

	public function isSaleComplete( $saleId ) {

		$apiContext = $this->getApiContext();

		$sale 		= Sale::get( $saleId, $apiContext );

		$completed	= strcmp( $sale->getState(), 'completed' ) == 0;

		return $completed;
	}

	public function getLink( array $links, $type ) {

		foreach( $links as $link ) {

			if( $link->getRel() == $type ) {

				return $link->getHref();
			}
		}

		return "";
	}

	// Create -------------

	public function createPayment( $order, $addressee = null ) {

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

	        $shippingAddress = $this->generateShippingAddress( $addressee, $cart );

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

	// Update -------------

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

	public function refundPayment( $saleId, $amount, $currency ) {

		$amount	 	= number_format( $amount, 2 );

		$apiContext = $this->getApiContext();

		$sale 		= Sale::get( $saleId, $apiContext );

		$refund 	= new Refund();

		if( isset($amount) && strlen($amount) > 0 ) {

			$amt = new Amount();

			$amt->setCurrency( $currency );
			$amt->setTotal( $amount );

			$refund->setAmount($amt);
		}

		$refund = $sale->refund( $refund, $apiContext );

		return $refund;
	}

	// Delete -------------

	// Additional ---------

	private function parseApiError( $errorJson ) {

		$msg 	= '';
		$data 	= json_decode( $errorJson, true );

		if( isset( $data[ 'name' ] ) && isset( $data[ 'message' ] ) ) {

			$msg .= $data[ 'name' ] . " : " .  $data[ 'message' ] . "<br/>";
		}

		if( isset( $data[ 'details' ] ) ) {

			$msg .= "<ul>";

			foreach( $data[ 'details' ] as $detail ) {

				$msg .= "<li>" . $detail[ 'field' ] . " : " . $detail[ 'issue' ] . "</li>";
			}

			$msg .= "</ul>";
		}

		if( $msg == '' ) {

			$msg = $errorJson;
		}

		return $msg;
	}

	private function generateShippingAddress( $addressee, $address ) {

        $shippingAddress	= new ShippingAddress();

        $shippingAddress->setRecipientName( $addressee );
        $shippingAddress->setLine1( $address->line1 );
        $shippingAddress->setLine2( $address->line2 );
        $shippingAddress->setCity( $address->cityName );
        $shippingAddress->setState( $address->provinceName );
        $shippingAddress->setCountryCode( $address->countryName );
        $shippingAddress->setPostalCode( $address->zip );

		return $shippingAddress;
	}

	private function generateItemsList( $order ) {

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

	// SDK Configuration --

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

	// Static Methods ----------------------------------------------

	// CMG parent classes --------------------

	// PaypalRestService ---------------------

	// Data Provider ------

	// Read ---------------

	// Read - Models ---

	// Read - Lists ----

	// Read - Maps -----

	// Read - Others ---

	// Create -------------

	// Update -------------

	// Delete -------------

}

// Sample Payment

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
