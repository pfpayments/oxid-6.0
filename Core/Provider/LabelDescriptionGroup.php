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

use PostFinanceCheckout\Sdk\Service\LabelDescriptionGroupService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Provider of label descriptor group information from the gateway.
 */
class LabelDescriptionGroup extends AbstractProvider
{

    protected function __construct()
    {
        parent::__construct('ox_PostFinanceCheckout_label_descriptor_group');
    }

    /**
     * Returns the label descriptor group by the given code.
     *
     * @param int $id
     * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup
     */
    public function find($id)
    {
        return parent::find($id);
    }

    /**
     * Returns a list of label descriptor groups.
     *
     * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup[]
     */
    public function getAll()
    {
        return parent::getAll();
    }

    /**
     * @return array|\PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup[]
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function fetchData()
    {
        $service = new LabelDescriptionGroupService(PostFinanceCheckoutModule::instance()->getApiClient());
        return $service->all();
    }

    protected function getId($entry)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup $entry */
        return $entry->getId();
    }
}