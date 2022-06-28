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

use Monolog\Logger;
use PostFinanceCheckout\Sdk\Model\TransactionState;
use PostFinanceCheckout\Sdk\Service\TransactionService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use Pfc\PostFinanceCheckout\Extend\Application\Model\Order;

/**
 * Webhook processor to handle transaction state transitions.
 */
class Transaction extends AbstractOrderRelated
{
    /**
     * Retrieves the entity from PostFinanceCheckout via sdk.
     *
     * @param Request $request
     * @return \PostFinanceCheckout\Sdk\Model\Transaction
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function loadEntity(Request $request)
    {
        $service = new TransactionService(PostFinanceCheckoutModule::instance()->getApiClient());
        return $service->read($request->getSpaceId(), $request->getEntityId());
    }

    protected function getOrderId($transaction)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\Transaction $transaction */

        $dbTransaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
        /* @var $dbTransaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
        $dbTransaction->loadByTransactionAndSpace($transaction->getId(), $transaction->getLinkedSpaceId());
        return $dbTransaction->getOrderId();
    }

    protected function getTransactionId($transaction)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\Transaction $transaction */
        return $transaction->getId();
    }

    /**
     * @param Order $order
     * @param object $entity
     * @throws \Exception
     */
    protected function processOrderRelatedInner(\OxidEsales\Eshop\Application\Model\Order $order, $entity)
    {
        /* @var $entity \PostFinanceCheckout\Sdk\Model\Transaction */
        /* @var $order \Pfc\PostFinanceCheckout\Extend\Application\Model\Order */
        if ($entity && $entity->getState() !== $order->getPostFinanceCheckoutTransaction()->getState()) {
            $cancel = false;
            switch ($entity->getState()) {
                case TransactionState::AUTHORIZED:
                case TransactionState::FULFILL:
                case TransactionState::COMPLETED:
                    $oldState = $order->getFieldData('oxtransstatus');
                    $order->setPostFinanceCheckoutState($entity->getState());
                    if (!PostFinanceCheckoutModule::isAuthorizedState($oldState)) {
                        $order->PostFinanceCheckoutAuthorize();
                    }
                    return true;
                case TransactionState::CONFIRMED:
                case TransactionState::PROCESSING:
                	$order->setPostFinanceCheckoutState($entity->getState());
                	return true;
                case TransactionState::VOIDED:
                    $cancel = true;
                case TransactionState::DECLINE:
                case TransactionState::FAILED:
                	$order->setPostFinanceCheckoutState($entity->getState());
                	$order->PostFinanceCheckoutFail($entity->getUserFailureMessage(), $entity->getState(), $cancel, true);
                	return true;
                default:
                	return false;
            }
        }
        return false;
    }
}