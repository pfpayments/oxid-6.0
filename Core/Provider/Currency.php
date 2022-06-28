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

use PostFinanceCheckout\Sdk\Service\CurrencyService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Provider of currency information from the gateway.
 */
class Currency extends AbstractProvider
{

    protected function __construct()
    {
        parent::__construct('ox_PostFinanceCheckout_currency');
    }

    /**
     * Returns the currency by the given code.
     *
     * @param string $code
     * @return \PostFinanceCheckout\Sdk\Model\RestCurrency
     */
    public function find($code)
    {
        return parent::find($code);
    }

    /**
     * Returns a list of currencies.
     *
     * @return \PostFinanceCheckout\Sdk\Model\RestCurrency[]
     */
    public function getAll()
    {
        return parent::getAll();
    }

    /**
     * @return array|\PostFinanceCheckout\Sdk\Model\RestCurrency[]
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function fetchData()
    {
        $service = new CurrencyService(PostFinanceCheckoutModule::instance()->getApiClient());
        return $service->all();
    }

    protected function getId($entry)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\RestCurrency $entry */
        return $entry->getCurrencyCode();
    }
}