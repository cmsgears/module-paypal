<?php
namespace cmsgears\paypal\rest\common\services\interfaces\system;

interface IPaypalRestService {

	// Data Provider ------

	// Read ---------------

	// Read - Models ---

	public function getPayment( $paymentId );

	public function getSale( $saleId );

	// Read - Lists ----

	// Read - Maps -----

	// Read - Others ---

	public function getSaleId( $payment );

	public function isSaleComplete( $saleId );

	public function getLink( array $links, $type );

	// Create -------------

	public function createPayment( $order, $addressee = null );

	// Update -------------

	public function executePayment( $paymentId, $token, $payerId );

	public function refundPayment( $saleId, $amount, $currency );

	// Delete -------------

}
