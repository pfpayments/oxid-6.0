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

namespace Pfc\PostFinanceCheckout\Application\Controller;

use Monolog\Logger;
use Pfc\PostFinanceCheckout\Core\Service\CompletionService;
use Pfc\PostFinanceCheckout\Core\Service\RefundService;
use Pfc\PostFinanceCheckout\Core\Service\VoidService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class Cron.
 */
class Cron extends \OxidEsales\Eshop\Core\Controller\BaseController
{
    public function init()
    {
        $this->_Cron_init_parent();
        $this->endRequestPrematurely();

        $oxid = PostFinanceCheckoutModule::instance()->getRequestParameter('oxid');
        if (!$oxid) {
            PostFinanceCheckoutModule::log(Logger::WARNING, 'CRON called without id.');
            exit();
        }

        try {
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->startTransaction();
            $result = \Pfc\PostFinanceCheckout\Application\Model\Cron::setProcessing($oxid);
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->commitTransaction();
            if (!$result) {
                exit();
            }
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::log(Logger::ERROR, "Updating cron failed: {$e->getMessage()}.");
            PostFinanceCheckoutModule::rollback();
            exit();
        }

        $errors = array_merge(
            CompletionService::instance()->resendAll(),
            VoidService::instance()->resendAll(),
            RefundService::instance()->resendAll()
        );

        try {
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->startTransaction();
            $result = \Pfc\PostFinanceCheckout\Application\Model\Cron::setComplete($oxid, implode('. ', $errors));
            \Pfc\PostFinanceCheckout\Application\Model\Cron::insertNewPendingCron();
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->commitTransaction();
            if (!$result) {
                PostFinanceCheckoutModule::log(Logger::ERROR, "Could not update finished cron job.");
                exit();
            }
        } catch (\Exception $e) {
            PostFinanceCheckoutModule::rollback();
            PostFinanceCheckoutModule::log(Logger::ERROR, "Could not update finished cron job.");
            exit();
        }
        exit();
    }

    private function endRequestPrematurely()
    {
        ob_end_clean();
        // Return request but keep executing
        set_time_limit(0);
        ignore_user_abort(true);
        ob_start();
        if (session_id()) {
            session_write_close();
        }
        header("Content-Encoding: none");
        header("Connection: close");
        header('Content-Type: text/javascript');
        ob_end_flush();
        flush();
        if (is_callable('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    protected function _Cron_init_parent()
    {
        return parent::init();
    }

    public static function getCronUrl()
    {
        \Pfc\PostFinanceCheckout\Application\Model\Cron::cleanUpHangingCrons();
        \Pfc\PostFinanceCheckout\Application\Model\Cron::insertNewPendingCron();
        $oxid = \Pfc\PostFinanceCheckout\Application\Model\Cron::getCurrentPendingCron();
        return $oxid ? PostFinanceCheckoutModule::getControllerUrl('pfc_postFinanceCheckout_Cron', null, $oxid) : null;
    }
}