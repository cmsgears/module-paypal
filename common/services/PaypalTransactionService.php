<?php
namespace cmsgears\paypal\rest\common\services;

// Yii Imports
use \Yii;

// CMG Imports
use cmsgears\core\common\config\CoreGlobal;
use cmsgears\cart\common\config\CartGlobal;

use cmsgears\paypal\rest\common\models\entities\PayPalRestTables;
use cmsgears\paypal\rest\common\models\entities\PaypalTransaction;

use cmsgears\core\common\utilities\DateUtil;

class PaypalTransactionService extends \cmsgears\core\common\services\base\Service {

	// Static Methods ----------------------------------------------

	// Read ----------------

	public static function findById( $id ) {

		return PaypalTransaction::findById( $id );
	}

	public static function findByCode( $code ) {

		return PaypalTransaction::findByCode( $code );
	}

	/**
	 * Find cart if exist for the given order
	 */
	public static function findByOrderId( $orderId ) {

		return PaypalTransaction::findByParentIdParentType( $orderId, CartGlobal::TYPE_ORDER );
	}

	// Data Provider ------

	/**
	 * @param array $config to generate query
	 * @return ActiveDataProvider
	 */
	public static function getPagination( $config = [] ) {

		return self::getDataProvider( new Cart(), $config );
	}

	// Create -----------

	public static function create( $parentId, $parentType, $transaction ) {

		// Set Attributes
		$user						= Yii::$app->cmgCore->getAppUser();

		$transaction				= new PaypalTransaction();
		$transaction->createdBy		= $user->id;
		$transaction->status		= Order::STATUS_NEW;

		$cart->save();

		// Return Cart
		return $cart;
	}

	public static function createForOrderId( $orderId, $payment ) {

		// Set Attributes
		$user				= Yii::$app->cmgCore->getAppUser();
		$txn				= new PaypalTransaction();

		$txn->createdBy		= $user->id;
		$txn->parentId		= $orderId;
		$txn->parentType	= CartGlobal::TYPE_ORDER;
		$txn->intent		= $payment->intent;
		$txn->code			= $payment->getId();
		$txn->createdAt		= DateUtil::getDateTime();

		$txn->save();

		return $txn;
	}

	// Update -----------

}

?>