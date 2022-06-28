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

use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Webhook processor to handle delivery indication state transitions.
 */
class DeliveryIndication extends AbstractOrderRelated {

	/**
	 *
	 * @see AbstractOrderRelated::load_entity()
	 * @return \PostFinanceCheckout\Sdk\Model\DeliveryIndication
	 */
	protected function loadEntity(Request $request){
		$service = new \PostFinanceCheckout\Sdk\Service\DeliveryIndicationService(PostFinanceCheckoutModule::instance()->getApiClient());
		return $service->read($request->getSpaceId(), $request->getEntityId());
	}

	protected function getOrderId($deliveryIndication){
		/* @var \PostFinanceCheckout\Sdk\Model\DeliveryIndication $delivery_indication */
		return $deliveryIndication->getTransaction()->getMerchantReference();
	}

	protected function getTransactionId($deliveryIndication){
		/* @var $delivery_indication \PostFinanceCheckout\Sdk\Model\DeliveryIndication */
		return $deliveryIndication->getLinkedTransaction();
	}

	protected function processOrderRelatedInner(\OxidEsales\Eshop\Application\Model\Order $order, $deliveryIndication){
		/* @var \PostFinanceCheckout\Sdk\Model\DeliveryIndication $deliveryIndication */
		switch ($deliveryIndication->getState()) {
			case \PostFinanceCheckout\Sdk\Model\DeliveryIndicationState::MANUAL_CHECK_REQUIRED:
				$this->review($order);
				break;
			default:
				// Nothing to do.
				break;
		}
	}

	protected function review(\OxidEsales\Eshop\Application\Model\Order $order){
		$order->getPostFinanceCheckoutTransaction()->pull();
		$order->setPostFinanceCheckoutState($order->getPostFinanceCheckoutTransaction()->getState());
	}
}