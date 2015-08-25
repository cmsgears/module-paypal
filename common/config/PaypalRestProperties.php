<?php
namespace cmsgears\paypal\rest\common\config;

// Yii Imports
use \Yii;

// CMG Imports
use cmsgears\paypal\rest\common\config\PaypalRestGlobal;

class PaypalRestProperties extends \cmsgears\core\common\config\CmgProperties {

	const PROP_STATUS			= "status";
	const PROP_PAYMENT_ENABLED	= "payment enabled";
	const PROP_CURRENCY			= "currency";
	const PROP_SEND_ADDRESS		= "send address";

	const PROP_SB_CLIENT_ID		= "sb client id";
	const PROP_SB_SECRET		= "sb secret";

	const PROP_LIVE_CLIENT_ID	= "live client id";
	const PROP_LIVE_SECRET		= "live secret";

	// Singleton instance
	private static $instance;

	// Constructor and Initialisation ------------------------------

	private function __construct() {

		// init code
	}

	/**
	 * Return Singleton instance.
	 */
	public static function getInstance() {

		if( !isset( self::$instance ) ) {

			self::$instance	= new PaypalRestProperties();

			self::$instance->init( PaypalRestGlobal::CONFIG_PAYPAL_REST );
		}

		return self::$instance;
	}

	/**
	 * Return rest property for the specified key.
	 */
	public function getProperty( $key ) {

		return $this->properties[ key ];
	}

	public function getStatus() {

		return $this->properties[ self::PROP_STATUS ];
	}

	public function isPaymentEnabled() {

		return $this->properties[ self::PROP_PAYMENT_ENABLED ];
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

?>