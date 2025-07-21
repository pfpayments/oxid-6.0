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

namespace Pfc\PostFinanceCheckout\Application\Controller;

use Monolog\Logger;

use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use Pfc\PostFinanceCheckout\Core\Webhook\Request;
use Pfc\PostFinanceCheckout\Core\Webhook\Service as WebhookService;

/**
 * Class Webhook.
 */
class Webhook extends \OxidEsales\Eshop\Core\Controller\BaseController
{
	public function notify()
	{
        $webhookService = new WebhookService();

        $requestBody = trim(file_get_contents("php://input"));
        set_error_handler(array(
            __CLASS__,
            'handleWebhookErrors'
        ));
        try {
        	$request = new Request(json_decode($requestBody));
        	$webhookModel = $webhookService->getWebhookEntityForId($request->getListenerEntityId());
        	PostFinanceCheckoutModule::log(Logger::INFO, "Webhook process started.", [$webhookModel, $requestBody]);
            if ($webhookModel === null) {
                PostFinanceCheckoutModule::log(Logger::ERROR, "Could not retrieve webhook model for listener entity id: {$request->getListenerEntityId()}.");
                header("HTTP/1.1 500 Internal Server Error");
                echo "Could not retrieve webhook model for listener entity id: {$request->getListenerEntityId()}.";
                exit();
            }
            $webhookHandlerClassName = $webhookModel->getHandlerClassName();
            $webhookHandler = $webhookHandlerClassName::instance();
            $webhookHandler->process($request);
        } catch (\Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            echo($e->getMessage());
            $message = "Oops, something was wrong. {$e->getMessage()} - {$e->getTraceAsString()}.";
            PostFinanceCheckoutModule::log(Logger::ERROR, $message);
            PostFinanceCheckoutModule::getUtilsView()->addErrorToDisplay($message);
            exit();
        }

        exit();
    }

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @return bool
     * @throws \ErrorException
     */
    public static function handleWebhookErrors($errno, $errstr, $errfile, $errline)
    {
        $fatal = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
        if ($errno & $fatal) {
            throw new \ErrorException($errstr, $errno, E_ERROR, $errfile, $errline);
        }
        return false;
    }

    /**
     * @return string|void
     * @throws \Exception
     */
    public function render()
    {
        throw new \Exception("This page may not be rendered.");
    }
}
