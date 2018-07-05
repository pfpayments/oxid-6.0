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


namespace Pfc\PostFinanceCheckout\Extend\Application\Controller;

use Monolog\Logger;
use PostFinanceCheckout\Sdk\Model\TransactionState;
use Pfc\PostFinanceCheckout\Application\Model\Transaction;
use Pfc\PostFinanceCheckout\Core\Service\TransactionService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class BasketItem.
 * Extends \OxidEsales\Eshop\Application\Controller\OrderController.
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\OrderController
 */
class OrderController extends OrderController_parent
{
    public function init()
    {
        $this->_OrderController_init_parent();
        if ($this->getIsOrderStep()) {
            try {
                $transaction = Transaction::loadPendingFromSession($this->getSession());
                $transaction->updateFromSession();
            } catch (\Exception $e) {
                PostFinanceCheckoutModule::log(Logger::ERROR, "Could not update transaction: {$e->getMessage()}.");
            }
        }
    }

    protected function _OrderController_init_parent()
    {
        parent::init();
    }

    public function pfcConfirm()
    {
    	// TODO stuff here
    	\OxidEsales\Eshop\Core\DatabaseProvider::getDb()->startTransaction();
    	$order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $response = array(
            'status' => false,
            'message' => 'unkown'
        );

        if ($this->isPostFinanceCheckoutTransaction()) {
            try {
                $transaction = Transaction::loadPendingFromSession($this->getSession());
                /* @var $order \Pfc\PostFinanceCheckout\Extend\Application\Model\Order */
                /** @noinspection PhpParamsInspection */
                $state = $order->finalizeOrder($this->getBasket(), $this->getUser());
                if ($state === 'POSTFINANCECHECKOUT_' . TransactionState::PENDING) {
                    $transaction->setTempBasket($this->getBasket());
                    $transaction->setOrderId($order->getId());
                    // TODO stuff here
                    \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->commitTransaction();
                    \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->startTransaction();
                    $transaction->updateFromSession(true);
                    \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->commitTransaction();
                    $response['status'] = true;
                } else if ($state == \OxidEsales\Eshop\Application\Model\Order::ORDER_STATE_ORDEREXISTS) {
                    // ensure new order can be created
                    $this->getSession()->deleteVariable('sess_challenge');
                    throw new \Exception(PostFinanceCheckoutModule::instance()->translate("Order already exists. Please check if you have already received a confirmation, then try again."));
                } else {
                    throw new \Exception(PostFinanceCheckoutModule::instance()->translate("Unable to confirm order in state !state.", true, array('!state' => $state)));
                }
            } catch (\Exception $e) {
                if (isset($transaction)) {
                    $state = $transaction->getState();
                } else if (!isset($state)) {
                    $state = 'confirmation_error_unkown';
                }
                $order->PostFinanceCheckoutFail($e->getMessage(), $state, true);
                PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to confirm transaction: {$e->getMessage()}.");
                $response['message'] = $e->getMessage();
            }
        } else {
            $response['message'] = PostFinanceCheckoutModule::instance()->translate("Not a WhiteLabenName order.");
        }

        PostFinanceCheckoutModule::renderJson($response);
    }

    public function pfcError()
    {
        try {
            $orderId = PostFinanceCheckoutModule::instance()->getRequestParameter('oxid');
            if ($orderId) {
            	$order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
                /* @var $order Order */
            	$transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
                /* @var $transaction Transaction */
                if ($order->load($orderId) && $transaction->loadByOrder($orderId)) {
                    $transaction->pull();
                    $order->PostFinanceCheckoutFail($transaction->getSdkTransaction()->getUserFailureMessage(), $transaction->getState());
                    PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($transaction->getSdkTransaction()->getUserFailureMessage());
                } else {
                	PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay(PostFinanceCheckoutModule::instance()->translate("An unknown error occurred, and the order could not be loaded."));
                }
            } else {
                $transaction = Transaction::loadFailedFromSession($this->getSession());
                if ($transaction) {
                    if ($transaction->getOrderId()) {
                    	$order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
                        /* @var $order \OxidEsales\Eshop\Application\Model\Order */
                        if ($order->load($transaction->getOrderId())) {
                            $order->PostFinanceCheckoutFail($transaction->getSdkTransaction()->getUserFailureMessage(), $transaction->getState());
                        }
                    }
                    PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($transaction->getSdkTransaction()->getUserFailureMessage());
                } else {
                	PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay(PostFinanceCheckoutModule::instance()->translate("An unknown error occurred, and the order could not be loaded."));
                }
            }
        } catch (\Exception $e) {
        	PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($e);
        }
    }
    
    public function isPostFinanceCheckoutTransaction()
    {
        return PostFinanceCheckoutModule::isPostFinanceCheckoutPayment($this->getBasket()->getPaymentId());
    }

    public function getPostFinanceCheckoutPaymentId()
    {
        return PostFinanceCheckoutModule::extractPostFinanceCheckoutId($this->getBasket()->getPaymentId());
    }

    public function getPostFinanceCheckoutJavascriptUrl()
    {
        try {
            $transaction = Transaction::loadPendingFromSession($this->getSession());
            return TransactionService::instance()->getJavascriptUrl($transaction->getTransactionId(), $transaction->getSpaceId());
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::log(Logger::ERROR, $e->getMessage(), array($this, $e));
        }
        return '';
    }
}