<?php
/**
 * This file is part of CMSGears Framework. Please view License file distributed
 * with the source code for license details.
 *
 * @link https://www.cmsgears.org/
 * @copyright Copyright (c) 2015 VulpineCode Technologies Pvt. Ltd.
 */

namespace cmsgears\paypal\rest\common\services\interfaces\system;

// CMG Imports
use cmsgears\core\common\services\interfaces\base\ISystemService;

/**
 * IPaypalRestService declares methods specific to PayPal REST APIs.
 *
 * @since 1.0.0
 */
interface IPaypalRestService extends ISystemService {

	public function getPayment( $paymentId );

	public function getSale( $saleId );

	public function getSaleId( $payment );

	public function isSaleComplete( $saleId );

	public function getLink( array $links, $type );

	public function createPayment( $order, $addressee = null );

	public function executePayment( $paymentId, $token, $payerId );

	public function refundPayment( $saleId, $amount, $currency );

}
