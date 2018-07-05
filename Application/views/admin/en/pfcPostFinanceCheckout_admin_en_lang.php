<?php
/**
 * PostFinanceCheckout OXID
 *
 * This OXID module enables to process payments with PostFinanceCheckout (https://www.postfinance.ch/).
 *
 * @package Whitelabelshortcut\PostFinanceCheckout
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
$sLangName = 'English';

$aLang = array(
    'charset' => 'UTF-8',
    'pfcPostFinanceCheckout' => 'PFC PostFinanceCheckout',

    'SHOP_MODULE_GROUP_pfcPostFinanceCheckoutSettings' => 'Shop settings',
    'SHOP_MODULE_GROUP_pfcPostFinanceCheckoutGlobalSettings' => 'Global settings',
    'SHOP_MODULE_pfcPostFinanceCheckoutAppKey' => 'App Key',
    'SHOP_MODULE_pfcPostFinanceCheckoutUserId' => 'User Id',
    'SHOP_MODULE_pfcPostFinanceCheckoutSpaceId' => 'Space Id',
    'SHOP_MODULE_pfcPostFinanceCheckoutSpaceViewId' => 'Space View Id',
    'SHOP_MODULE_pfcPostFinanceCheckoutEmailConfirm' => 'Email Confirm',
    'SHOP_MODULE_pfcPostFinanceCheckoutInvoiceDoc' => 'Invoice Doc',
    'SHOP_MODULE_pfcPostFinanceCheckoutPackingDoc' => 'Packing Doc',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel' => 'Log Level',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_' => ' - ',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Error' => 'Error',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Debug' => 'Debug',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Info' => 'Info',
	//TODO remove uneeded
	
	'pfc_postFinanceCheckout_Download Invoice' => 'Download Invoice',
	'pfc_postFinanceCheckout_Download Packing Slip' => 'Download Packing Slip',
	'pfc_postFinanceCheckout_Delivery Fee' => 'Delivery Fee',
	'pfc_postFinanceCheckout_Payment Fee' => 'Payment Fee',
	'pfc_postFinanceCheckout_Gift Card' => 'Gift Card',
	'pfc_postFinanceCheckout_Wrapping Fee' => 'Wrapping Fee',
	'pfc_postFinanceCheckout_Total Discount' => 'Total Discount',
	'pfc_postFinanceCheckout_VAT' => 'VAT',
	'pfc_postFinanceCheckout_Order already exists. Please check if you have already received a confirmation, then try again.' => 'Order already exists. Please check if you have already received a confirmation, then try again.',
	'pfc_postFinanceCheckout_Unable to load transaction !id in space !space.' => 'Unable to load transaction !id in space !space',
	'pfc_postFinanceCheckout_Manual Tasks (!count)' => 'Manual Tasks (!count)',
	'pfc_postFinanceCheckout_Unable to confirm order in state !state.' => 'Unable to confirm order in state !state.',
	'pfc_postFinanceCheckout_Not a PostFinance Checkout order.' => 'Not a PostFinance Checkout order.',
	'pfc_postFinanceCheckout_An unknown error occurred, and the order could not be loaded.' => 'An unknown error occurred, and the order could not be loaded.',
	'pfc_postFinanceCheckout_Successfully created and sent completion job !id.' => 'Successfully created and sent completion job !id.',
	'pfc_postFinanceCheckout_Successfully created and sent void job !id.' => 'Successfully created and sent void job !id.',
	'pfc_postFinanceCheckout_Successfully created and sent refund job !id.' => 'Successfully created and sent refund job !id.',
	'pfc_postFinanceCheckout_Unable to load transaction for order !id.' => 'Unable to load transaction for order !id.',
	'pfc_postFinanceCheckout_Completions' => 'Completions',
	'pfc_postFinanceCheckout_Completion' => 'Completion',
	'pfc_postFinanceCheckout_Refunds' => 'Refunds',
	'pfc_postFinanceCheckout_Voids' => 'Voids',
	'pfc_postFinanceCheckout_Completion #!id' => 'Completion #!id',
	'pfc_postFinanceCheckout_Refund #!id' => 'Refund #!id',
	'pfc_postFinanceCheckout_Void #!id' => 'Void #!id',
	'pfc_postFinanceCheckout_Transaction information' => 'Transaction information',
	'pfc_postFinanceCheckout_Authorization amount' => 'Authorization amount',
	'pfc_postFinanceCheckout_The amount which was authorized with the PostFinance Checkout transaction.' => 'The amount which was authorized with the PostFinance Checkout transaction.',
	'pfc_postFinanceCheckout_Transaction #!id' => 'Transaction #!id',
	'pfc_postFinanceCheckout_Status' => 'Status',
	'pfc_postFinanceCheckout_Status in the PostFinance Checkout system.' => 'Status in the PostFinance Checkout system.',
	'pfc_postFinanceCheckout_Payment method' => 'Payment method',
	'pfc_postFinanceCheckout_Open in your PostFinance Checkout backend.' => 'Open in your PostFinance Checkout backend.',
	'pfc_postFinanceCheckout_Open' => 'Open',
	'pfc_postFinanceCheckout_PostFinance Checkout Link' => 'PostFinance Checkout Link',
	
	// tpl translations
	'pfc_postFinanceCheckout_Restock' => 'Restock',
	'pfc_postFinanceCheckout_Total' => 'Total',
	'pfc_postFinanceCheckout_Reset' => 'Reset',
	'pfc_postFinanceCheckout_Full' => 'Full',
	'pfc_postFinanceCheckout_Empty refund not permitted' => 'Empty refund not permitted.',
	'pfc_postFinanceCheckout_Void' => 'Void',
	'pfc_postFinanceCheckout_Complete' => 'Complete',
	'pfc_postFinanceCheckout_Refund' => 'Refund',
	'pfc_postFinanceCheckout_Name' => 'Name',
	'pfc_postFinanceCheckout_SKU' => 'SKU',
	'pfc_postFinanceCheckout_Quantity' => 'Quantity',
	'pfc_postFinanceCheckout_Reduction' => 'Reduction',
	'pfc_postFinanceCheckout_Refund amount' => 'Refund amount',
	
	// menu
	'pfc_postFinanceCheckout_transaction_title' => 'PostFinance Checkout Transaction'
);