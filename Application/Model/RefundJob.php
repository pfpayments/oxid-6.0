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

use Pfc\PostFinanceCheckout\Core\Service\JobService;
use Pfc\PostFinanceCheckout\Core\Service\RefundService;

/**
 * Class RefundJob.
 * RefundJob model.
 */
class RefundJob extends AbstractJob
{
    private $formReductions;

    public function getRestock() {
        return $this->getFieldData('pfcrestock');
    }

    public function setRestock($value){
        $this->_setFieldData('pfcrestock', $value);
    }

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->init('pfcPostFinanceCheckout_refundjob');
    }

    public function setFormReductions(array $formReductions)
    {
        $this->formReductions = $formReductions;
    }
    public function getFormReductions(){
        return $this->formReductions;
    }

    /**
     * @return JobService
     */
    protected function getService()
    {
        return RefundService::instance();
    }
}