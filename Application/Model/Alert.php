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

namespace Pfc\PostFinanceCheckout\Application\Model;

;

/**
 * Class Alert.
 */
class Alert
{
    const KEY_MANUAL_TASK = 'manual_task';

    protected static function getTableName()
    {
        return 'pfcPostFinanceCheckout_alert';
    }

    public static function setCount($key, $count) {
        $count = (int)$count;
        $key = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->quote($key);
        $query = "UPDATE `pfcPostFinanceCheckout_alert` SET `pfccount`=$count WHERE `pfckey`=$key;";
        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query) === 1;
    }

    public static function modifyCount($key, $countModifier = 1) {
        $countModifier = (int)$countModifier;
        $key = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->quote($key);
        $query = "UPDATE `pfcPostFinanceCheckout_alert` SET `PFCCOUNT`=`PFCCOUNT`+$countModifier WHERE `pfckey`=$key;";
        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query) === 1;
    }

    public static function loadAll() {
        $query = "SELECT `PFCKEY`, `PFCCOUNT`, `PFCFUNC`, `PFCTARGET` FROM `pfcPostFinanceCheckout_alert`";
        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getAll($query);
    }

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->init(self::getTableName());
    }
}