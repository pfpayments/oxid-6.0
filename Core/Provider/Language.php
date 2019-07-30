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
namespace Pfc\PostFinanceCheckout\Core\Provider;

use PostFinanceCheckout\Sdk\Service\LanguageService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * Provider of language information from the gateway.
 */
class Language extends AbstractProvider
{

    protected function __construct()
    {
        parent::__construct('ox_PostFinanceCheckout_languages');
    }

    /**
     * Returns the language by the given code.
     *
     * @param string $code
     * @return \PostFinanceCheckout\Sdk\Model\RestLanguage
     */
    public function find($code)
    {
        return parent::find($code);
    }

    /**
     * Returns the primary language in the given group.
     *
     * @param string $code
     * @return bool|\PostFinanceCheckout\Sdk\Model\RestLanguage
     */
    public function findPrimary($code)
    {
        $code = substr($code, 0, 2);
        foreach ($this->getAll() as $language) {
            if ($language->getIso2Code() == $code && $language->getPrimaryOfGroup()) {
                return $language;
            }
        }

        return false;
    }

    /**
     * @param $iso
     * @return bool|\PostFinanceCheckout\Sdk\Model\RestLanguage
     */
    public function findByIsoCode($iso)
    {
        foreach ($this->getAll() as $language) {
            if ($language->getIso2Code() == $iso || $language->getIso3Code() == $iso) {
                return $language;
            }
        }
        return false;
    }

    /**
     * Returns a list of language.
     *
     * @return \PostFinanceCheckout\Sdk\Model\RestLanguage[]
     */
    public function getAll()
    {
        return parent::getAll();
    }

    /**
     * @return array|\PostFinanceCheckout\Sdk\Model\RestLanguage[]
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function fetchData()
    {
        $service = new LanguageService(PostFinanceCheckoutModule::instance()->getApiClient());
        return $service->all();
    }

    protected function getId($entry)
    {
        /* @var \PostFinanceCheckout\Sdk\Model\RestLanguage $entry */
        return $entry->getIetfCode();
    }
}