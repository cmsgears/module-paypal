<?php
namespace cmsgears\paypal\rest\common\models\entities;

// Yii Imports
use \Yii;

// CMG Imports
use cmsgears\core\common\config\CoreGlobal;

use cmsgears\core\common\models\entities\CmgEntity;

/**
 * PaypalTransaction Entity - The primary class.
 *
 * @property integer $id
 * @property integer $createdBy
 * @property integer $parentId
 * @property string $parentType
 * @property string $code
 * @property string $intent
 * @property datetime $createdAt
 */
class PaypalTransaction extends CmgEntity {

	// Instance methods --------------------------------------------------

	// yii\base\Model --------------------

	// Static Methods ----------------------------------------------

	// yii\db\ActiveRecord ---------------

	public static function tableName() {

		return PayPalRestTables::TABLE_REST_TRANSACTION;
	}

	// PaypalTransaction -----------------

	public static function findByCode( $code ) {

		return self::find()->where( 'code=:code', [ ':code' => $code ] )->one();
	}

	public static function findByParentIdParentType( $parentId, $parentType ) {

		return self::find()->where( 'parentId=:id AND parentType=:type', [ ':id' => $parentId, ':type' => $parentType ] )->orderBy( 'id DESC' )->one();
	}
}

?>