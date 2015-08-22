<?php
// Yii Imports
use \Yii;
use yii\helpers\Html;
use yii\helpers\Url;

$core	= Yii::$app->cmgCore;
$user	= Yii::$app->user->getIdentity();

// Sidebar
$parent 	= $this->params['sidebar-parent'];
$child 		= $this->params['sidebar-child'];
?>