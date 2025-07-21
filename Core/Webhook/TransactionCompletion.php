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
namespace Pfc\PostFinanceCheckout\Core\Webhook;

use PostFinanceCheckout\Sdk\Model\TransactionState;
use PostFinanceCheckout\Sdk\Model\TransactionCompletionState;
use PostFinanceCheckout\Sdk\Service\TransactionCompletionService;
use Pfc\PostFinanceCheckout\Application\Model\CompletionJob;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use Pfc\PostFinanceCheckout\Extend\Application\Model\Order;

/**
 * Webhook processor to handle transaction completion state transitions.
 */
class TransactionCompletion extends AbstractOrderRelated
{

    /**
     * @param Request $request
     * @return \PostFinanceCheckout\Sdk\Model\TransactionCompletion
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function loadEntity(Request $request)
    {
        $service = new TransactionCompletionService(PostFinanceCheckoutModule::instance()->getApiClient());
        return $service->read($request->getSpaceId(), $request->getEntityId());
    }

    /**
     * @param object $completion
     * @return string
     * @throws \Exception
     */
    protected function getOrderId($completion)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion */
        $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
        /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
        if ($transaction->loadByTransactionAndSpace($completion->getLinkedTransaction(), $completion->getLinkedSpaceId())) {
            return $transaction->getOrderId();
        }
        throw new \Exception("Unable to load transaction {$completion->getLinkedTransaction()} in space {$completion->getLinkedSpaceId()} from database.");
    }

    protected function getTransactionId($entity)
    {
        /* @var $entity \PostFinanceCheckout\Sdk\Model\TransactionCompletion */
        return $entity->getLinkedTransaction();
    }

    /**
     * @param Order $order
     * @param object $completion
     * @throws \Exception
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function processOrderRelatedInner(\OxidEsales\Eshop\Application\Model\Order $order, $completion)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion */
        switch ($completion->getState()) {
            case TransactionCompletionState::FAILED:
                $this->failed($completion, $order);
                return true;
            case TransactionCompletionState::SUCCESSFUL:
                $this->success($completion, $order);
                return true;
            default:
                // Ignore PENDING & CREATE
                // Nothing to do.
                return false;
        }
    }

    /**
     * @param \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion
     * @param Order $order
     * @throws \Exception
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function success(\PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion, \OxidEsales\Eshop\Application\Model\Order $order)
    {
    	$job = oxNew(\Pfc\PostFinanceCheckout\Application\Model\CompletionJob::class);
        /* @var $job CompletionJob */
        if ($job->loadByOrder($order->getId()) || $job->loadByJob($completion->getId(), $completion->getLinkedSpaceId())) {
            $job->apply($completion);
        }
        $order->getPostFinanceCheckoutTransaction()->pull();
        $order->setPostFinanceCheckoutState($order->getPostFinanceCheckoutTransaction()->getState());

        // Twint and immediate payments. Marks order as authorized, sends confirmation email
        $status = $order->getFieldData('oxtransstatus');
        if ($status !== 'POSTFINANCECHECKOUT_' . TransactionState::AUTHORIZED) {
            $order->PostFinanceCheckoutAuthorize();
        }
    }

    /**
     * Fails the given order.
     *
     * @param \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion
     * @param Order $order
     * @throws \Exception
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function failed(\PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion, \OxidEsales\Eshop\Application\Model\Order $order)
    {
        /** @noinspection PhpParamsInspection */
        $message = PostFinanceCheckoutModule::instance()->PostFinanceCheckoutTranslate($completion->getFailureReason()->getName());
        /** @noinspection PhpParamsInspection */
        $message .= PostFinanceCheckoutModule::instance()->PostFinanceCheckoutTranslate($completion->getFailureReason()->getDescription());
        $order->getPostFinanceCheckoutTransaction()->pull();
        $order->PostFinanceCheckoutFail($message, $order->getPostFinanceCheckoutTransaction()->getState(), true, true);

        $job = oxNew(\Pfc\PostFinanceCheckout\Application\Model\CompletionJob::class);
        /* @var $job \Pfc\PostFinanceCheckout\Application\Model\CompletionJob */
        if ($job->loadByJob($completion->getId(), $completion->getLinkedSpaceId()) || $job->loadByOrder($order->getId())) {
            $job->apply($completion);
        }
    }
}
