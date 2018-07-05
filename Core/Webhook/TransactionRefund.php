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

use Monolog\Logger;
use PostFinanceCheckout\Sdk\Model\LineItemType;
use PostFinanceCheckout\Sdk\Model\Refund;
use PostFinanceCheckout\Sdk\Model\RefundState;
use PostFinanceCheckout\Sdk\Service\RefundService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use Pfc\PostFinanceCheckout\Extend\Application\Model\Order;

/**
 * Webhook processor to handle refund state transitions.
 */
class TransactionRefund extends AbstractOrderRelated
{

    /**
     * @param Request $request
     * @return \PostFinanceCheckout\Sdk\Model\Refund
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function loadEntity(Request $request)
    {
        $service = new RefundService(PostFinanceCheckoutModule::instance()->getApiClient());
        return $service->read($request->getSpaceId(), $request->getEntityId());
    }

    protected function getOrderId($refund)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\Refund $refund */
        $transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
        /* @var $dbTransaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
        $transaction->loadByTransactionAndSpace($refund->getTransaction()->getId(), $refund->getLinkedSpaceId());
        return $transaction->getOrderId();
    }

    protected function getTransactionId($entity)
    {
        /* @var $entity \PostFinanceCheckout\Sdk\Model\Refund */
        return $entity->getTransaction()->getId();
    }

    protected function processOrderRelatedInner(\OxidEsales\Eshop\Application\Model\Order $order, $refund)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\Refund $refund */
        $job = $this->apply($refund, $order);
        if($refund->getState() === RefundState::SUCCESSFUL && $job) {
            $this->restock($refund);
        }
        return $job != null;
    }

    private function apply(Refund $refund, \OxidEsales\Eshop\Application\Model\Order $order)
    {
    	$job = oxNew(\Pfc\PostFinanceCheckout\Application\Model\RefundJob::class);
        /* @var $job \Pfc\PostFinanceCheckout\Application\Model\RefundJob */
        if ($job->loadByJob($refund->getId(), $refund->getLinkedSpaceId()) || $job->loadByOrder($order->getId())) {
            if ($job->getState() !== $refund->getState()) {
                $job->apply($refund);
                return $job;
            }
        } else {
            PostFinanceCheckoutModule::log(Logger::WARNING, "Unknown refund received, was not processed: $refund.");
        }
        return null;
    }

    protected function restock(Refund $refund)
    {
        foreach ($refund->getReductions() as $reduction) {
            foreach ($refund->getReducedLineItems() as $reduced) {
                if ($reduced->getUniqueId() === $reduction->getLineItemUniqueId() && $reduced->getType() !== LineItemType::PRODUCT) {
                    break 1;
                }
            }
            if ($reduction->getQuantityReduction()) {
            	$oxArticle = oxNew(\OxidEsales\Eshop\Application\Model\Article::class);
                /* @var $oxArticle \OxidEsales\Eshop\Application\Model\Article */
                if ($oxArticle->load($reduction->getLineItemUniqueId())) {
                    if (!$oxArticle->reduceStock(-$reduction->getQuantityReduction())) {
                        PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to increase stock for article {$reduction->getLineItemUniqueId()} by {$reduction->getQuantityReduction()}.");
                    }
                } else {
                    PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to load article {$reduction->getLineItemUniqueId()} to reduce stock by {$reduction->getQuantityReduction()}.");
                }
            }
        }
    }
}