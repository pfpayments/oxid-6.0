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

/**
 * This entity holds data about a token on the gateway.
 */
class Token extends \OxidEsales\Eshop\Core\Model\BaseModel
{

	private $_sTableName = 'pfcPostFinanceCheckout_token';
	protected $_aSkipSaveFields = ['oxtimestamp', 'pfcupdated'];

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->init($this->_sTableName);
    }

    public function getTokenId()
    {
        return $this->getFieldData('pfctokenid');
    }

    public function getState()
    {
        return $this->getFieldData('pfcstate');
    }

    public function getSpaceId()
    {
        return $this->getFieldData('pfcspaceid');
    }

    public function getName()
    {
        return $this->getFieldData('pfcname');
    }

    public function getCustomerId()
    {
        return $this->getFieldData('pfccustomerid');
    }

    public function getPaymentMethodId()
    {
        return $this->getFieldData('pfcpaymentmethodid');
    }

    public function getConnectorId()
    {
        return $this->getFieldData('pfcconnectorid');
    }

    public function setTokenId($value)
    {
        $this->_setFieldData('pfctokenid', $value);
    }

    public function setState($value)
    {
        $this->_setFieldData('pfcstate', $value);
    }

    public function setSpaceId($value)
    {
        $this->_setFieldData('pfcspaceid', $value);
    }

    public function setName($value)
    {
        $this->_setFieldData('pfcname', $value);
    }

    public function setCustomerId($value)
    {
        $this->_setFieldData('pfccustomerid', $value);
    }

    public function setPaymentMethodId($value)
    {
        $this->_setFieldData('pfcpaymentmethodid', $value);
    }

    public function setConnectorId($value)
    {
        $this->_setFieldData('pfcconnectorid', $value);
    }

    public function loadByToken($spaceId, $tokenId)
    {
        $query = $this->buildSelectString(array('pfcspaceid' => $spaceId, 'pfctokenid' => $tokenId));
        return $this->assignRecord($query);
    }
}