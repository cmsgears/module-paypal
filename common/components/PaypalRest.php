<?php
/**
 * This file is part of CMSGears Framework. Please view License file distributed
 * with the source code for license details.
 *
 * @link https://www.cmsgears.org/
 * @copyright Copyright (c) 2015 VulpineCode Technologies Pvt. Ltd.
 */

namespace cmsgears\paypal\rest\common\components;

// Yii Imports
use Yii;
use yii\base\Component;

/**
 * PaypalRest component register the services provided by Paypal REST Module.
 *
 * @since 1.0.0
 */
class PaypalRest extends Component {

	// Global -----------------

	// Public -----------------

	// Protected --------------

	// Private ----------------

	// Constructor and Initialisation ------------------------------

	/**
	 * Initialize the services.
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

	// Properties ----------------

	// Components and Objects ----

	/**
	 * Register the services.
	 */
	public function registerComponents() {

		// Register services
		$this->registerResourceServices();
		$this->registerSystemServices();

		// Init services
		$this->initResourceServices();
		$this->initSystemServices();
	}

	/**
	 * Registers resource services.
	 */
	public function registerResourceServices() {

		$factory = Yii::$app->factory->getContainer();

		$factory->set( 'cmsgears\paypal\rest\common\services\interfaces\resources\ITransactionService', 'cmsgears\paypal\rest\common\services\resources\TransactionService' );
	}

	/**
	 * Registers system services.
	 */
	public function registerSystemServices() {

		$factory = Yii::$app->factory->getContainer();

		$factory->set( 'cmsgears\paypal\rest\common\services\interfaces\system\IPaypalRestService', 'cmsgears\paypal\rest\common\services\system\PaypalRestService' );
	}

	/**
	 * Initialize resource services.
	 */
	public function initResourceServices() {

		$factory = Yii::$app->factory->getContainer();

		$factory->set( 'paypalTransactionService', 'cmsgears\paypal\rest\common\services\resources\TransactionService' );
	}

	/**
	 * Initialize system services.
	 */
	public function initSystemServices() {

		$factory = Yii::$app->factory->getContainer();

		$factory->set( 'paypalRestService', 'cmsgears\paypal\rest\common\services\system\PaypalRestService' );
	}

}
