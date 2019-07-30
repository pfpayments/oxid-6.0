<?php
/**
 * PostFinanceCheckout OXID
 *
 * This OXID module enables to process payments with PostFinanceCheckout (https://www.postfinance.ch/checkout/).
 *
 * @package Whitelabelshortcut\PostFinanceCheckout
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache Software License (ASL 2.0)
 */
namespace Pfc\PostFinanceCheckout\Core\Service;

use Monolog\Logger;
use PostFinanceCheckout\Sdk\Model\ManualTaskState;
use PostFinanceCheckout\Sdk\Service\ManualTaskService;
use Pfc\PostFinanceCheckout\Application\Model\Alert;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * This service provides methods to handle manual tasks.
 */
class ManualTask extends AbstractService
{
    /**
     * Updates the number of open manual tasks.
     *
     * @throws \Exception
     * @return int
     */
    public function update()
    {
        try {
            $service = new ManualTaskService(PostFinanceCheckoutModule::instance()->getApiClient());

            $taskCount = $service->count(PostFinanceCheckoutModule::settings()->getSpaceId(),
                $this->createEntityFilter('state', ManualTaskState::OPEN));

            Alert::setCount(Alert::KEY_MANUAL_TASK, $taskCount);

            return $taskCount;
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to update manual tasks: {$e->getMessage()} - {$e->getTraceAsString()}.");
            throw $e;
        }
    }
}