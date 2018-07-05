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

/**
 * Webhook processor to handle manual task state transitions.
 */
class ManualTask extends AbstractWebhook {

    /**
     * Updates the number of open manual tasks.
     *
     * @param \Pfc\PostFinanceCheckout\Core\Webhook\Request $request
     */
    public function process(Request $request){
        \Pfc\PostFinanceCheckout\Core\Service\ManualTask::instance()->update();
    }
}