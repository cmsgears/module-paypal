<?php
namespace common\utilities;

// Yii Imports
use \Yii;
use yii\helpers\Url;

// Paypal Imports
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Sale;
use PayPal\Api\Refund;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\ShippingAddress;

class PaypalRestUtil {

	private $properties;
	private $successUrl;
	private $failureUrl;

	public function __construct( $properties, $scenario ) {

		$this->properties = $properties;

		if( strcmp( $scenario, "booking" ) == 0 ) {

			$this->successUrl = Url::home( true ) . Url::to( "payment/success" );
			$this->failureUrl = Url::home( true ) . Url::to( "payment/failure" );
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

	// Utlity Methods ---------------------------------------

	function generateShippingAddress( $cart ) {

		// TODO - Generate Shipping Address from Cart

        $address   = new ShippingAddress();
        $address->setRecipientName( "Test Receipient" );
        $address->setLine1( "line 1" );
        $address->setLine2( "line 2" );
        $address->setCity( "Ontario" );
        $address->setState( "Toronto" );
        $address->setCountryCode( "CA" );
        $address->setPostalCode( "M5A4E9" );

		return $address;
	}

	function generateItemsList( $cart ) {

		// TODO - Generate Items List from Cart

		$currency	= $this->properties->getCurrency();
        $items   	= array();

        $item   = new Item();
        $item->setName( "Test Item 1" );
        $item->setQuantity( 2 );
        $item->setCurrency( $currency );
        $item->setPrice( 10.00 );        
        $item->setSku( 1 );

        $items[] = $item;

        $item   = new Item();
        $item->setName( "Test Item 2" );
        $item->setQuantity( 1 );
        $item->setCurrency( $currency );
        $item->setPrice( 10.00 );        
        $item->setSku( 2 );

        $items[] = $item;

        $itemList = new ItemList();

        $itemList->setItems( $items );

		return $itemList;
	}
    
	// Payment Methods --------------------------------------

	public function createPayment( $cart, $description ) {

		// Cart Totals
	    $subTotal     		= 30.00; // TODO - Replace with cart total
		$shippingCharges	=  0.00; // TODO - Replace with cart total
		$tax				=  0.00; // TODO - Replace with cart total
		$grandTotal   		= 30.00; // TODO - Replace with cart total

		// Get Context
		$context = $this->getApiContext();

		// Payer
		$payer = new Payer();
		$payer->setPaymentMethod( "paypal" );

        // Shipping Address
        $shippingAddress	= $this->generateShippingAddress( $cart );

        // Cart Items
        $itemList   		= $this->generateItemsList( $cart );

        $itemList->setShippingAddress( $shippingAddress );

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
		$transaction->setDescription( $description );
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

	public function executePayment( $paymentId, $payerId ) {

		$apiContext 		= $this->getApiContext();
		$payment 			= Payment::get( $paymentId, $apiContext );
		$paymentExecution 	= new PaymentExecution();
		
		$paymentExecution->setPayerId( $payerId );
		
		$payment = $payment->execute( $paymentExecution, $apiContext );

		return $payment;
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

		$completed		= strcmp( $sale->getState(), "completed") == 0;

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

		if( strcmp( $this->properties->getStatus(), "sandbox" ) == 0 ) {

			$apiContext = new ApiContext( new OAuthTokenCredential( $this->properties->getSandboxClientId(),  $this->properties->getSandboxSecret() ) );
		}
		else if( strcmp( $this->properties->getStatus(), "live" ) == 0 ) {

			$apiContext = new ApiContext( new OAuthTokenCredential( $this->properties->getLiveClientId(),  $this->properties->getLiveSecret() ) );
		}

		$apiContext->setConfig([
			'http.ConnectionTimeOut' => 90,
			'http.Retry' => 1,
			'mode' => $this->properties->getStatus(),
			'log.LogEnabled' => true,
			'log.FileName' => Yii::getAlias( "@frontend" ) . '/runtime/paypal.log',
			'log.LogLevel' => 'INFO'		
		]);

		return $apiContext;
	}
}

?>