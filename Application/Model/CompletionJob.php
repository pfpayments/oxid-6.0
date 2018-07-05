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

namespace Pfc\PostFinanceCheckout\Application\Model;

use Pfc\PostFinanceCheckout\Core\Service\CompletionService;
use Pfc\PostFinanceCheckout\Core\Service\JobService;

/**
 * Class CompletionJob.
 * CompletionJob model.
 */
class CompletionJob extends AbstractJob
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->init('pfcPostFinanceCheckout_completionjob');
    }

    /**
     * @return JobService
     */
    protected function getService()
    {
        return CompletionService::instance();
    }
}