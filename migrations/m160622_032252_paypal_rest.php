<?php
// CMG Imports
use cmsgears\core\common\config\CoreGlobal;

class m160622_032252_paypal_rest extends \yii\db\Migration {

	// Public Variables

	public $fk;
	public $options;

	// Private Variables

	private $prefix;

	public function init() {

		// Fixed
		$this->prefix		= 'cmg_';

		// Get the values via config
		$this->fk			= Yii::$app->cmgMigration->isFk();
		$this->options		= Yii::$app->cmgMigration->getTableOptions();

		// Default collation
		if( $this->db->driverName === 'mysql' ) {

			$this->options = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
	}

    public function up() {

    }

    public function down() {

    }
}

?>