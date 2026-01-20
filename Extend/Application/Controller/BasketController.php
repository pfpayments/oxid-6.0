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

namespace Pfc\PostFinanceCheckout\Extend\Application\Controller;

use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class used to include tracking device id on basket.
 *
 * Extends \OxidEsales\Eshop\Application\Controller\BasketController.
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\BasketController
 */
class BasketController extends BasketController_parent
{
    /**
     * Keep the original template return value to preserve OXID inheritance chain.
     *
     * @return string
     */
    public function render()
    {
        $template = parent::render();
        $deviceId = $this->ensurePostFinanceCheckoutDeviceCookie();
        $this->_aViewData['PostFinanceCheckoutDeviceScript'] = $this->getPostFinanceCheckoutDeviceUrl($deviceId);

        return $template;
    }

    /**
     * Build device script URL.
     *
     * @param string $deviceId
     * @return string
     */
    private function getPostFinanceCheckoutDeviceUrl($deviceId)
    {
        $baseUrl = (string) PostFinanceCheckoutModule::settings()->getBaseUrl();
        $spaceId = (string) PostFinanceCheckoutModule::settings()->getSpaceId();
        $script = rtrim($baseUrl, '/')
            . '/s/[spaceId]/payment/device.js?sessionIdentifier=[UniqueSessionIdentifier]';

        $script = str_replace(
            array('[spaceId]', '[UniqueSessionIdentifier]'),
            array($spaceId, rawurlencode((string) $deviceId)),
            $script
        );

        return $script;
    }

    /**
     * Ensure device cookie exists and return its value.
     *
     * @return string
     */
    private function ensurePostFinanceCheckoutDeviceCookie()
    {
        $cookieName = 'PostFinanceCheckout_device_id';

        $value = isset($_COOKIE[$cookieName]) && is_string($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] !== ''
            ? $_COOKIE[$cookieName]
            : (string) PostFinanceCheckoutModule::getUtilsObject()->generateUId();

        $expire = time() + 365 * 24 * 60 * 60;

        setcookie($cookieName, $value, $expire, '/');
        $_COOKIE[$cookieName] = $value;

        return $value;
    }
}
