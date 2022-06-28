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
use PostFinanceCheckout\Sdk\Model\TransactionState;
use PostFinanceCheckout\Sdk\Service\TransactionCompletionService;
use Pfc\PostFinanceCheckout\Application\Model\AbstractJob;
use Pfc\PostFinanceCheckout\Application\Model\CompletionJob;
use Pfc\PostFinanceCheckout\Application\Model\Transaction;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class CompletionService
 */
class CompletionService extends JobService
{

    private $service;

    protected function getService()
    {
        if ($this->service === null) {
            $this->service = new TransactionCompletionService(PostFinanceCheckoutModule::instance()->getApiClient());
        }
        return $this->service;
    }


    protected function getJobType()
    {
        return CompletionJob::class;
    }

    public function getSupportedTransactionStates()
    {
        return array(
            TransactionState::AUTHORIZED
        );
    }

    protected function processSend(AbstractJob $job)
    {
        if (!$job instanceof CompletionJob) {
            throw new \Exception("Invalid job type supplied.");
        }
        return $this->getService()->completeOnline($job->getSpaceId(), $job->getTransactionId());
    }

    public function resendAll()
    {
        $errors = array();
        $completion = oxNew(CompletionJob::class);
        /* @var $completion \Pfc\PostFinanceCheckout\Application\Model\CompletionJob */
        $notSent = $completion->loadNotSentIds();
        foreach ($notSent as $job) {
            if ($completion->loadByJob($job['PFCJOBID'], $job['PFCSPACEID'])) {
                $transaction = oxNew(Transaction::class);
                /* @var $transaction Transaction */
                if ($transaction->loadByTransactionAndSpace($completion->getTransactionId(), $completion->getSpaceId())) {
                    $transaction->updateLineItems();
                    $this->send($completion);
                    if ($completion->getState() === self::getFailedState()) {
                        $errors[] = $completion->getFailureReason();
                    }
                } else {
                    $errors[] = PostFinanceCheckoutModule::instance()->translate("Unable to load transaction !id in space !space", true, array('!id' => $completion->getTransactionId(), '!space' => $completion->getSpaceId()));
                }
            } else {
                PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to load pending job {$job['PFCJOBID']} / {$job['PFCSPACEID']}.");
            }
        }
        return $errors;
    }
}