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


namespace Pfc\PostFinanceCheckout\Core\Service;

use Monolog\Logger;
use PostFinanceCheckout\Sdk\Model\CriteriaOperator;
use PostFinanceCheckout\Sdk\Model\EntityQuery;
use PostFinanceCheckout\Sdk\Model\EntityQueryFilter;
use PostFinanceCheckout\Sdk\Model\EntityQueryFilterType;
use PostFinanceCheckout\Sdk\Model\LineItemReductionCreate;
use PostFinanceCheckout\Sdk\Model\LineItemType;
use PostFinanceCheckout\Sdk\Model\Refund;
use PostFinanceCheckout\Sdk\Model\RefundCreate;
use PostFinanceCheckout\Sdk\Model\RefundState;
use PostFinanceCheckout\Sdk\Model\RefundType;
use PostFinanceCheckout\Sdk\Model\TransactionState;
use PostFinanceCheckout\Sdk\Service\RefundService as sdkRefundService;
use Pfc\PostFinanceCheckout\Application\Model\AbstractJob;
use Pfc\PostFinanceCheckout\Application\Model\RefundJob;
use Pfc\PostFinanceCheckout\Application\Model\Transaction;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class RefundService
 * Handles interactions regarding refunds.
 */
class RefundService extends JobService
{

    private $service;

    protected function getService()
    {
        if ($this->service === null) {
            $this->service = new sdkRefundService(PostFinanceCheckoutModule::instance()->getApiClient());
        }
        return $this->service;
    }


    /**
     * Return list of line items, with all successful reductions removed.
     *
     * @param Transaction $transaction
     * @return array
     */
    public function getReducedItems(Transaction $transaction)
    {
        $refunds = $this->getService()->search($transaction->getSpaceId(), self::createSuccessfulRefundQuery($transaction->getTransactionId()));

        $items = array();
        foreach ($transaction->getSdkTransaction()->getLineItems() as $item) {
            $items[$item->getUniqueId()] = array(
                'id' => $item->getUniqueId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'quantity' => $item->getQuantity(),
                'unit_price' => $item->getUnitPriceIncludingTax(),
                'total' => $item->getAmountIncludingTax()
            );
        }

        $remove = array();
        foreach ($refunds as $refund) {
            foreach ($refund->getReductions() as $reduction) {
                $items[$reduction->getLineItemUniqueId()]['quantity'] -= $reduction->getQuantityReduction();
                $items[$reduction->getLineItemUniqueId()]['unit_price'] -= $reduction->getUnitPriceReduction();
                if ($items[$reduction->getLineItemUniqueId()]['quantity'] == 0 || $items[$reduction->getLineItemUniqueId()]['unit_price'] == 0) {
                    $remove[] = $reduction->getLineItemUniqueId();
                }
            }
        }
        foreach ($remove as $toRemove) {
            unset($items[$toRemove]);
        }

        return $items;
    }

    protected function getJobType()
    {
        return RefundJob::class;
    }

    public function getSupportedTransactionStates()
    {
        return array(
            TransactionState::COMPLETED,
            TransactionState::FULFILL
        );
    }

    protected function processSend(AbstractJob $job)
    {
        if (!$job instanceof RefundJob) {
            throw new \Exception("Invalid job type supplied.");
        }
        if (empty($job->getFormReductions())) {
            throw new \Exception("No form reductions supplied");
        }
        $refund = $this->getService()->refund($job->getSpaceId(), $this->createRefund($job));
        if ($refund->getState() === RefundState::SUCCESSFUL) {
            $this->restock($refund);
        }
        return $refund;
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

    private function createRefund(RefundJob $job)
    {
        $refund = new RefundCreate();
        $refund->setType(RefundType::MERCHANT_INITIATED_ONLINE);
        $refund->setTransaction($job->getTransactionId());
        $refund->setExternalId($job->getId());
        $reductions = array();
        foreach ($job->getFormReductions() as $formReduction) {
            $reduction = new LineItemReductionCreate();
            $reduction->setLineItemUniqueId($formReduction['id']);
            $reduction->setQuantityReduction($formReduction['quantity']);
            $reduction->setUnitPriceReduction($formReduction['price']);
            $reductions[] = $reduction;
        }
        $refund->setReductions($reductions);
        return $refund;
    }

    /**
     * Creates an EntityQuery for the given transaction id which includes all successful refunds associated with the transaction.
     *
     * @noinspection PhpParamsInspection
     * suppress enum warnings
     * @param $transactionId
     * @return EntityQuery
     */
    private static function createSuccessfulRefundQuery($transactionId)
    {
        $query = new EntityQuery();

        $transactionFilter = new EntityQueryFilter();
        $transactionFilter->setType(EntityQueryFilterType::LEAF);
        $transactionFilter->setOperator(CriteriaOperator::EQUALS);
        $transactionFilter->setFieldName('transaction.id');
        $transactionFilter->setValue($transactionId);

        $stateFilter = new EntityQueryFilter();
        $stateFilter->setType(EntityQueryFilterType::LEAF);
        $stateFilter->setOperator(CriteriaOperator::EQUALS);
        $stateFilter->setFieldName('state');
        $stateFilter->setValue(RefundState::SUCCESSFUL); // only exclude successful, refunds are not possible if open pending / manual tasks.

        $filter = new EntityQueryFilter();
        $filter->setType(EntityQueryFilterType::_AND);
        $filter->setChildren(array($stateFilter, $transactionFilter));

        $query->setFilter($filter);

        return $query;
    }

    public function resendAll()
    {
        $errors = array();
        $refund = oxNew(RefundJob::class);
        /* @var $refund \Pfc\PostFinanceCheckout\Application\Model\RefundJob */
        $notSent = $refund->loadNotSentIds();
        foreach ($notSent as $job) {
            if ($refund->loadByJob($job['PFCJOBID'], $job['PFCSPACEID'])) {
                $this->send($refund);
                if($refund->getState() === self::getFailedState()) {
                    $errors[] = $refund->getFailureReason();
                }
            } else {
                PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to load pending job {$job['PFCJOBID']} / {$job['PFCSPACEID']}.");
            }
        }
        return $errors;
    }

    public static function getPendingStates() {
        return array(
            RefundState::PENDING,
            RefundState::MANUAL_CHECK
        );
    }
}