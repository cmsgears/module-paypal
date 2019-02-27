<?php
/**
 * This file is part of CMSGears Framework. Please view License file distributed
 * with the source code for license details.
 *
 * @link https://www.cmsgears.org/
 * @copyright Copyright (c) 2015 VulpineCode Technologies Pvt. Ltd.
 */

namespace cmsgears\paypal\rest\common\config;

// CMG Imports
use cmsgears\paypal\rest\common\config\PaypalRestGlobal;

use cmsgears\core\common\config\Properties;

/**
 * PaypalRestProperties provide methods to access the properties specific to paypal.
 *
 * @since 1.0.0
 */
class PaypalRestProperties extends Properties {

	// Variables ---------------------------------------------------

	// Globals ----------------

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

	private static $instance;

	// Traits ------------------------------------------------------

	// Constructor and Initialisation ------------------------------

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

	// Instance methods --------------------------------------------

	// Yii interfaces ------------------------

	// Yii parent classes --------------------

	// CMG interfaces ------------------------

	// CMG parent classes --------------------

	// PaypalRestProperties ------------------

	/**
	 * Return the status among live or sandbox.
	 *
	 * @return string
	 */
	public function getStatus() {

		return $this->properties[ self::PROP_STATUS ];
	}

	/**
	 * Check whether payments are enabled for PayPal.
	 *
	 * @return boolean
	 */
	public function isPayments() {

		return $this->properties[ self::PROP_PAYMENTS ];
	}

	/**
	 * Check whether status is set to either sandbox or live.
	 *
	 * @return boolean
	 */
	public function isActive() {

		$status = $this->properties[ self::PROP_STATUS ];

		return $status === 'sandbox' || $status === 'live';
	}

	/**
	 * Returns the currency configured for PayPal.
	 *
	 * @return string
	 */
	public function getCurrency() {

		return $this->properties[ self::PROP_CURRENCY ];
	}

	/**
	 * Check whether address verification is enabled for PayPal.
	 *
	 * @return string
	 */
	public function isSendAddress() {

		$sendAddress = $this->properties[ self::PROP_SEND_ADDRESS ];

		return $sendAddress;
	}

	/**
	 * Returns the client id for sandbox mode.
	 *
	 * @return string
	 */
	public function getSandboxClientId() {

		return $this->properties[ self::PROP_SB_CLIENT_ID ];
	}

	/**
	 * Returns the secret for sandbox mode.
	 *
	 * @return string
	 */
	public function getSandboxSecret() {

		return $this->properties[ self::PROP_SB_SECRET ];
	}

	/**
	 * Returns the client id for live mode.
	 *
	 * @return string
	 */
	public function getLiveClientId() {

		return $this->properties[ self::PROP_LIVE_CLIENT_ID ];
	}

	/**
	 * Returns the secret for live mode.
	 *
	 * @return string
	 */
	public function getLiveSecret() {

		return $this->properties[ self::PROP_LIVE_SECRET ];
	}

	/**
	 * Check whether PayPal can be used to handle payments.
	 *
	 * @return boolean
	 */
	public function isPaymentActive() {

		return $this->isActive() && $this->isPaymentEnabled();
	}

}
