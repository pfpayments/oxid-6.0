<?php
/**
 * PostFinanceCheckout OXID
 *
 * This OXID module enables to process payments with PostFinanceCheckout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html/).
 *
 * @package Whitelabelshortcut\PostFinanceCheckout
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
$sLangName = 'Italiano';

$aLang = array(
    'charset' => 'UTF-8',
    'pfcPostFinanceCheckout' => 'PFC PostFinanceCheckout',
	
	'SHOP_MODULE_GROUP_pfcPostFinanceCheckoutPostFinance CheckoutSettings' => 'PostFinance Checkout Impostazioni',
	'SHOP_MODULE_GROUP_pfcPostFinanceCheckoutShopSettings' => 'Impostazioni del negozio',
	'SHOP_MODULE_GROUP_pfcPostFinanceCheckoutSpaceViewSettings' => 'Opzioni di visualizzazione dello spazio',
	'SHOP_MODULE_pfcPostFinanceCheckoutAppKey' => 'Authentication Key',
	'SHOP_MODULE_pfcPostFinanceCheckoutUserId' => 'User Id',
    'SHOP_MODULE_pfcPostFinanceCheckoutSpaceId' => 'Space Id',
	'SHOP_MODULE_pfcPostFinanceCheckoutSpaceViewId' => 'Space View Id',
	'SHOP_MODULE_pfcPostFinanceCheckoutEmailConfirm' => 'E-mail di conferma',
	'SHOP_MODULE_pfcPostFinanceCheckoutInvoiceDoc' => 'Fattura doc',
	'SHOP_MODULE_pfcPostFinanceCheckoutPackingDoc' => 'Imballaggio Doc',
	'SHOP_MODULE_pfcPostFinanceCheckoutEnforceConsistency' => 'Imponi la coerenza',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel' => 'Log Level',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_' => ' - ',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Error' => 'Error',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Debug' => 'Debug',
	'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Info' => 'Info',
	
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutUserId' => 'L\'utente richiede l\'autorizzazione completa nello spazio a cui è collegato il negozio.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutSpaceViewId' => 'L\'ID vista spazio consente di controllare lo stile del modulo di pagamento e della pagina di pagamento all\'interno dello spazio. Nelle configurazioni multi negozio permette di adattare il modulo di pagamento a stili differenti per sub store senza richiedere uno spazio dedicato..',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutEmailConfirm' => 'È possibile disattivare l\'e-mail di conferma dell\'ordine OXID per le transazioni PostFinance Checkout.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutInvoiceDoc' => 'Puoi consentire ai clienti di scaricare le fatture nella loro area account.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutPackingDoc' => 'Puoi consentire ai clienti di scaricare i documenti di trasporto nell\'area del loro account.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutEnforceConsistency' => 'Richiedi che le voci della transazione corrispondano a quelle dell\'ordine di acquisto in Magento. Ciò potrebbe comportare che i metodi di pagamento PostFinance Checkout non siano disponibili per il cliente in alcuni casi. In cambio, è garantito che solo i dati corretti vengano trasmessi a PostFinance Checkout..',
	
	'pfc_postFinanceCheckout_Settings saved successfully.' => 'Impostazioni salvate correttamente.',
	'pfc_postFinanceCheckout_Payment methods successfully synchronized.' => 'Metodi di pagamento sincronizzati con successo.',
	'pfc_postFinanceCheckout_Webhook URL updated.' => 'URL webhook aggiornato.',
	//TODO remove uneeded
	
	'pfc_postFinanceCheckout_Download Invoice' => 'Scarica fattura',
	'pfc_postFinanceCheckout_Download Packing Slip' => 'Scarica fattura',
	'pfc_postFinanceCheckout_Delivery Fee' => 'Tassa di consegna',
	'pfc_postFinanceCheckout_Payment Fee' => 'Commissione di pagamento',
	'pfc_postFinanceCheckout_Gift Card' => 'Commissione di pagamento',
	'pfc_postFinanceCheckout_Wrapping Fee' => 'Spese di confezionamento',
	'pfc_postFinanceCheckout_Total Discount' => 'Sconto totale',
	'pfc_postFinanceCheckout_VAT' => 'VAT',
	'pfc_postFinanceCheckout_Order already exists. Please check if you have already received a confirmation, then try again.' => 'L\'ordine esiste già. Verifica di aver già ricevuto una conferma, quindi riprova.',
	'pfc_postFinanceCheckout_Unable to load transaction !id in space !space.' => 'Impossibile caricare la transazione !id nello spazio !space',
	'pfc_postFinanceCheckout_Manual Tasks (!count)' => 'Compiti manuali (!count)',
	'pfc_postFinanceCheckout_Unable to confirm order in state !state.' => 'Impossibile confermare l\'ordine nello stato !state.',
	'pfc_postFinanceCheckout_Not a PostFinance Checkout order.' => 'Non un ordine PostFinance Checkout.',
	'pfc_postFinanceCheckout_An unknown error occurred, and the order could not be loaded.' => 'Si è verificato un errore sconosciuto e non è stato possibile caricare l\'ordine.',
	'pfc_postFinanceCheckout_Successfully created and sent completion job !id.' => 'Lavoro di completamento creato e inviato con successo !id.',
	'pfc_postFinanceCheckout_Successfully created and sent void job !id.' => 'Lavoro annullato creato e inviato con successo !id.',
	'pfc_postFinanceCheckout_Successfully created and sent refund job !id.' => 'Lavoro di rimborso creato e inviato con successo !id.',
	'pfc_postFinanceCheckout_Unable to load transaction for order !id.' => 'Impossibile caricare la transazione per l\'ordine !id.',
	'pfc_postFinanceCheckout_Completions' => 'Completamenti',
	'pfc_postFinanceCheckout_Completion' => 'Completamento',
	'pfc_postFinanceCheckout_Refunds' => 'Refunds',
	'pfc_postFinanceCheckout_Voids' => 'Rimborsi',
	'pfc_postFinanceCheckout_Completion #!id' => 'Completamento #!id',
	'pfc_postFinanceCheckout_Refund #!id' => 'Refund #!id',
	'pfc_postFinanceCheckout_Void #!id' => 'Vuoto #!id',
	'pfc_postFinanceCheckout_Transaction information' => 'Informazioni sulla transazione',
	'pfc_postFinanceCheckout_Authorization amount' => 'Authorization amount',
	'pfc_postFinanceCheckout_The amount which was authorized with the PostFinance Checkout transaction.' => 'L\'importo autorizzato con la transazione PostFinance Checkout.',
	'pfc_postFinanceCheckout_Transaction #!id' => 'Transazione #!id',
	'pfc_postFinanceCheckout_Status' => 'Stato',
	'pfc_postFinanceCheckout_Status in the PostFinance Checkout system.' => 'Stato nel sistema PostFinance Checkout.',
	'pfc_postFinanceCheckout_Payment method' => 'Metodo di pagamento',
	'pfc_postFinanceCheckout_Open in your PostFinance Checkout backend.' => 'Apri nel tuo back-end PostFinance Checkout.',
	'pfc_postFinanceCheckout_Open' => 'Aprire',
	'pfc_postFinanceCheckout_PostFinance Checkout Link' => 'PostFinance Checkout Collegamento',
	
	// tpl translations
	'pfc_postFinanceCheckout_Restock' => 'Rifornire',
	'pfc_postFinanceCheckout_Total' => 'Totale',
	'pfc_postFinanceCheckout_Reset' => 'Ripristina',
	'pfc_postFinanceCheckout_Full' => 'Pieno',
	'pfc_postFinanceCheckout_Empty refund not permitted' => 'Rimborso vuoto non consentito.',
	'pfc_postFinanceCheckout_Void' => 'Vuoto',
	'pfc_postFinanceCheckout_Complete' => 'Completare',
	'pfc_postFinanceCheckout_Refund' => 'Rimborso',
	'pfc_postFinanceCheckout_Name' => 'Nome',
	'pfc_postFinanceCheckout_SKU' => 'SKU',
	'pfc_postFinanceCheckout_Quantity' => 'Quantità',
	'pfc_postFinanceCheckout_Reduction' => 'Riduzione',
	'pfc_postFinanceCheckout_Refund amount' => 'Importo rimborsato',
	
	// menu
	'pfc_postFinanceCheckout_transaction_title' => 'PostFinance Checkout Transazione'
);