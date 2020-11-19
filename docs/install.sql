CREATE TABLE IF NOT EXISTS `pfcPostFinanceCheckout_transaction` (
  `OXID` char(32) NOT NULL,
  `OXORDERID` char(32) NOT NULL,
  `PFCTRANSACTIONID` bigint(20) unsigned NOT NULL,
  `PFCSTATE` varchar(255) NOT NULL,
  `PFCSPACEID` bigint(20) unsigned NOT NULL,
  `PFCSPACEVIEWID` bigint(20) unsigned DEFAULT NULL,
  `PFCFAILUREREASON` longtext,
  `PFCTEMPBASKET` longtext,
  `PFCVERSION` int(11) NOT NULL DEFAULT 0,
  `PFCUPDATED` TIMESTAMP NOT NULL DEFAULT now() ON UPDATE now(),
  PRIMARY KEY (`OXID`),
  UNIQUE KEY `unq_transaction_id_space_id` (`PFCTRANSACTIONID`,`PFCSPACEID`),
  UNIQUE KEY `unq_order_id` (`OXORDERID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `pfcPostFinanceCheckout_completionjob` (
  `OXID` char(32) NOT NULL,
  `OXORDERID` char(32) NOT NULL,
  `PFCTRANSACTIONID` bigint(20) unsigned NOT NULL,
  `PFCJOBID` bigint(20) unsigned,
  `PFCSTATE` varchar(255) NOT NULL,
  `PFCSPACEID` bigint(20) unsigned NOT NULL,
  `PFCFAILUREREASON` longtext,
  `PFCUPDATED` TIMESTAMP NOT NULL DEFAULT now() ON UPDATE now(),
  PRIMARY KEY (`OXID`),
  INDEX `unq_job_id_space_id` (`PFCJOBID`,`PFCSPACEID`),
  INDEX `idx_order_id` (`OXORDERID`),
  INDEX `idx_state` (`PFCSTATE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `pfcPostFinanceCheckout_voidjob` (
  `OXID` char(32) NOT NULL,
  `OXORDERID` char(32) NOT NULL,
  `PFCTRANSACTIONID` bigint(20) unsigned NOT NULL,
  `PFCJOBID` bigint(20) unsigned,
  `PFCSTATE` varchar(255) NOT NULL,
  `PFCSPACEID` bigint(20) unsigned NOT NULL,
  `PFCFAILUREREASON` longtext,
  `PFCUPDATED` TIMESTAMP NOT NULL DEFAULT now() ON UPDATE now(),
  PRIMARY KEY (`OXID`),
  INDEX `unq_job_id_space_id` (`PFCJOBID`,`PFCSPACEID`),
  INDEX `idx_order_id` (`OXORDERID`),
  INDEX `idx_state` (`PFCSTATE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `pfcPostFinanceCheckout_refundjob` (
  `OXID` char(32) NOT NULL,
  `OXORDERID` char(32) NOT NULL,
  `PFCTRANSACTIONID` bigint(20) unsigned NOT NULL,
  `PFCJOBID` bigint(20) unsigned,
  `PFCSTATE` varchar(255) NOT NULL,
  `PFCSPACEID` bigint(20) unsigned NOT NULL,
  `FORMREDUCTIONS` longtext,
  `PFCRESTOCK` bool NOT NULL,
  `PFCFAILUREREASON` longtext,
  `PFCUPDATED` TIMESTAMP NOT NULL DEFAULT now() ON UPDATE now(),
  PRIMARY KEY (`OXID`),
  INDEX `unq_job_id_space_id` (`PFCJOBID`,`PFCSPACEID`),
  INDEX `idx_order_id` (`OXORDERID`),
  INDEX `idx_state` (`PFCSTATE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `pfcPostFinanceCheckout_cron` (
  `OXID` char(32) NOT NULL,
  `PFCFAILUREREASON` longtext,
  `PFCSTATE` char(7),
  `PFCSCHEDULED` DATETIME NOT NULL,
  `PFCSTARTED` DATETIME,
  `PFCCOMPLETED` DATETIME,
  `PFCCONSTRAINT` SMALLINT,
  PRIMARY KEY (`OXID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `pfcPostFinanceCheckout_alert` (
  `PFCKEY` varchar(11) NOT NULL,
  `PFCFUNC` varchar(20) NOT NULL,
  `PFCTARGET` varchar(20) NOT NULL,
  `PFCCOUNT` int unsigned DEFAULT NULL,
  `PFCUPDATED` TIMESTAMP NOT NULL DEFAULT now() ON UPDATE now(),
  PRIMARY KEY (`PFCKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `pfcPostFinanceCheckout_alert` (`PFCKEY`, `PFCFUNC`, `PFCTARGET`, `PFCCOUNT`) VALUES ('manual_task', 'manualtask', '_parent', 0);

CREATE INDEX idx_pfc_oxorder_oxtransstatus ON `oxorder` (`OXTRANSSTATUS`);