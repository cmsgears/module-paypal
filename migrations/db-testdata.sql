/* ========================== CMSGears PayPal REST ========================================== */

--
-- REST Config Form
--

INSERT INTO `cmg_core_form` (`siteId`,`templateId`,`createdBy`,`modifiedBy`,`name`,`slug`,`type`,`description`,`successMessage`,`captcha`,`visibility`,`active`,`userMail`,`adminMail`,`createdAt`,`modifiedAt`,`htmlOptions`,`data`) VALUES
	(@site,NULL,1,1,'PP REST','pp-rest','system','PayPal REST configuration form.','All configurations saved successfully.',0,10,1,0,0,'2014-10-11 14:22:54','2014-10-11 14:22:54',NULL,NULL);

SELECT @form := `id` FROM cmg_core_form WHERE slug = 'pp-rest';

INSERT INTO `cmg_core_form_field` (`formId`,`name`,`label`,`type`,`compress`,`validators`,`order`,`icon`,`htmlOptions`,`data`) VALUES 
	(@form,'status','Status',80,0,'required',0,NULL,'{\"title\":\"Status\",\"items\":[\"sandbox"\:\"Sandbox"\,\"live"\:\"Live"\]}',NULL),
	(@form,'payments','Payments',30,0,'required',0,NULL,'{\"title\":\"Payments Enabled\"}',NULL),
	(@form,'currency','Currency',80,0,'required',0,NULL,'{\"title\":\"Currency\",\"items\":[\"USD"\:\"USD"\,\"CAD"\:\"CAD"\]}',NULL),
	(@form,'address','Address',30,0,'required',0,NULL,'{\"title\":\"Address Verification\"}',NULL),
	(@form,'sb client id','Sandbox Client ID',0,0,'required',0,NULL,'{\"title\":\"Sandbox Client ID\",\"placeholder\":\"Sandbox Client ID\"}',NULL),
	(@form,'sb secret','Sandbox Secret',10,0,'required',0,NULL,'{\"title\":\"Sandbox Secret\",\"placeholder\":\"Sandbox Secret\"}',NULL),
	(@form,'live client id','Live Client ID',0,0,'required',0,NULL,'{\"title\":\"Live Client ID\",\"placeholder\":\"Live Client ID\"}',NULL),
	(@form,'live secret','Live Secret',10,0,'required',0,NULL,'{\"title\":\"Live Secret\",\"placeholder\":\"Live Secret\"}',NULL);

--
-- Dumping data for table `cmg_core_model_attribute`
--

INSERT INTO `cmg_core_model_attribute` (`parentId`,`parentType`,`name`,`type`,`valueType`,`value`) VALUES
	(@site,'site','status','paypal-rest','text',NULL),
	(@site,'site','payments','paypal-rest','flag','0'),
	(@site,'site','currency','paypal-rest','text','USD'),
	(@site,'site','address','paypal-rest','flag','0'),
	(@site,'site','sb_client_id','paypal-rest','text',NULL),
	(@site,'site','sb_secret','paypal-rest','text',NULL),
	(@site,'site','live_client_id','paypal-rest','text',NULL),
	(@site,'site','live_secret','paypal-rest','text',NULL);