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


namespace Pfc\PostFinanceCheckout\Core\Service;

use Monolog\Logger;
use PostFinanceCheckout\Sdk\ApiException;
use PostFinanceCheckout\Sdk\Model\TransactionCompletionState;
use PostFinanceCheckout\Sdk\Service\TransactionCompletionService;
use PostFinanceCheckout\Sdk\Service\TransactionVoidService;
use Pfc\PostFinanceCheckout\Application\Model\AbstractJob;
use Pfc\PostFinanceCheckout\Application\Model\Transaction;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class PaymentService
 * Handles api interactions regarding payment methods.
 *
 * @codeCoverageIgnore
 */
abstract class JobService extends AbstractService
{
    /**
     * @return \PostFinanceCheckout\Sdk\Service\RefundService|TransactionVoidService|TransactionCompletionService
     */
    protected abstract function getService();

    protected abstract function processSend(AbstractJob $job);

    public function send(AbstractJob $job)
    {
        try {
            $response = $this->processSend($job);
            $job->apply($response);
            return true;
        } catch (\Exception $e) {
            $job->setState(static::getFailedState());
            $job->setFailureReason($e->getMessage());
        }
        try {
            $job->save();
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to save job post send: {$e->getMessage()} - {$e->getTraceAsString()}. " . print_r($job, true));
        }
        return false;
    }

    /**
     * Creates a job for the given type.
     * Checks the given transaction for correct state,
     * If a created, unsent job already exists, that is returned instead.
     *
     * @param Transaction $transaction
     * @param bool $save (If the job should be saved. e.g. with RefundJob further data will be applied
     * @throws ApiException
     * @throws \Exception
     * @return AbstractJob
     */
    public function create(Transaction $transaction, $save = true)
    {
        if (!in_array($transaction->getState(), $this->getSupportedTransactionStates())) {
            $states = implode(", ", $this->getSupportedTransactionStates());
            throw new \Exception("Job may only be created if transaction in one of states: $states.");
        }
        $job = oxNew($this->getJobType());
        /* @var $job AbstractJob */
        if($job->loadByOrder($transaction->getOrderId(), static::getPendingStates())) {
            throw new \Exception("Job may only be created if transaction no pending jobs exist.");
        }
        if (!$job->loadByOrder($transaction->getOrderId(), array(static::getCreationState()))) {
            $job->setState(static::getCreationState());
            $job->setOrderId($transaction->getOrderId());
            $job->setTransactionId($transaction->getTransactionId());
            $job->setSpaceId($transaction->getSpaceId());
            if ($save) {
                $job->save();
            }
        }
        return $job;
    }

    public function read(AbstractJob $job)
    {
        return $this->getService()->read($job->getSpaceId(), $job->getJobId());
    }

    /**
     * Gets the state that newly created jobs should have
     *
     * @return string
     */
    public static function getCreationState()
    {
        return TransactionCompletionState::CREATE;
    }

    public static function getPendingStates() {
        return array(
            TransactionCompletionState::PENDING
        );
    }

    public static function getFailedState()
    {
        return TransactionCompletionState::FAILED;
    }

    protected abstract function getJobType();

    public abstract function resendAll();

    public abstract function getSupportedTransactionStates();
}