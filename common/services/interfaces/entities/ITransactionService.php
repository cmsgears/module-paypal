<?php
namespace cmsgears\paypal\rest\common\services\interfaces\entities;

// CMG Imports
use cmsgears\core\common\services\interfaces\base\IEntityService;

interface ITransactionService extends IEntityService {

	// Data Provider ------

	// Read ---------------

	// Read - Models ---

	public function getByCode( $code );

	// Read - Lists ----

	// Read - Maps -----

	// Create -------------

	// Update -------------

	// Delete -------------

}
