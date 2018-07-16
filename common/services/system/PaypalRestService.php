<?php
/**
 * This file is part of CMSGears Framework. Please view License file distributed
 * with the source code for license details.
 *
 * @link https://www.cmsgears.org/
 * @copyright Copyright (c) 2015 VulpineCode Technologies Pvt. Ltd.
 */

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

use PayPal\Api\Payout;
use PayPal\Api\PayoutSenderBatchHeader;
use PayPal\Api\PayoutItem;
use PayPal\Api\Currency;

// CMG Imports
use cmsgears\paypal\rest\common\config\PaypalRestProperties;

use cmsgears\paypal\rest\common\services\interfaces\system\IPaypalRestService;

use cmsgears\core\common\services\base\SystemService;

/**
 * PaypalRestService provide methods specific to PayPal REST APIs to handle transactions.
 *
 * @since 1.0.0
 */
class PaypalRestService extends SystemService implements IPaypalRestService {

	// Variables ---------------------------------------------------

	// Globals ----------------

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

	// Yii interfaces ------------------------

	// Yii parent classes --------------------

	// CMG interfaces ------------------------

	// CMG parent classes --------------------

	// PaypalRestService ---------------------

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

	public function createPayout( $config = [] ) {
		
		$email		= $config[ 'email' ] ?? null;
		$amount		= $config[ 'amount' ] ?? null;
		$currency	= $config[ 'currency' ] ?? null;
		$itemId		= $config[ 'itemId' ] ?? null;
		$message	= $config[ 'message' ] ?? 'Thanks for your patronage!';
		$type		= $config[ 'type' ] ?? null;
		$subject	= $config[ 'subject' ] ?? "You have a Payout!";
		$batchId	= $config[ 'batchId' ] ?? uniqid();
		// Create a new instance of Payout object
		$payouts = new Payout();

		// This is how our body should look like:
		/*
		 * {
					"sender_batch_header":{
						"sender_batch_id":"2014021801",
						"email_subject":"You have a Payout!"
					},
					"items":[
						{
							"recipient_type":"EMAIL",
							"amount":{
								"value":"1.0",
								"currency":"USD"
							},
							"note":"Thanks for your patronage!",
							"sender_item_id":"2014031400023",
							"receiver":"shirt-supplier-one@mail.com"
						}
					]
				}
		 */

		$senderBatchHeader = new PayoutSenderBatchHeader();
		// ### NOTE:
		// You can prevent duplicate batches from being processed. If you specify a `sender_batch_id` that was used in the last 30 days, the batch will not be processed. For items, you can specify a `sender_item_id`. If the value for the `sender_item_id` is a duplicate of a payout item that was processed in the last 30 days, the item will not be processed.

		
		//uniqid()
		// #### Batch Header Instance
		$senderBatchHeader->setSenderBatchId( $batchId )
			->setEmailSubject($subject);

		// #### Sender Item
		// Please note that if you are using single payout with sync mode, you can only pass one Item in the request
		$senderItem = new PayoutItem();
		
		$payoutAmount	= new Currency("{
								\"value\":\"$amount\",
								\"currency\":\"$currency\"
							}");
		
		$senderItem->setRecipientType($type)
			->setNote($message)
			->setReceiver($email)
			->setSenderItemId($itemId)
			->setAmount( $payoutAmount );

		$payouts->setSenderBatchHeader($senderBatchHeader)->addItem($senderItem);

		// For Sample Purposes Only.
		$request = clone $payouts;

		$apiContext = $this->getApiContext();

		// ### Create Payout
		try {
			
			$output = $payouts->create( [], $apiContext);
			
		}

		catch (Exception $ex) {

			// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
			//ResultPrinter::printError("Created Single Synchronous Payout", "Payout", null, $request, $ex);
			exit(1);
		}

		// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
		 //ResultPrinter::printResult("Created Single Synchronous Payout", "Payout", $output->getBatchHeader()->getPayoutBatchId(), $request, $output);

		return $output;
	}
	
	public function getPayoutBatch( $payoutBatchId ) {
		
		$apiContext = $this->getApiContext();
		
		$payouts = new Payout();
		
		$output = $payouts->get($payoutBatchId, $apiContext );
		
		return $output;
	}
	
	public function getPayoutItemDetails(  $payoutItmeId ) {
		
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
      	catch( \PayPal\Exception\PayPalConnectionException $ex ) {

          	return false;
        }
		catch( Exception $ex ) {

          	return false;
		}

		return true;
	}

	public function reversePayment( $order ) {

		$transaction	= $order->getTransaction()->one();
		$data			= json_decode( $transaction->data );
		$paymentId		= $data->paymentId;
		$payment		= $this->getPayment( $paymentId );
		$saleId			= $this->getSaleId( $payment );
		$amount			= $transaction->amount;
		$currency		= $transaction->currency;

		return  $this->refundPayment( $saleId, $amount, $currency);

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

	// Yii parent classes --------------------

	// CMG parent classes --------------------

	// PaypalRestService ---------------------

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
