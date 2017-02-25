<?php
// CMG Imports
use cmsgears\core\common\config\CoreGlobal;

use cmsgears\core\common\models\entities\Site;
use cmsgears\core\common\models\entities\User;
use cmsgears\core\common\models\resources\Form;
use cmsgears\core\common\models\resources\FormField;

use cmsgears\core\common\utilities\DateUtil;

class m160622_032302_paypal_rest_data extends \yii\db\Migration {

	// Public Variables

	// Private Variables

	private $prefix;

	private $site;

	private $master;

	public function init() {

		// Table prefix
		$this->prefix	= Yii::$app->migration->cmgPrefix;

		$this->site		= Site::findBySlug( CoreGlobal::SITE_MAIN );
		$this->master	= User::findByUsername( Yii::$app->migration->getSiteMaster() );

		Yii::$app->core->setSite( $this->site );
	}

    public function up() {

		// Create various config
		$this->insertPaypalConfig();

		// Init default config
		$this->insertDefaultConfig();
    }

	private function insertPaypalConfig() {

		$this->insert( $this->prefix . 'core_form', [
            'siteId' => $this->site->id,
            'createdBy' => $this->master->id, 'modifiedBy' => $this->master->id,
            'name' => 'Config PayPal REST', 'slug' => 'config-paypal-rest',
            'type' => CoreGlobal::TYPE_SYSTEM,
            'description' => 'PayPal REST configuration form.',
            'successMessage' => 'All configurations saved successfully.',
            'captcha' => false,
            'visibility' => Form::VISIBILITY_PROTECTED,
            'active' => true, 'userMail' => false,'adminMail' => false,
            'createdAt' => DateUtil::getDateTime(),
            'modifiedAt' => DateUtil::getDateTime()
        ]);

		$config	= Form::findBySlug( 'config-paypal-rest', CoreGlobal::TYPE_SYSTEM );

		$columns = [ 'formId', 'name', 'label', 'type', 'compress', 'validators', 'order', 'icon', 'htmlOptions' ];

		$fields	= [
			[ $config->id, 'status', 'Status', FormField::TYPE_SELECT, false, 'required', 0, NULL, '{\"title\":\"Status\",\"items\":[\"sandbox\",\"live\"]}' ],
			[ $config->id, 'payments', 'Payments', FormField::TYPE_TOGGLE, false, 'required', 0, NULL, '{\"title\":\"Payments Enabled\"}' ],
			[ $config->id, 'currency', 'Currency', FormField::TYPE_SELECT, false, 'required', 0, NULL, '{\"title\":\"Currency\",\"items\":[\"USD\",\"CAD\"]}' ],
			[ $config->id, 'address', 'Address', FormField::TYPE_TOGGLE, false, 'required', 0, NULL, '{\"title\":\"Address Verification\"}' ],
			[ $config->id, 'sb_client_id', 'Sandbox Client ID', FormField::TYPE_TEXT, false, 'required', 0, NULL, '{\"title\":\"Sandbox Client ID\",\"placeholder\":\"Sandbox Client ID\"}' ],
			[ $config->id, 'sb_secret', 'Sandbox Secret', FormField::TYPE_PASSWORD, false, 'required', 0, NULL, '{\"title\":\"Sandbox Secret\",\"placeholder\":\"Sandbox Secret\"}' ],
			[ $config->id, 'live_client_id', 'Live Client ID', FormField::TYPE_TEXT, false, 'required', 0, NULL, '{\"title\":\"Live Client ID\",\"placeholder\":\"Live Client ID\"}' ],
			[ $config->id, 'live_secret', 'Live Secret', FormField::TYPE_PASSWORD, false, 'required', 0, NULL, '{\"title\":\"Live Secret\",\"placeholder\":\"Live Secret\"}' ]
		];

		$this->batchInsert( $this->prefix . 'core_form_field', $columns, $fields );
	}

	private function insertDefaultConfig() {

		$columns = [ 'modelId', 'name', 'label', 'type', 'valueType', 'value' ];

		$attributes	= [
			[ $this->site->id, 'status', 'Status', 'paypal-rest','text', null ],
			[ $this->site->id, 'payments', 'Payments', 'paypal-rest','flag', '0' ],
			[ $this->site->id, 'currency','Currency', 'paypal-rest','text', 'USD' ],
			[ $this->site->id, 'address','Address', 'paypal-rest','flag', '0' ],
			[ $this->site->id, 'sb client id','Sandbox Client ID', 'paypal-rest','text', null ],
			[ $this->site->id, 'sb secret','Sandbox Secret', 'paypal-rest','text', null ],
			[ $this->site->id, 'live client id','Live Client ID', 'paypal-rest','text', null ],
			[ $this->site->id, 'live secret','Live Secret', 'paypal-rest','text', null ]
		];

		$this->batchInsert( $this->prefix . 'core_site_meta', $columns, $attributes );
	}

    public function down() {

        echo "m160622_032302_paypal_rest_data will be deleted with m160621_014408_core.\n";

        return true;
    }
}

?>