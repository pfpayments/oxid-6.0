ALTER TABLE `pfcPostFinanceCheckout_transaction`
	MODIFY `OXID` char(32) CHARSET latin1 COLLATE latin1_general_ci DEFAULT '',
	MODIFY `OXORDERID` char(32) CHARSET latin1 COLLATE latin1_general_ci DEFAULT '',
	MODIFY `PFCUPDATED` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	MODIFY `PFCVERSION` int(11) DEFAULT '0';
	
ALTER TABLE `pfcPostFinanceCheckout_completionjob`
	MODIFY `OXID` char(32) CHARSET latin1 COLLATE latin1_general_ci DEFAULT '',
	MODIFY `OXORDERID` char(32) CHARSET latin1 COLLATE latin1_general_ci DEFAULT '',
	MODIFY `PFCUPDATED` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
	
ALTER TABLE `pfcPostFinanceCheckout_voidjob`
	MODIFY `OXID` char(32) CHARSET latin1 COLLATE latin1_general_ci DEFAULT '',
	MODIFY `OXORDERID` char(32) CHARSET latin1 COLLATE latin1_general_ci DEFAULT '',
	MODIFY `PFCUPDATED` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
	
ALTER TABLE `pfcPostFinanceCheckout_cron`
	MODIFY `OXID` char(32) CHARSET latin1 COLLATE latin1_general_ci DEFAULT '';

ALTER TABLE `pfcPostFinanceCheckout_refundjob`
	MODIFY `OXID` char(32) CHARSET latin1 COLLATE latin1_general_ci DEFAULT '',
	MODIFY `OXORDERID` char(32) CHARSET latin1 COLLATE latin1_general_ci DEFAULT '',
	MODIFY `PFCUPDATED` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `pfcPostFinanceCheckout_alert`
	MODIFY `PFCUPDATED` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `pfcPostFinanceCheckout_token` (
  `PFCTOKENID` bigint(20) unsigned NOT NULL,
  `PFCSTATE` varchar(255) NOT NULL,
  `PFCSPACEID` bigint(20) unsigned NOT NULL,
  `PFCNAME` longtext,
  `PFCCUSTOMERID` bigint(20) unsigned NOT NULL,
  `PFCPAYMENTMETHODID` bigint(20) unsigned NOT NULL,
  `PFCCONNECTORID` bigint(20) unsigned NOT NULL,
  `PFCUPDATED` TIMESTAMP NOT NULL DEFAULT now() ON UPDATE now(),
  PRIMARY KEY (`PFCTOKENID`),
  INDEX (`PFCTOKENID`, `PFCSPACEID`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;