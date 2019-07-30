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


namespace Pfc\PostFinanceCheckout\Extend\Application\Controller;

use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class used to include tracking device id on basket.
 *
 * Class BasketController.
 * Extends \OxidEsales\Eshop\Application\Controller\BasketController.
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\BasketController
 */
class BasketController extends BasketController_parent
{
    public function render()
    {
        parent::render();

        $this->setPostFinanceCheckoutDeviceCookie();
        $this->_aViewData['PostFinanceCheckoutDeviceScript'] = $this->getPostFinanceCheckoutDeviceUrl();

        return 'pfcPostFinanceCheckoutCheckoutBasket.tpl';
    }

    private function getPostFinanceCheckoutDeviceUrl()
    {
        $script = PostFinanceCheckoutModule::settings()->getBaseUrl();
        $script .= '/s/[spaceId]/payment/device.js?sessionIdentifier=[UniqueSessionIdentifier]';

        $script = str_replace(array(
            '[spaceId]',
            '[UniqueSessionIdentifier]'
        ), array(
            PostFinanceCheckoutModule::settings()->getSpaceId(),
            $_COOKIE['PostFinanceCheckout_device_id']
        ), $script);

        return $script;
    }

    private function setPostFinanceCheckoutDeviceCookie()
    {
        if (isset($_COOKIE['PostFinanceCheckout_device_id'])) {
            $value = $_COOKIE['PostFinanceCheckout_device_id'];
        } else {
        	$_COOKIE['PostFinanceCheckout_device_id'] = $value = PostFinanceCheckoutModule::getUtilsObject()->generateUId();
        }
        setcookie('PostFinanceCheckout_device_id', $value, time() + 365 * 24 * 60 * 60, '/');
    }
}