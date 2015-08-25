/* ========================== CMSGears PayPal REST ========================================== */

SET FOREIGN_KEY_CHECKS=0;

--
-- Dumping data for table `cmg_core_model_meta`
--

INSERT INTO `cmg_core_model_meta` (`parentId`,`parentType`,`name`,`value`,`type`,`fieldType`,`fieldMeta`) VALUES
	(1,'site','status','sandbox','paypal-rest','text',null),
	(1,'site','payment enabled','1','paypal-rest','text',null),
	(1,'site','currency','USD','paypal-rest','text',null),
	(1,'site','send address','0','paypal-rest','text',null),
	(1,'site','sb client id',null,'paypal-rest','text',null),
	(1,'site','sb secret',null,'paypal-rest','password',null),
	(1,'site','live client id',null,'paypal-rest','text',null),
	(1,'site','live secret',null,'paypal-rest','password',null);