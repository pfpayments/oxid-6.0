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
namespace Pfc\PostFinanceCheckout\Core\Webhook;

use PostFinanceCheckout\Sdk\Service\TransactionVoidService;
use Pfc\PostFinanceCheckout\Application\Model\VoidJob;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use Pfc\PostFinanceCheckout\Extend\Application\Model\Order;
use PostFinanceCheckout\Sdk\Model\TransactionVoidState;
use Monolog\Logger;

/**
 * Webhook processor to handle transaction void state transitions.
 */
class TransactionVoid extends AbstractOrderRelated
{

    /**
     * @param Request $request
     * @return \PostFinanceCheckout\Sdk\Model\TransactionVoid
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function loadEntity(Request $request)
    {
        $voidService = new TransactionVoidService(PostFinanceCheckoutModule::instance()->getApiClient());
        return $voidService->read($request->getSpaceId(), $request->getEntityId());
    }

    protected function getOrderId($void)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\TransactionVoid $void */
        $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
        /* @var $dbTransaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
        $transaction->loadByTransactionAndSpace($void->getTransaction()->getId(), $void->getLinkedSpaceId());
        return $transaction->getOrderId();
    }

    protected function getTransactionId($entity)
    {
        /* @var $entity \PostFinanceCheckout\Sdk\Model\TransactionVoid */
        return $entity->getTransaction()->getId();
    }

    protected function processOrderRelatedInner(\OxidEsales\Eshop\Application\Model\Order $order, $void)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\TransactionVoid $void */
        if ($this->apply($void, $order)) {
            switch ($void->getState()) {
                case TransactionVoidState::SUCCESSFUL:
                    $order->cancelOrder();
                    return true;
                default:
                    // Nothing to do.
                    break;
            }
        }
        return false;
    }

    protected function apply(\PostFinanceCheckout\Sdk\Model\TransactionVoid $void, Order $order)
    {
    	$job = oxNew(\Pfc\PostFinanceCheckout\Application\Model\VoidJob::class);
        /* @var $job \Pfc\PostFinanceCheckout\Application\Model\VoidJob */
        if ($job->loadByJob($void->getId(), $void->getLinkedSpaceId()) || $job->loadByOrder($order->getId())) {
            if ($job->getState() !== $void->getState()) {
                $job->apply($void);
                return true;
            }
        } else {
            PostFinanceCheckoutModule::log(Logger::WARNING, "Unknown void received, was not processed: $void.");
        }
        return false;
    }
}