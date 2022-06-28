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
namespace Pfc\PostFinanceCheckout\Core\Service;

use PostFinanceCheckout\Sdk\Model\EntityQuery;
use PostFinanceCheckout\Sdk\Model\EntityQueryFilter;
use PostFinanceCheckout\Sdk\Model\EntityQueryFilterType;
use PostFinanceCheckout\Sdk\Model\TokenVersion;
use PostFinanceCheckout\Sdk\Model\TokenVersionState;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * This service provides functions to deal with PostFinanceCheckout tokens.
 */
class Token extends AbstractService
{

    /**
     * The token API service.
     *
     * @var \PostFinanceCheckout\Sdk\Service\TokenService
     */
    private $tokenService;

    /**
     * The token version API service.
     *
     * @var \PostFinanceCheckout\Sdk\Service\TokenVersionService
     */
    private $versionService;

    public function updateTokenVersion($spaceId, $tokenVersionId)
    {
        $version = $this->getTokenVersionService()->read($spaceId, $tokenVersionId);
        $this->updateInfo($spaceId, $version);
    }

    public function updateToken($spaceId, $tokenId)
    {
        $query = new EntityQuery();
        $filter = new EntityQueryFilter();
        $filter->setType(EntityQueryFilterType::_AND);
        $filter->setChildren(
            array(
                $this->createEntityFilter('token.id', $tokenId),
                $this->createEntityFilter('state', TokenVersionState::ACTIVE)
            ));
        $query->setFilter($filter);
        $query->setNumberOfEntities(1);
        $versions = $this->getTokenVersionService()->search($spaceId, $query);
        if (!empty($versions)) {
            $this->updateInfo($spaceId, current($versions));
        } else {
            $token = $this->loadToken($spaceId, $tokenId);
            $token->delete();
        }
    }

    protected function updateInfo($spaceId, TokenVersion $version)
    {
        $token = $this->loadToken($spaceId, $version->getToken()->getId());
        if (!in_array($version->getToken()->getState(),
            array(
                TokenVersionState::ACTIVE,
                TokenVersionState::UNINITIALIZED
            ))) {
            $token->delete();
            return;
        }

        $token->setCustomerId($version->getToken()->getCustomerId());
        $token->setName($version->getName());
        $token->setPaymentMethodId($version->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getPaymentMethod());
        $token->setConnectorId($version->getPaymentConnectorConfiguration()->getConnector());

        $token->setSpaceId($spaceId);
        $token->setState($version->getToken()->getState());
        $token->setTokenId($version->getToken()->getId());
        $token->save();
    }

    protected function loadToken($spaceId, $tokenId)
    {
        $token = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Token::class);
        /* @var $token \Pfc\PostFinanceCheckout\Application\Model\Token */
        $token->loadByToken($spaceId, $tokenId);
        return $token;
     }

    public function deleteToken($spaceId, $tokenId)
    {
        $this->getTokenService()->delete($spaceId, $tokenId);
    }

    /**
     * Returns the token API service.
     *
     * @return \PostFinanceCheckout\Sdk\Service\TokenService
     */
    protected function getTokenService()
    {
        if ($this->tokenService == null) {
            $this->tokenService = new \PostFinanceCheckout\Sdk\Service\TokenService(PostFinanceCheckoutModule::instance()->getApiClient());
        }

        return $this->tokenService;
    }

    /**
     * Returns the token version API service.
     *
     * @return \PostFinanceCheckout\Sdk\Service\TokenVersionService
     */
    protected function getTokenVersionService()
    {
        if ($this->versionService == null) {
            $this->versionService = new \PostFinanceCheckout\Sdk\Service\TokenVersionService(PostFinanceCheckoutModule::instance()->getApiClient());
        }

        return $this->versionService;
    }
}