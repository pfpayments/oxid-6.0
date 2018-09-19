ALTER TABLE `pfcPostFinanceCheckout_token` DROP PRIMARY KEY;
ALTER TABLE `pfcPostFinanceCheckout_token` ADD `OXID` char(32) CHARSET latin1 COLLATE latin1_general_ci DEFAULT '' PRIMARY KEY FIRST;