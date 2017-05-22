<?php
namespace cmsgears\paypal\rest\common\services\entities;

// Yii Imports
use \Yii;

// CMG Imports
use cmsgears\cart\common\config\CartGlobal;

use cmsgears\paypal\rest\common\models\entities\PaypalTransaction;

use cmsgears\paypal\rest\common\services\interfaces\entities\IPaypalTransactionService;

use cmsgears\core\common\utilities\DateUtil;

class PaypalTransactionService extends \cmsgears\payment\common\services\entities\TransactionService implements IPaypalTransactionService {

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

	public static function updateData( $payment, $paymentId, $token, $payerId ) {

		$payment->setDataMeta( 'paymentId', $paymentId );
		$payment->setDataMeta( 'token', $token );
		$payment->setDataMeta( 'payerId', $payerId );

		$payment->update();
    }

}
