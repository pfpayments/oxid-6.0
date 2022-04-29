<?php
/**
 * PostFinanceCheckout OXID
 *
 * This OXID module enables to process payments with PostFinanceCheckout (https://www.postfinance.ch/checkout/).
 *
 * @package Whitelabelshortcut\PostFinanceCheckout
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */

$sLangName = 'Français';

$aLang = array(
    'charset' => 'UTF-8',
	
	'pfc_postFinanceCheckout_Downloads' => 'Télécharger des documents',
	'pfc_postFinanceCheckout_Download Invoice' => 'Télécharger la facture',
	'pfc_postFinanceCheckout_Download Packing Slip' => 'Télécharger le bon de livraison',
	'pfc_postFinanceCheckout_Delivery Fee' => 'Frais de livraison',
	'pfc_postFinanceCheckout_Payment Fee' => 'Frais de paiement',
	'pfc_postFinanceCheckout_Gift Card' => 'Carte cadeau',
	'pfc_postFinanceCheckout_Wrapping Fee' => 'Frais d\'emballage',
	'pfc_postFinanceCheckout_Total Discount' => 'Remise totale',
	'pfc_postFinanceCheckout_VAT' => 'T.V.A.',
	'pfc_postFinanceCheckout_Order already exists. Please check if you have already received a confirmation, then try again.' => 'La commande existe déjà. Veuillez vérifier si vous avez déjà reçu une confirmation, puis réessayez.',
	'pfc_postFinanceCheckout_Unable to load transaction !id in space !space.' => 'Impossible de charger la transaction !id dans l\'espace !space',
	'pfc_postFinanceCheckout_Manual Tasks (!count)' => 'Tâches manuelles (!count)',
	'pfc_postFinanceCheckout_Unable to confirm order in state !state.' => 'Impossible de confirmer la commande dans l\'état !state.',
	'pfc_postFinanceCheckout_Not a PostFinance Checkout order.' => 'Pas une commande PostFinance Checkout.',
	'pfc_postFinanceCheckout_An unknown error occurred, and the order could not be loaded.' => 'Une erreur inconnue s\'est produite et la commande n\'a pas pu être chargée.',
	'pfc_postFinanceCheckout_Successfully created and sent completion job !id.' => 'Tâche d\'achèvement créée et envoyée avec succès !id.',
	'pfc_postFinanceCheckout_Successfully created and sent void job !id.' => 'Travail annulé créé et envoyé avec succès !id.',
	'pfc_postFinanceCheckout_Successfully created and sent refund job !id.' => 'La tâche de remboursement !id a bien été créée et envoyée.',
	'pfc_postFinanceCheckout_Unable to load transaction for order !id.' => 'Impossible de charger la transaction pour la commande !id.',
	'pfc_postFinanceCheckout_Completions' => 'Achèvements',
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
	'pfc_postFinanceCheckout_Status in the PostFinance Checkout system.' => 'Statut dans le système PostFinance Checkout system.',
	'pfc_postFinanceCheckout_Payment method' => 'Mode de paiement',
	'pfc_postFinanceCheckout_Open in your PostFinance Checkout backend.' => 'Ouvrir dans votre backend PostFinance Checkout.',
	'pfc_postFinanceCheckout_Open' => 'Ouvrir',
	'pfc_postFinanceCheckout_PostFinance Checkout Link' => 'PostFinance Checkout Lien',
	'pfc_postFinanceCheckout_You must agree to the terms and conditions.' => 'Vous devez accepter les termes et conditions.',
	'pfc_postFinanceCheckout_Rounding Adjustment' => 'Ajustement d\'arrondi',
	'pfc_postFinanceCheckout_Totals mismatch, please contact merchant or use another payment method.' => 'Les totaux ne correspondent pas, veuillez contacter le marchand ou utiliser un autre mode de paiement.',
	
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
	'pfc_postFinanceCheckout_SKU' => 'UGS',
	'pfc_postFinanceCheckout_Quantity' => 'Quantité',
	'pfc_postFinanceCheckout_Reduction' => 'Réduction',
	'pfc_postFinanceCheckout_Refund amount' => 'Montant du remboursement',
	
	// menu
	'pfc_postFinanceCheckout_transaction_title' => 'PostFinance Checkout Transaction'
);