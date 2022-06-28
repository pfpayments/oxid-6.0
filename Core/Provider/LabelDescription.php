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
namespace Pfc\PostFinanceCheckout\Core\Provider;

use PostFinanceCheckout\Sdk\Service\LabelDescriptionService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Provider of label descriptor information from the gateway.
 */
class LabelDescription extends AbstractProvider
{

    protected function __construct()
    {
        parent::__construct('ox_PostFinanceCheckout_label_descriptor');
    }

    /**
     * Returns the label descriptor by the given code.
     *
     * @param int $id
     * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptor
     */
    public function find($id)
    {
        return parent::find($id);
    }

    /**
     * Returns a list of label descriptors.
     *
     * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptor[]
     */
    public function getAll()
    {
        return parent::getAll();
    }

    /**
     * @return array|\PostFinanceCheckout\Sdk\Model\LabelDescriptor[]
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function fetchData()
    {
        $service = new LabelDescriptionService(PostFinanceCheckoutModule::instance()->getApiClient());
        return $service->all();
    }

    protected function getId($entry)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\LabelDescriptor $entry */
        return $entry->getId();
    }
}