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


$sLangName = 'Deutsch';

$aLang = array(
    'charset' => 'UTF-8',
    'pfcPostFinanceCheckout' => 'PFC PostFinanceCheckout',

    'SHOP_MODULE_GROUP_pfcPostFinanceCheckoutPostFinance CheckoutSettings' => 'PostFinance Checkout Einstellungen',
	'SHOP_MODULE_GROUP_pfcPostFinanceCheckoutShopSettings' => 'Shop Einstellungen',
	'SHOP_MODULE_GROUP_pfcPostFinanceCheckoutSpaceViewId' => 'Space View Id',
    'SHOP_MODULE_pfcPostFinanceCheckoutAppKey' => 'Authentication Key',
	'SHOP_MODULE_pfcPostFinanceCheckoutUserId' => 'Benutzer Id',
    'SHOP_MODULE_pfcPostFinanceCheckoutSpaceId' => 'Space Id',
	'SHOP_MODULE_pfcPostFinanceCheckoutSpaceViewSettings' => 'Space View Optionen',
    'SHOP_MODULE_pfcPostFinanceCheckoutEmailConfirm' => 'Email Bestätigung',
    'SHOP_MODULE_pfcPostFinanceCheckoutInvoiceDoc' => 'Rechnung',
    'SHOP_MODULE_pfcPostFinanceCheckoutPackingDoc' => 'Lieferschein',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel' => 'Log Level',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_' => ' - ',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Error' => 'Error',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Debug' => 'Debug',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Info' => 'Info',
	
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutUserId' => 'Der Benutzer benötigt volle Berechtigungen auf dem verbundenen space.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutSpaceViewId' => 'Die Space View ID lässt das Gestalten der Zahlungsformulare und -seiten innerhalb eines Spaces. Dies kann u.A. für Multishopsysteme die unterschiedliche Aussehen haben sollten verwendet werden.',	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutEmailConfirm' => 'You may deactivate the OXID order confirmation email for PostFinance Checkout transactions.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutInvoiceDoc' => 'Sie können ihren Kunden erlauben Rechnungen für Ihre Bestellungen im Frontend-Bereich herunterzuladen.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutPackingDoc' => 'Sie können ihren Kunden erlauben Lieferscheine für Ihre Bestellungen im Frontend-Bereich herunterzuladen.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutEmailConfirm' => 'Sie können OXID Bestellbestätigungen für PostFinance Checkout Transaktionen unterbinden.',
	
	'pfc_postFinanceCheckout_Settings saved successfully.' => 'Die Einstellungen wurden erfolgreich gespeichert.',
	'pfc_postFinanceCheckout_Payment methods successfully synchronized.' => 'Die Zahlarten wurden synchronisiert.',
	'pfc_postFinanceCheckout_Webhook URL updated.' => 'Webhook URL wurde aktualisiert.',
	//TODO remove unneeded
	
	'pfc_postFinanceCheckout_Download Invoice' => 'Rechnung herunterladen',
	'pfc_postFinanceCheckout_Download Packing Slip' => 'Lieferschein herunterladen',
	'pfc_postFinanceCheckout_Delivery Fee' => 'Liefergebühr',
	'pfc_postFinanceCheckout_Payment Fee' => 'Zahlartgebühr',
	'pfc_postFinanceCheckout_Gift Card' => 'Geschenkkarte',
	'pfc_postFinanceCheckout_Wrapping Fee' => 'Packgebühr',
	'pfc_postFinanceCheckout_Total Discount' => 'Gesamte Rabatte',
	'pfc_postFinanceCheckout_VAT' => 'MwSt.',
	'pfc_postFinanceCheckout_Order already exists. Please check if you have already received a confirmation, then try again.' => 'Die Bestellung existiert bereits. Bitte prüfen Sie ob sie eine Bestätigung erhalten haben, und versuchen Sie es erneut wenn nicht.',
	'pfc_postFinanceCheckout_Unable to load transaction !id in space !space.' => 'Transaktion konnte nicht geladen werden (Transaktion: !id. Space: !space)',
	'pfc_postFinanceCheckout_Manual Tasks (!count)' => 'Manuelle Aufgaben (!count)',
	'pfc_postFinanceCheckout_Unable to confirm order in state !state.' => 'Bestellung im status !state kann nicht bestätigt werden.',
	'pfc_postFinanceCheckout_Not a PostFinance Checkout order.' => 'Nicht eine PostFinance Checkout Bestellung.',
	'pfc_postFinanceCheckout_An unknown error occurred, and the order could not be loaded.' => 'Ein unbekannter Fehler ist aufgetreten und die Bestellung konnte nicht geladen werden.',
	'pfc_postFinanceCheckout_Successfully created and sent completion job !id.' => 'Bestätigung (!id) erfolgreich erstellt und versandt.',
	'pfc_postFinanceCheckout_Successfully created and sent void job !id.' => 'Storno (!id) erfolgreich erstellt und versandt.',
	'pfc_postFinanceCheckout_Successfully created and sent refund job !id.' => 'Rückerstattung (!id) erfolgreich erstellt und versandt.',
	'pfc_postFinanceCheckout_Unable to load transaction for order !id.' => 'Transaktion für die Bestellung !id konnte nicht geladen werden.',
	'pfc_postFinanceCheckout_Completions' => 'Bestätigungen',
	'pfc_postFinanceCheckout_Completion #!id' => 'Bestätigung #!id',
	'pfc_postFinanceCheckout_Refunds' => 'Rückerstattungen',
	'pfc_postFinanceCheckout_Refund #!id' => 'Rückerstattung #!id',
	'pfc_postFinanceCheckout_Voids' => 'Stornos',
	'pfc_postFinanceCheckout_Void #!id' => 'Storno #!id',
	'pfc_postFinanceCheckout_Transaction information' => 'Transaktionsinformation',
	'pfc_postFinanceCheckout_Authorization amount' => 'Authorisierter Betrag',
	'pfc_postFinanceCheckout_The amount which was authorized with the PostFinance Checkout transaction.' => 'Der Betrag der durch die PostFinance Checkout transaktion authorisiert wurde.',
	'pfc_postFinanceCheckout_Transaction #!id' => 'Transaktion #!id',
	'pfc_postFinanceCheckout_Status' => 'Status',
	'pfc_postFinanceCheckout_Status in the PostFinance Checkout system.' => 'Status in dem PostFinance Checkout system.',
	'pfc_postFinanceCheckout_Payment method' => 'Payment method',
	'pfc_postFinanceCheckout_Open in your PostFinance Checkout backend.' => 'Öffne im PostFinance Checkout backend.',
	'pfc_postFinanceCheckout_Open' => 'Öffnen',
	'pfc_postFinanceCheckout_PostFinance Checkout Link' => 'PostFinance Checkout Link',
	
	// tpl translations
	'pfc_postFinanceCheckout_Restock' => 'Lagerbestand wiederherstellen',
	'pfc_postFinanceCheckout_Total' => 'Total',
	'pfc_postFinanceCheckout_Reset' => 'Zurücksetzen',
	'pfc_postFinanceCheckout_Full' => 'Volle Rückerstattung',
	'pfc_postFinanceCheckout_Empty refund not permitted' => 'Eine leere Rückerstattung kann nicht erstellt werden.',
	'pfc_postFinanceCheckout_Void' => 'Storno',
	'pfc_postFinanceCheckout_Complete' => 'Bestätigen',
	'pfc_postFinanceCheckout_Refund' => 'Rückerstatten',
	'pfc_postFinanceCheckout_Name' => 'Name',
	'pfc_postFinanceCheckout_SKU' => 'SKU',
	'pfc_postFinanceCheckout_Quantity' => 'Quantität',
	'pfc_postFinanceCheckout_Reduction' => 'Reduktion',
	'pfc_postFinanceCheckout_Refund amount' => 'Rückerstattungsbetrag',
	
	// menu
	'pfc_postFinanceCheckout_transaction_title' => 'PostFinance Checkout Transaktion');