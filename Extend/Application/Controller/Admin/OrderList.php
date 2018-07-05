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


namespace Pfc\PostFinanceCheckout\Extend\Application\Controller\Admin;

use Pfc\PostFinanceCheckout\Extend\Application\Model\Order;

/**
 * Class NavigationController.
 * Extends \OxidEsales\Eshop\Application\Controller\Admin\OrderList.
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\Admin\OrderList
 */
class OrderList extends OrderList_parent
{
    protected $_sThisTemplate = 'pfcPostFinanceCheckoutOrderList.tpl';

    public function render()
    {
        $orderId = $this->getEditObjectId();
        if ($orderId != '-1' && isset($orderId)) {
        	$order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            $order->load($orderId);
            /* @var $order Order */

            if ($order->isPfcOrder()) {
                $this->_aViewData['pfcEnabled'] = true;
            }
        }
        $this->_OrderList_render_parent();

        return $this->_sThisTemplate;
    }

    protected function _OrderList_render_parent()
    {
        return parent::render();
    }
}