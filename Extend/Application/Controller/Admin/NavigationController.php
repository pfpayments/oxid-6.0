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


namespace Pfc\PostFinanceCheckout\Extend\Application\Controller\Admin;

use Monolog\Logger;
use Pfc\PostFinanceCheckout\Application\Model\Alert;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class NavigationController.
 * Extends \OxidEsales\Eshop\Application\Controller\Admin\NavigationController.
 *
 * @mixin \OxidEsales\Eshop\Application\Controller\Admin\AdminController
 */
class NavigationController extends NavigationController_parent
{
	public function getPfcAlerts()
    {
        $alerts = array();
        foreach (Alert::loadAll() as $row) {
            if ($row[1] > 0) {
                switch ($row[0]) {
                    case Alert::KEY_MANUAL_TASK:
                        $alerts[] = array(
                            'func' => $row[2],
                            'target' => $row[3],
                            'title' => PostFinanceCheckoutModule::instance()->translate("Manual Tasks (!count)", true, array('!count' => $row[1]))
                        );
                        break;
                    default:
                        PostFinanceCheckoutModule::log(Logger::WARNING, "Unkown alert loaded from database: " . array($row));
                }
            }
        }
        return $alerts;
    }
}

