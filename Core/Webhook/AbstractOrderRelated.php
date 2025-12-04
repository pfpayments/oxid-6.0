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
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use Pfc\PostFinanceCheckout\Core\Exception\OptimisticLockingException;
use Pfc\PostFinanceCheckout\Core\Exception\OrderNotFoundException;

/**
 * Abstract webhook processor for order related entities.
 */
abstract class AbstractOrderRelated extends AbstractWebhook
{
	const NO_ORDER = 1;
	const OPTIMISTIC_RETRIES = 6;
	const SECONDS_TO_WAIT = 1;

    /**
     * Processes the received order related webhook request.
     * @param Request $request
     * @throws \Exception
     */
    public function process(Request $request)
    {
        if ($request->getSpaceId() != PostFinanceCheckoutModule::settings()->getSpaceId()) {
            throw new \Exception("Received webhook with space id {$request->getSpaceId()} in store which is configured for space id " . PostFinanceCheckoutModule::settings()->getSpaceId());
        }

		$entity = $this->loadEntity($request);
		$orderId = $this->getOrderId($entity);
        try {
            $order = $this->loadOrder($orderId);
        }
        catch (OrderNotFoundException $e) {
            if ($entity->getModelName() == 'Transaction' && ($entity->getState() == 'FAILED' || $entity->getState() == 'PENDING')) {
                // For FAILED state, most likely the order was never created. So we just log and ignore.
                PostFinanceCheckoutModule::log(Logger::INFO, "Ignoring failed webhook for non existing order with id $orderId.");
                return;
            }
            PostFinanceCheckoutModule::log(Logger::ERROR, "Could not load order with id $orderId for webhook processing: " . $e->getMessage());
            throw $e;
        }
		\OxidEsales\Eshop\Core\Registry::getLang()->setBaseLanguage($order->getOrderLanguage());

		if(!$order->getPostFinanceCheckoutTransaction() || !$order->getPostFinanceCheckoutTransaction()->getId()){
			throw new \Exception("Transaction could not be loaded on order.");
		}

		if($this->processOrderRelatedInner($order, $entity)) {
			if(!$order->getPostFinanceCheckoutTransaction()->save() || !$order->save()) {
				throw new \Exception('Unable to save order');
			}
		}
    }


    /**
     * @param $orderId
     * @return \OxidEsales\Eshop\Application\Model\Order
     * @throws \Exception
     */
    protected function loadOrder($orderId)
    {
        PostFinanceCheckoutModule::getUtilsObject()->resetInstanceCache(\OxidEsales\Eshop\Application\Model\Order::class);
        $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        /* @var $order \Pfc\PostFinanceCheckout\Extend\Application\Model\Order */
        if ($order->load($orderId) && $order->isPfcOrder()) {
            return $order;
        }
        throw new OrderNotFoundException("Could not load order by id $orderId.", self::NO_ORDER);
    }

    /**
     * Loads and returns the entity for the webhook request.
     *
     * @param Request $request
     * @return object
     */
    abstract protected function loadEntity(Request $request);

    /**
     * Returns the order id linked to the entity.
     *
     * @param object $entity
     * @return string
     */
    abstract protected function getOrderId($entity);

    /**
     * Returns the transaction id linked to the entity
     *
     *
     * @param object $entity
     * @return int
     */
    abstract protected function getTransactionId($entity);

    /**
     * Actually processes the order related webhook request.
     *
     * This must be implemented
     *
     * @param \OxidEsales\Eshop\Application\Model\Order $order
     * @param object $entity
     * @return bool If a change was applied to the database, e.g. if the order should be saved.
     */
    abstract protected function processOrderRelatedInner(\OxidEsales\Eshop\Application\Model\Order $order, $entity);
}
