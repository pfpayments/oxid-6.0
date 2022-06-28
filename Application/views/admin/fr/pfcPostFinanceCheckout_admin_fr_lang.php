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
$sLangName = 'Français';

$aLang = array(
    'charset' => 'UTF-8',
    'pfcPostFinanceCheckout' => 'PFC PostFinanceCheckout',
	
	'SHOP_MODULE_GROUP_pfcPostFinanceCheckoutPostFinance CheckoutSettings' => 'PostFinance Checkout Réglages',
	'SHOP_MODULE_GROUP_pfcPostFinanceCheckoutShopSettings' => 'Paramètres de la boutique',
	'SHOP_MODULE_GROUP_pfcPostFinanceCheckoutSpaceViewSettings' => 'Options d\'affichage de l\'espace',
	'SHOP_MODULE_pfcPostFinanceCheckoutAppKey' => 'Authentication Key',
	'SHOP_MODULE_pfcPostFinanceCheckoutUserId' => 'User Id',
    'SHOP_MODULE_pfcPostFinanceCheckoutSpaceId' => 'Space Id',
	'SHOP_MODULE_pfcPostFinanceCheckoutSpaceViewId' => 'Space View Id',
	'SHOP_MODULE_pfcPostFinanceCheckoutEmailConfirm' => 'E-mail de confirmation',
	'SHOP_MODULE_pfcPostFinanceCheckoutInvoiceDoc' => 'Document de facturation',
	'SHOP_MODULE_pfcPostFinanceCheckoutPackingDoc' => 'Document d\'emballage',
	'SHOP_MODULE_pfcPostFinanceCheckoutEnforceConsistency' => 'Appliquer la cohérence',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel' => 'Log Level',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_' => ' - ',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Error' => 'Error',
    'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Debug' => 'Debug',
	'SHOP_MODULE_pfcPostFinanceCheckoutLogLevel_Info' => 'Info',
	
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutUserId' => 'L\'utilisateur a besoin d\'une autorisation complète dans l\'espace auquel la boutique est liée.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutSpaceViewId' => 'L\'ID de vue de l\'espace permet de contrôler le style du formulaire de paiement et de la page de paiement dans l\'espace. Dans les configurations multi-boutiques, cela permet d\'adapter le formulaire de paiement à différents styles par sous-magasin sans nécessiter d\'espace dédié.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutEmailConfirm' => 'Vous pouvez désactiver l\'e-mail de confirmation de commande OXID pour les transactions PostFinance Checkout.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutInvoiceDoc' => 'Vous pouvez autoriser les clients à télécharger des factures dans leur espace de compte.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutPackingDoc' => 'Vous pouvez autoriser les clients à télécharger les bordereaux d\'expédition dans leur espace de compte.',
	'HELP_SHOP_MODULE_pfcPostFinanceCheckoutEnforceConsistency' => 'Exiger que les rubriques de la transaction correspondent à celles du bon de commande dans Magento. Il peut en résulter que les méthodes de paiement PostFinance Checkout ne sont pas disponibles pour le client dans certains cas. En retour, il est garanti que seules des données correctes sont transmises à PostFinance Checkout.',
	
	'pfc_postFinanceCheckout_Settings saved successfully.' => 'Paramètres enregistrés avec succès.',
	'pfc_postFinanceCheckout_Payment methods successfully synchronized.' => 'Modes de paiement synchronisés avec succès.',
	'pfc_postFinanceCheckout_Webhook URL updated.' => 'URL du webhook mise à jour.',
	//TODO remove uneeded
	
	'pfc_postFinanceCheckout_Download Invoice' => 'Télécharger la facture',
	'pfc_postFinanceCheckout_Download Packing Slip' => 'Télécharger le bordereau d\'expédition',
	'pfc_postFinanceCheckout_Delivery Fee' => 'Frais de livraison',
	'pfc_postFinanceCheckout_Payment Fee' => 'Frais de paiement',
	'pfc_postFinanceCheckout_Gift Card' => 'Carte cadeau',
	'pfc_postFinanceCheckout_Wrapping Fee' => 'Frais d\'emballage',
	'pfc_postFinanceCheckout_Total Discount' => 'Remise totale',
	'pfc_postFinanceCheckout_VAT' => 'VAT',
	'pfc_postFinanceCheckout_Order already exists. Please check if you have already received a confirmation, then try again.' => 'La commande existe déjà. Veuillez vérifier si vous avez déjà reçu une confirmation, puis réessayez.',
	'pfc_postFinanceCheckout_Unable to load transaction !id in space !space.' => 'Impossible de charger la transaction !id dans l\'espace !space',
	'pfc_postFinanceCheckout_Manual Tasks (!count)' => 'Tâches manuelles (!count)',
	'pfc_postFinanceCheckout_Unable to confirm order in state !state.' => 'Impossible de confirmer la commande dans l\'état !state.',
	'pfc_postFinanceCheckout_Not a PostFinance Checkout order.' => 'Pas une commande PostFinance Checkout.',
	'pfc_postFinanceCheckout_An unknown error occurred, and the order could not be loaded.' => 'Une erreur inconnue s\'est produite et la commande n\'a pas pu être chargée.',
	'pfc_postFinanceCheckout_Successfully created and sent completion job !id.' => 'Tâche d\'achèvement créée et envoyée avec succès !id.',
	'pfc_postFinanceCheckout_Successfully created and sent void job !id.' => 'Travail annulé créé et envoyé avec succès !id.',
	'pfc_postFinanceCheckout_Successfully created and sent refund job !id.' => 'La tâche de remboursement a bien été créée et envoyée !id.',
	'pfc_postFinanceCheckout_Unable to load transaction for order !id.' => 'Impossible de charger la transaction pour la commande !id.',
	'pfc_postFinanceCheckout_Completions' => 'Achèvements',
	'pfc_postFinanceCheckout_Completion' => 'Achèvement',
	'pfc_postFinanceCheckout_Refunds' => 'Remboursements',
	'pfc_postFinanceCheckout_Voids' => 'Vides',
	'pfc_postFinanceCheckout_Completion #!id' => 'Achèvement #!id',
	'pfc_postFinanceCheckout_Refund #!id' => 'Rembourser #!id',
	'pfc_postFinanceCheckout_Void #!id' => 'Vide #!id',
	'pfc_postFinanceCheckout_Transaction information' => 'Informations sur les transactions',
	'pfc_postFinanceCheckout_Authorization amount' => 'Montant de l\'autorisation',
	'pfc_postFinanceCheckout_The amount which was authorized with the PostFinance Checkout transaction.' => 'Le montant qui a été autorisé avec la transaction PostFinance Checkout.',
	'pfc_postFinanceCheckout_Transaction #!id' => 'Transaction #!id',
	'pfc_postFinanceCheckout_Status' => 'Statut',
	'pfc_postFinanceCheckout_Status in the PostFinance Checkout system.' => 'Statut dans le système PostFinance Checkout.',
	'pfc_postFinanceCheckout_Payment method' => 'Mode de paiement',
	'pfc_postFinanceCheckout_Open in your PostFinance Checkout backend.' => 'Ouvrir dans votre backend PostFinance Checkout.',
	'pfc_postFinanceCheckout_Open' => 'Ouvrir',
	'pfc_postFinanceCheckout_PostFinance Checkout Link' => 'Lien PostFinance Checkout',
	
	// tpl translations
	'pfc_postFinanceCheckout_Restock' => 'Réapprovisionner',
	'pfc_postFinanceCheckout_Total' => 'Total',
	'pfc_postFinanceCheckout_Reset' => 'Réinitialiser',
	'pfc_postFinanceCheckout_Full' => 'Plein',
	'pfc_postFinanceCheckout_Empty refund not permitted' => 'Remboursement vide non autorisé.',
	'pfc_postFinanceCheckout_Void' => 'Vide',
	'pfc_postFinanceCheckout_Complete' => 'Complet',
	'pfc_postFinanceCheckout_Refund' => 'Rembourser',
	'pfc_postFinanceCheckout_Name' => 'Nom',
	'pfc_postFinanceCheckout_SKU' => 'SKU',
	'pfc_postFinanceCheckout_Quantity' => 'Quantité',
	'pfc_postFinanceCheckout_Reduction' => 'Réduction',
	'pfc_postFinanceCheckout_Refund amount' => 'Montant du remboursement',
	
	// menu
	'pfc_postFinanceCheckout_transaction_title' => 'PostFinance Checkout Transaction'
);