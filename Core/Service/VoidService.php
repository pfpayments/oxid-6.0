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
use PostFinanceCheckout\Sdk\Service\TransactionVoidService;
use Pfc\PostFinanceCheckout\Application\Model\AbstractJob;
use Pfc\PostFinanceCheckout\Application\Model\VoidJob;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class VoidService
 */
class VoidService extends JobService
{
    private $service;

    protected function getService()
    {
        if ($this->service === null) {
            $this->service = new TransactionVoidService(PostFinanceCheckoutModule::instance()->getApiClient());
        }
        return $this->service;
    }


    protected function getJobType()
    {
        return VoidJob::class;
    }

    public function getSupportedTransactionStates()
    {
        return array(
            TransactionState::AUTHORIZED
        );
    }

    protected function processSend(AbstractJob $job)
    {
        if (!$job instanceof VoidJob) {
            throw new \Exception("Invalid job type supplied.");
        }
        return $this->getService()->voidOnline($job->getSpaceId(), $job->getTransactionId());
    }

    public function resendAll()
    {
        $errors = array();
        $void = oxNew(VoidJob::class);
        /* @var $void \Pfc\PostFinanceCheckout\Application\Model\VoidJob */
        $notSent = $void->loadNotSentIds();
        foreach ($notSent as $job) {
            if ($void->loadByJob($job['PFCJOBID'], $job['PFCSPACEID'])) {
                $this->send($void);
                if ($void->getState() === self::getFailedState()) {
                    $errors[] = $void->getFailureReason();
                }
            } else {
                PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to load pending job {$job['PFCJOBID']} / {$job['PFCSPACEID']}.");
            }
        }
        return $errors;
    }
}