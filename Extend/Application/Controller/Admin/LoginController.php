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


namespace Pfc\PostFinanceCheckout\Extend\Application\Controller\Admin;

use Pfc\PostFinanceCheckout\Application\Controller\Cron;

/**
 * Class BasketItem.
 * Extends \OxidEsales\Eshop\Application\Controller\Admin\LoginController.
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\Admin\LoginController
 */
class LoginController extends LoginController_parent
{
    public function render()
    {
        $this->_aViewData['pfcCronUrl'] = Cron::getCronUrl();
        return $this->_NavigationController_render_parent();
    }

    protected function _NavigationController_render_parent()
    {
        return parent::render();
    }
}

