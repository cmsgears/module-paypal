<?php
namespace cmsgears\paypal\rest\common\components;

// Yii Imports
use Yii;

class PaypalRest extends \yii\base\Component {

	// Global -----------------

	// Public -----------------

	// Protected --------------

	// Private ----------------

	// Constructor and Initialisation ------------------------------

	/**
	 * Initialise the CMG Core Component.
	 */
	public function init() {

		parent::init();

		// Register application components and objects i.e. CMG and Project
		$this->registerComponents();
	}

	// Instance methods --------------------------------------------

	// Yii parent classes --------------------

	// CMG parent classes --------------------

	// PaypalRest ----------------------------

	// Properties

	// Components and Objects

	public function registerComponents() {

		// Register services
		$this->registerEntityServices();
		$this->registerSystemServices();

		// Init services
		$this->initEntityServices();
		$this->initSystemServices();
	}

	public function registerEntityServices() {

		$factory = Yii::$app->factory->getContainer();

		$factory->set( 'cmsgears\paypal\rest\common\services\interfaces\entities\ITransactionService', 'cmsgears\paypal\rest\common\services\entities\TransactionService' );
	}

	public function registerSystemServices() {

		$factory = Yii::$app->factory->getContainer();

		$factory->set( 'cmsgears\paypal\rest\common\services\interfaces\system\IPaypalRestService', 'cmsgears\paypal\rest\common\services\system\PaypalRestService' );
	}

	public function initEntityServices() {

		$factory = Yii::$app->factory->getContainer();

		$factory->set( 'paypalTransactionService', 'cmsgears\paypal\rest\common\services\entities\TransactionService' );
	}

	public function initSystemServices() {

		$factory = Yii::$app->factory->getContainer();

		$factory->set( 'paypalRestService', 'cmsgears\paypal\rest\common\services\system\PaypalRestService' );
	}
}
