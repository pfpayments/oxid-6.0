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
namespace Pfc\PostFinanceCheckout\Core\Webhook;
use Pfc\PostFinanceCheckout\Core\Service\PaymentService;

/**
 * Webhook processor to handle payment method configuration state transitions.
 */
class MethodConfiguration extends AbstractWebhook
{

    /**
     * Synchronizes the payment method configurations on state transition.
     * @param Request $request
     * @throws \Exception
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    public function process(Request $request)
    {
        $paymentService = new PaymentService();
        $paymentService->synchronize();
    }
}