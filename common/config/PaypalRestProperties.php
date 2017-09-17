<?php
namespace cmsgears\paypal\rest\common\config;

// CMG Imports
use cmsgears\paypal\rest\common\config\PaypalRestGlobal;

class PaypalRestProperties extends \cmsgears\core\common\config\CmgProperties {

	// Variables ---------------------------------------------------

	// Global -----------------

	const PROP_STATUS			= 'status';
	const PROP_PAYMENTS			= 'payments';
	const PROP_CURRENCY			= 'currency';
	const PROP_SEND_ADDRESS		= 'address';

	const PROP_SB_CLIENT_ID		= 'sb_client_id';
	const PROP_SB_SECRET		= 'sb_secret';

	const PROP_LIVE_CLIENT_ID	= 'live_client_id';
	const PROP_LIVE_SECRET		= 'live_secret';

	// Public -----------------

	// Protected --------------

	// Private ----------------

	// Singleton instance
	private static $instance;

	// Constructor and Initialisation ------------------------------

	// Instance methods --------------------------------------------

	// Yii parent classes --------------------

	// CMG parent classes --------------------

	// PaypalRestProperties ------------------

	// Singleton

	public static function getInstance() {

		if( !isset( self::$instance ) ) {

			self::$instance	= new PaypalRestProperties();

			self::$instance->init( PaypalRestGlobal::CONFIG_PAYPAL_REST );
		}

		return self::$instance;
	}

	// Properties

	public function getStatus() {

		return $this->properties[ self::PROP_STATUS ];
	}

	public function isPayments() {

		return $this->properties[ self::PROP_PAYMENTS ];
	}

	public function isActive() {

		$status = $this->properties[ self::PROP_STATUS ];

		return strcmp( $status, 'sandbox' ) == 0 || strcmp( $status, 'live' ) == 0;
	}

	public function getCurrency() {

		return $this->properties[ self::PROP_CURRENCY ];
	}

	public function isSendAddress() {

		$sendAddress = $this->properties[ self::PROP_SEND_ADDRESS ];

		return $sendAddress;
	}

	public function getSandboxClientId() {

		return $this->properties[ self::PROP_SB_CLIENT_ID ];
	}

	public function getSandboxSecret() {

		return $this->properties[ self::PROP_SB_SECRET ];
	}

	public function getLiveClientId() {

		return $this->properties[ self::PROP_LIVE_CLIENT_ID ];
	}

	public function getLiveSecret() {

		return $this->properties[ self::PROP_LIVE_SECRET ];
	}

	public function isPaymentActive() {

		return $this->isActive() && $this->isPaymentEnabled();
	}
}
