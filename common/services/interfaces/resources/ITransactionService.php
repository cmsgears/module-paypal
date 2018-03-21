<?php
/**
 * This file is part of CMSGears Framework. Please view License file distributed
 * with the source code for license details.
 *
 * @link https://www.cmsgears.org/
 * @copyright Copyright (c) 2015 VulpineCode Technologies Pvt. Ltd.
 */

namespace cmsgears\paypal\rest\common\services\interfaces\resources;

// CMG Imports
use cmsgears\cart\common\services\interfaces\resources\ITransactionService as IBaseTransactionService;

/**
 * ITransactionService declares methods specific to order transactions using PayPal.
 *
 * @since 1.0.0
 */
interface ITransactionService extends IBaseTransactionService {

	// Data Provider ------

	// Read ---------------

	// Read - Models ---

	public function getByCode( $code );

	// Read - Lists ----

	// Read - Maps -----

	// Read - Others ---

	// Create -------------

	// Update -------------

	// Delete -------------

	// Bulk ---------------

	// Notifications ------

	// Cache --------------

	// Additional ---------

}
