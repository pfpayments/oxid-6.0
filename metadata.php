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


/**
 * Metadata version
 */
$sMetadataVersion = '2.0';

/**
 * Module information
 */
$aModule = array(
    'id' => 'pfcPostFinanceCheckout',
    'title' => array(
        'de' => 'PFC :: PostFinanceCheckout',
        'en' => 'PFC :: PostFinanceCheckout'
    ),
    'description' => array(
        'de' => 'PFC PostFinanceCheckout Module',
        'en' => 'PFC PostFinanceCheckout Module'
    ),
    'thumbnail' => 'out/pictures/picture.png',
    'version' => '1.0.38',
    'author' => 'customweb GmbH',
    'url' => 'https://www.customweb.com',
    'email' => 'info@customweb.com',
    'extend' => array(
        \OxidEsales\Eshop\Application\Model\Order::class => Pfc\PostFinanceCheckout\Extend\Application\Model\Order::class,
        \OxidEsales\Eshop\Application\Model\PaymentList::class => Pfc\PostFinanceCheckout\Extend\Application\Model\PaymentList::class,
        \OxidEsales\Eshop\Application\Model\BasketItem::class => Pfc\PostFinanceCheckout\Extend\Application\Model\BasketItem::class,
        \OxidEsales\Eshop\Application\Controller\StartController::class => Pfc\PostFinanceCheckout\Extend\Application\Controller\StartController::class,
        \OxidEsales\Eshop\Application\Controller\BasketController::class => Pfc\PostFinanceCheckout\Extend\Application\Controller\BasketController::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class => Pfc\PostFinanceCheckout\Extend\Application\Controller\OrderController::class,
        \OxidEsales\Eshop\Application\Controller\Admin\LoginController::class => Pfc\PostFinanceCheckout\Extend\Application\Controller\Admin\LoginController::class,
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => Pfc\PostFinanceCheckout\Extend\Application\Controller\Admin\ModuleConfiguration::class,
        \OxidEsales\Eshop\Application\Controller\Admin\NavigationController::class => Pfc\PostFinanceCheckout\Extend\Application\Controller\Admin\NavigationController::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderList::class => Pfc\PostFinanceCheckout\Extend\Application\Controller\Admin\OrderList::class,
    ),
    'controllers' => array(
        'pfc_postFinanceCheckout_RefundJob' => Pfc\PostFinanceCheckout\Application\Controller\Admin\RefundJob::class,
        'pfc_postFinanceCheckout_Cron' => Pfc\PostFinanceCheckout\Application\Controller\Cron::class,
        'pfc_postFinanceCheckout_Pdf' => Pfc\PostFinanceCheckout\Application\Controller\Pdf::class,
        'pfc_postFinanceCheckout_Webhook' => Pfc\PostFinanceCheckout\Application\Controller\Webhook::class,
        'pfc_postFinanceCheckout_Transaction' => Pfc\PostFinanceCheckout\Application\Controller\Admin\Transaction::class,
        'pfc_postFinanceCheckout_Alert' => Pfc\PostFinanceCheckout\Application\Controller\Admin\Alert::class
    ),
    'templates' => array(
        'pfcPostFinanceCheckoutCheckoutBasket.tpl' => 'pfc/PostFinanceCheckout/Application/views/pages/pfcPostFinanceCheckoutCheckoutBasket.tpl',
        'pfcPostFinanceCheckoutCron.tpl' => 'pfc/PostFinanceCheckout/Application/views/pages/pfcPostFinanceCheckoutCron.tpl',
        'pfcPostFinanceCheckoutError.tpl' => 'pfc/PostFinanceCheckout/Application/views/pages/pfcPostFinanceCheckoutError.tpl',
        'pfcPostFinanceCheckoutTransaction.tpl' => 'pfc/PostFinanceCheckout/Application/views/admin/tpl/pfcPostFinanceCheckoutTransaction.tpl',
        'pfcPostFinanceCheckoutRefundJob.tpl' => 'pfc/PostFinanceCheckout/Application/views/admin/tpl/pfcPostFinanceCheckoutRefundJob.tpl',
        'pfcPostFinanceCheckoutOrderList.tpl' => 'pfc/PostFinanceCheckout/Application/views/admin/tpl/pfcPostFinanceCheckoutOrderList.tpl',
    ),
    'blocks' => array(
        array(
            'template' => 'page/checkout/order.tpl',
            'block' => 'shippingAndPayment',
            'file' => 'Application/views/blocks/pfcPostFinanceCheckout_checkout_order_shippingAndPayment.tpl'
        ),
        array(
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_btn_submit_bottom',
            'file' => 'Application/views/blocks/pfcPostFinanceCheckout_checkout_order_btn_submit_bottom.tpl'
        ),
        array(
            'template' => 'layout/base.tpl',
            'block' => 'base_js',
            'file' => 'Application/views/blocks/pfcPostFinanceCheckout_include_cron.tpl'
        ),
        array(
            'template' => 'login.tpl',
            'block' => 'admin_login_form',
            'file' => 'Application/views/blocks/pfcPostFinanceCheckout_include_cron.tpl'
        ),
    	array(
    		'template' => 'header.tpl',
    		'block' => 'admin_header_links',
    		'file' => 'Application/views/blocks/pfcPostFinanceCheckout_admin_header_links.tpl'
    	),
    	array(
    		'template' => 'page/account/order.tpl',
    		'block' => 'account_order_history',
    		'file' => 'Application/views/blocks/pfcPostFinanceCheckout_account_order_history.tpl'
    	),
    ),
	'settings' => array(
		array(
			'group' => 'pfcPostFinanceCheckoutPostFinance CheckoutSettings',
			'name' => 'pfcPostFinanceCheckoutSpaceId',
			'type' => 'str',
			'value' => ''
		),
		array(
			'group' => 'pfcPostFinanceCheckoutPostFinance CheckoutSettings',
			'name' => 'pfcPostFinanceCheckoutUserId',
			'type' => 'str',
			'value' => ''
		),
		array(
			'group' => 'pfcPostFinanceCheckoutPostFinance CheckoutSettings',
			'name' => 'pfcPostFinanceCheckoutAppKey',
			'type' => 'password',
			'value' => ''
		),
		array(
			'group' => 'pfcPostFinanceCheckoutShopSettings',
			'name' => 'pfcPostFinanceCheckoutEmailConfirm',
			'type' => 'bool',
			'value' => true
		),
		array(
			'group' => 'pfcPostFinanceCheckoutShopSettings',
			'name' => 'pfcPostFinanceCheckoutEnforceConsistency',
			'type' => 'bool',
			'value' => true
		),
		array(
			'group' => 'pfcPostFinanceCheckoutShopSettings',
			'name' => 'pfcPostFinanceCheckoutInvoiceDoc',
			'type' => 'bool',
			'value' => true
		),
		array(
			'group' => 'pfcPostFinanceCheckoutShopSettings',
			'name' => 'pfcPostFinanceCheckoutPackingDoc',
			'type' => 'bool',
			'value' => true
		),
		array(
			'group' => 'pfcPostFinanceCheckoutShopSettings',
			'name' => 'pfcPostFinanceCheckoutLogLevel',
			'type' => 'select',
			'value' => 'Error',
			'constraints' => 'Error|Info|Debug'
		),
		array(
			'group' => 'pfcPostFinanceCheckoutSpaceViewSettings',
			'name' => 'pfcPostFinanceCheckoutSpaceViewId',
			'type' => 'str',
			'value' => ''
		)
    ),
    'events' => array(
        'onActivate' => Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule::class . '::onActivate',
        'onDeactivate' => Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule::class . '::onDeactivate'
    )
);