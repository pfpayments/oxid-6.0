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

namespace Pfc\PostFinanceCheckout\Application\Controller\Admin;

use Monolog\Logger;
use PostFinanceCheckout\Sdk\Model\RefundState;
use PostFinanceCheckout\Sdk\Model\TransactionCompletionState;
use PostFinanceCheckout\Sdk\Model\TransactionVoidState;
use Pfc\PostFinanceCheckout\Core\Service\CompletionService;
use Pfc\PostFinanceCheckout\Core\Service\RefundService;
use Pfc\PostFinanceCheckout\Core\Service\VoidService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;


/**
 * Class Transaction.
 */
class Transaction extends \OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController
{

    /**
     * Controller template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'pfcPostFinanceCheckoutTransaction.tpl';

    /**
     * @return string
     */
    public function render()
    {
        parent::render();
        $this->_aViewData['pfc_postFinanceCheckout_enabled'] = false;
        $orderId = $this->getEditObjectId();
        try {
            if ($orderId != '-1' && isset($orderId)) {
                $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
                /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
                if ($transaction->loadByOrder($orderId)) {
                    $transaction->pull();
                    $this->_aViewData['labelGroupings'] = $transaction->getLabels();
                    $this->_aViewData['pfc_postFinanceCheckout_enabled'] = true;
                    return $this->_sThisTemplate;
                } else {
                    throw new \Exception(PostFinanceCheckoutModule::instance()->translate('Not a PostFinance Checkout order.'));
                }
            } else {
                throw new \Exception(PostFinanceCheckoutModule::instance()->translate('No order selected'));
            }
        } catch (\Exception $e) {
            $this->_aViewData['pfc_error'] = $e->getMessage();
            return 'pfcPostFinanceCheckoutError.tpl';
        }
    }

    /**
     * Creates and sends a completion job.
     */
    public function complete()
    {
        $oxid = $this->getEditObjectId();
        $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
        /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
        if ($transaction->loadByOrder($oxid)) {
            try {
                $transaction->updateLineItems();
                $job = CompletionService::instance()->create($transaction);
                CompletionService::instance()->send($job);
                if ($job->getState() === TransactionCompletionState::FAILED) {
                	PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($job->getFailureReason());
                } else {
                    $this->_aViewData['message'] = PostFinanceCheckoutModule::instance()->translate("Successfully created and sent completion job !id.", true, array('!id' => $job->getJobId()));
                }
            } catch (\Exception $e) {
                PostFinanceCheckoutModule::log(Logger::ERROR, "Exception occurred while completing transaction: {$e->getMessage()} - {$e->getTraceAsString()}");
                PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($e->getMessage()); // To set error
            }
        } else {
            $error = "Unable to load transaction by order $oxid for completion.";
            PostFinanceCheckoutModule::log(Logger::ERROR, $error);
            PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($error); // To set error
        }
    }

    /**
     * Creates and sends a void job.
     *
     */
    public function void()
    {
        $oxid = $this->getEditObjectId();
        $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
        /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
        if ($transaction->loadByOrder($oxid)) {
            try {
                $job = VoidService::instance()->create($transaction);
                VoidService::instance()->send($job);
                if ($job->getState() === TransactionVoidState::FAILED) {
                	PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($job->getFailureReason());
                } else {
                    $this->_aViewData['message'] = PostFinanceCheckoutModule::instance()->translate("Successfully created and sent void job !id.", true, array('!id' => $job->getJobId()));
                }
            } catch (\Exception $e) {
                PostFinanceCheckoutModule::log(Logger::ERROR, "Exception occurred while completing transaction: {$e->getMessage()} - {$e->getTraceAsString()}");
                PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($e->getMessage()); // To set error
            }
        } else {
            $error = "Unable to load transaction by order $oxid for completion.";
            PostFinanceCheckoutModule::log(Logger::ERROR, $error);
            PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($error); // To set error
        }
    }

    /**
     * Checks if the transaction associated with the given order id is in the correct state for completion, and checks if any completion jobs are currently running.
     *
     * @param $orderId
     * @return bool
     */
    public function canComplete($orderId)
    {
        try {
        	$job = oxNew(\Pfc\PostFinanceCheckout\Application\Model\CompletionJob::class);
            /* @var $job \Pfc\PostFinanceCheckout\Application\Model\CompletionJob */
            $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
            /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
            $transaction->loadByOrder($orderId);
            $transaction->pull();
            return !$job->loadByOrder($orderId, array(TransactionCompletionState::PENDING)) &&
                in_array($transaction->getState(), CompletionService::instance()->getSupportedTransactionStates());
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to check completion possibility: {$e->getMessage()} - {$e->getTraceAsString()}");
        }
        return false;
    }

    /**
     * Checks if the transaction associated with the given order id is in the correct state for refund, and checks if any refund jobs are currently running.
     *
     * @param $orderId
     * @return bool
     */
    public function canRefund($orderId)
    {
        try {
            $job = oxNew(\Pfc\PostFinanceCheckout\Application\Model\RefundJob::class);
            /* @var $job \Pfc\PostFinanceCheckout\Application\Model\RefundJob */
            $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
            /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
            $transaction->loadByOrder($orderId);
            $transaction->pull();
            return !$job->loadByOrder($orderId, array(RefundState::MANUAL_CHECK, RefundState::PENDING)) &&
                in_array($transaction->getState(), RefundService::instance()->getSupportedTransactionStates()) && !empty(RefundService::instance()->getReducedItems($transaction));
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to check completion possibility: {$e->getMessage()} - {$e->getTraceAsString()}");
        }
        return false;
    }

    /**
     * Checks if the transaction associated with the given order id is in the correct state for void, and checks if any void jobs are currently running.
     * @param $orderId
     * @return bool
     */
    public function canVoid($orderId)
    {
        try {
        	$job = oxNew(\Pfc\PostFinanceCheckout\Application\Model\VoidJob::class);
            /* @var $job \Pfc\PostFinanceCheckout\Application\Model\VoidJob */
            $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
            /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
            $transaction->loadByOrder($orderId);
            $transaction->pull();
            return !$job->loadByOrder($orderId, array(TransactionVoidState::PENDING)) &&
                in_array($transaction->getState(), VoidService::instance()->getSupportedTransactionStates());
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to check void possibility: {$e->getMessage()} - {$e->getTraceAsString()}");
        }
        return false;
    }
}