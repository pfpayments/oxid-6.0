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
	
	'pfc_postFinanceCheckout_Downloads' => 'Scarica documenti',
	'pfc_postFinanceCheckout_Download Invoice' => 'Scarica fattura',
	'pfc_postFinanceCheckout_Download Packing Slip' => 'Scarica il documento di trasporto',
	'pfc_postFinanceCheckout_Delivery Fee' => 'Tassa di consegna',
	'pfc_postFinanceCheckout_Payment Fee' => 'Commissione di pagamento',
	'pfc_postFinanceCheckout_Gift Card' => 'Carta regalo',
	'pfc_postFinanceCheckout_Wrapping Fee' => 'Spese di imballaggio',
	'pfc_postFinanceCheckout_Total Discount' => 'Sconto totale',
	'pfc_postFinanceCheckout_VAT' => 'I.V.A.',
	'pfc_postFinanceCheckout_Order already exists. Please check if you have already received a confirmation, then try again.' => 'L\'ordine esiste già. Verifica di aver già ricevuto una conferma, quindi riprova.',
	'pfc_postFinanceCheckout_Unable to load transaction !id in space !space.' => 'Impossibile caricare la transazione !id nello spazio !space',
	'pfc_postFinanceCheckout_Manual Tasks (!count)' => 'Attività manuali (!count)',
	'pfc_postFinanceCheckout_Unable to confirm order in state !state.' => 'Impossibile confermare l\'ordine nello stato !state.',
	'pfc_postFinanceCheckout_Not a PostFinance Checkout order.' => 'Non un ordine PostFinance Checkout.',
	'pfc_postFinanceCheckout_An unknown error occurred, and the order could not be loaded.' => 'Si è verificato un errore sconosciuto e non è stato possibile caricare l\'ordine.',
	'pfc_postFinanceCheckout_Successfully created and sent completion job !id.' => 'Processo di completamento creato e inviato correttamente !id.',
	'pfc_postFinanceCheckout_Successfully created and sent void job !id.' => 'Lavoro vuoto creato e inviato correttamente !id.',
	'pfc_postFinanceCheckout_Successfully created and sent refund job !id.' => 'Processo di rimborso creato e inviato con successo !id.',
	'pfc_postFinanceCheckout_Unable to load transaction for order !id.' => 'Impossibile caricare la transazione per l\'ordine !id.',
	'pfc_postFinanceCheckout_Completions' => 'Completamenti',
	'pfc_postFinanceCheckout_Refunds' => 'Rimborsi',
	'pfc_postFinanceCheckout_Voids' => 'Vuoti',
	'pfc_postFinanceCheckout_Completion #!id' => 'Completamento #!id',
	'pfc_postFinanceCheckout_Refund #!id' => 'Rimborso #!id',
	'pfc_postFinanceCheckout_Void #!id' => 'Vuoto #!id',
	'pfc_postFinanceCheckout_Transaction information' => 'Informazioni sulla transazione',
	'pfc_postFinanceCheckout_Authorization amount' => 'Importo dell\'autorizzazione',
	'pfc_postFinanceCheckout_The amount which was authorized with the PostFinance Checkout transaction.' => 'L\'importo autorizzato con la transazione PostFinance Checkout.',
	'pfc_postFinanceCheckout_Transaction #!id' => 'Transazione #!id',
	'pfc_postFinanceCheckout_Status' => 'Stato',
	'pfc_postFinanceCheckout_Status in the PostFinance Checkout system.' => 'Stato nel sistema PostFinance Checkout.',
	'pfc_postFinanceCheckout_Payment method' => 'Metodo di pagamento',
	'pfc_postFinanceCheckout_Open in your PostFinance Checkout backend.' => 'Apri nel tuo back-end PostFinance Checkout.',
	'pfc_postFinanceCheckout_Open' => 'Aprire',
	'pfc_postFinanceCheckout_PostFinance Checkout Link' => 'PostFinance Checkout Collegamento',
	'pfc_postFinanceCheckout_You must agree to the terms and conditions.' => 'Devi accettare i termini e le condizioni.',
	'pfc_postFinanceCheckout_Rounding Adjustment' => 'Regolazione dell\'arrotondamento',
	'pfc_postFinanceCheckout_Totals mismatch, please contact merchant or use another payment method.' => 'Totali non corrispondenti, contatta il commerciante o utilizza un altro metodo di pagamento.',
	
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