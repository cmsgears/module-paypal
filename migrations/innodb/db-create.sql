/* ========================== CMSGears PayPal REST ========================================== */

--
-- Table structure for table `cmg_pp_rest_txn`
--

DROP TABLE IF EXISTS `cmg_pp_rest_txn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cmg_pp_rest_txn` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `createdBy` bigint(20) NOT NULL,
  `parentId` bigint(20) DEFAULT NULL,
  `parentType` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `intent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cmg_cart_1` (`createdBy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET FOREIGN_KEY_CHECKS=0;

--
-- Constraints for table `cmg_pp_rest_txn`
--
ALTER TABLE `cmg_pp_rest_txn`
	ADD CONSTRAINT `fk_cmg_pp_rest_txn_1` FOREIGN KEY (`createdBy`) REFERENCES `cmg_core_user` (`id`);

SET FOREIGN_KEY_CHECKS=1;