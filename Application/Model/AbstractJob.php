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
use PostFinanceCheckout\Sdk\Model\Refund;
use PostFinanceCheckout\Sdk\Model\TransactionCompletion;
use PostFinanceCheckout\Sdk\Model\TransactionVoid;
use Pfc\PostFinanceCheckout\Core\Service\JobService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Class AbstractJob.
 */
abstract class AbstractJob extends \OxidEsales\Eshop\Core\Model\BaseModel
{
	protected $_aSkipSaveFields = ['oxtimestamp', 'pfcupdated'];
    private $sdkObject;

    /**
     * @return mixed
     */
    public function getSdkObject()
    {
        return $this->sdkObject;
    }

    /**
     * @param mixed $sdkObject
     */
    public function setSdkObject($sdkObject)
    {
        $this->sdkObject = $sdkObject;
    }


    public function setJobId($value)
    {
        $this->_setFieldData('pfcjobid', $value);
    }

    public function getJobId()
    {
        return $this->getFieldData('pfcjobid');
    }

    public function setTransactionId($value)
    {
        $this->_setFieldData('pfctransactionid', $value);
    }

    public function getTransactionId()
    {
        return $this->getFieldData('pfctransactionid');
    }

    public function setState($value)
    {
        $this->_setFieldData('pfcstate', $value);
    }

    public function getState()
    {
        return $this->getFieldData('pfcstate');
    }

    public function setSpaceId($value)
    {
        $this->_setFieldData('pfcspaceid', $value);
    }

    public function getSpaceId()
    {
        return $this->getFieldData('pfcspaceid');
    }

    public function setOrderId($value)
    {
        $this->_setFieldData('oxorderid', $value);
    }

    public function getOrderId()
    {
        return $this->getFieldData('oxorderid');
    }

    public function setFailureReason($value)
    {
        $this->_setFieldData('pfcfailurereason', base64_encode(serialize($value)));
    }

    public function getFailureReason()
    {
        $value = unserialize(base64_decode($this->getFieldData('pfcfailurereason')));
        if (is_array($value)) {
            $value = PostFinanceCheckoutModule::instance()->PostFinanceCheckoutTranslate($value);
        }
        return $value;
    }

    public function loadByOrder($orderId, $targetStates = array())
    {
        $this->_addField('oxid', 0);
        $query = $this->buildSelectString(['oxorderid' => $orderId]);
        if (!empty($targetStates)) {
            $query .= " AND `pfcstate` in ('" . implode("', '", $targetStates) . "')";
        }
        $this->_isLoaded = $this->assignRecord($query);
        return $this->_isLoaded;
    }

    public function loadByJob($jobId, $spaceId)
    {
        $this->_addField('oxid', 0);
        $query = $this->buildSelectString(['pfcjobid' => $jobId, 'pfcspaceid' => $spaceId]);
        $this->_isLoaded = $this->assignRecord($query);
        return $this->_isLoaded;
    }

    /**
     * @throws \Exception
     */
    public function pull()
    {
        $this->apply($this->getService()->read($this));
    }

    /**
     * @return JobService
     */
    protected abstract function getService();

    /**
     * @param TransactionVoid|TransactionCompletion|Refund $job
     * @throws \Exception
     */
    public function apply($job)
    {
        $this->setJobId($job->getId());
        $this->setSpaceId($job->getLinkedSpaceId());

        // getState not in TransactionAwareEntity
        if ($job instanceof TransactionCompletion || $job instanceof TransactionVoid || $job instanceof Refund) {
            $this->setState($job->getState());
        }
        if ($job instanceof Refund) {
            $this->setTransactionId($job->getTransaction()->getId());
        } else {
            $this->setTransactionId($job->getLinkedTransaction());
        }
        $this->setSdkObject($job);
        $this->_isLoaded = true;
        $this->save();
    }

    protected function createNotSentQuery() {
        $table = $this->getCoreTableName();
        $createState = JobService::getCreationState();
        return "SELECT `PFCJOBID`, `PFCSPACEID` FROM `$table` WHERE `PFCSTATE` = '$createState';";
    }

    public function loadNotSentIds()
    {
        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select($this->createNotSentQuery());
    }
}