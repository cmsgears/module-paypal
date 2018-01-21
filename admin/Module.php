<?php
namespace cmsgears\paypal\rest\admin;

// Yii Imports
use \Yii;

// CMG Imports
use cmsgears\paypal\rest\common\config\PaypalRestGlobal;

class Module extends \cmsgears\core\common\base\Module {

    public $controllerNamespace = 'cmsgears\paypal\rest\admin\controllers';

	public $config 				= [ PaypalRestGlobal::CONFIG_PAYPAL_REST ];

    public function init() {

        parent::init();

        $this->setViewPath( '@cmsgears/module-paypal-rest/admin/views' );
    }

	public function getSidebarHtml() {

		$path	= Yii::getAlias( "@cmsgears" ) . "/module-paypal-rest/admin/views/sidebar.php";

		return $path;
	}
}

?>