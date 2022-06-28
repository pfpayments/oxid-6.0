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
use PostFinanceCheckout\Sdk\Model\TransactionInvoiceState;
use PostFinanceCheckout\Sdk\Service\TransactionInvoiceService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use Pfc\PostFinanceCheckout\Extend\Application\Model\Order;

/**
 * Webhook processor to handle manual task state transitions.
 */
class TransactionInvoice extends AbstractOrderRelated
{
    /**
     * Loads and returns the entity for the webhook request.
     * @param Request $request
     * @return \PostFinanceCheckout\Sdk\Model\TransactionInvoice
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function loadEntity(Request $request)
    {
        $service = new TransactionInvoiceService(PostFinanceCheckoutModule::instance()->getApiClient());
        return $service->read($request->getSpaceId(), $request->getEntityId());
    }

    /**
     * Loads and returns the order id associated with the given entity.
     *
     * @param object $entity
     * @return string
     * @throws \Exception
     */
    protected function getOrderId($entity)
    {
        /* @var $entity \PostFinanceCheckout\Sdk\Model\TransactionInvoice */
        $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
        /* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
        if ($transaction->loadByTransactionAndSpace($this->getTransactionId($entity), $entity->getLinkedSpaceId())) {
            return $transaction->getOrderId();
        }
        throw new \Exception("Could not load transaction {$entity->getLinkedTransaction()} in space {$entity->getLinkedSpaceId()}.");
    }

    /**
     * Returns the transaction id linked to the entity
     *
     *
     * @param object $entity
     * @return int
     */
    protected function getTransactionId($entity)
    {
        /* @var $entity \PostFinanceCheckout\Sdk\Model\TransactionInvoice */
        return $entity->getLinkedTransaction();
    }

    /**
     * Actually processes the order related webhook request.
     *
     * This must be implemented
     *
     * @param Order $order
     * @param object $entity
     */
    protected function processOrderRelatedInner(\OxidEsales\Eshop\Application\Model\Order $order, $entity)
    {
        /* @var $entity \PostFinanceCheckout\Sdk\Model\TransactionInvoice */
        switch ($entity->getState()) {
            case TransactionInvoiceState::NOT_APPLICABLE:
            case TransactionInvoiceState::PAID:
                $order->setPostFinanceCheckoutPaid();
                return true;
            default:
                PostFinanceCheckoutModule::log(Logger::WARNING, "Received unprocessable TransactionInvoiceState {$entity->getState()}.");
                return false;
        }
    }
}