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
namespace Pfc\PostFinanceCheckout\Core\Webhook;

use Monolog\Logger;
use PostFinanceCheckout\Sdk\ApiException;
use PostFinanceCheckout\Sdk\Model\CreationEntityState;
use PostFinanceCheckout\Sdk\Model\CriteriaOperator;
use PostFinanceCheckout\Sdk\Model\DeliveryIndication;
use PostFinanceCheckout\Sdk\Model\DeliveryIndicationState;
use PostFinanceCheckout\Sdk\Model\EntityQuery;
use PostFinanceCheckout\Sdk\Model\EntityQueryFilter;
use PostFinanceCheckout\Sdk\Model\EntityQueryFilterType;
use PostFinanceCheckout\Sdk\Model\ManualTaskState;
use PostFinanceCheckout\Sdk\Model\RefundState;
use PostFinanceCheckout\Sdk\Model\TokenVersionState;
use PostFinanceCheckout\Sdk\Model\TransactionCompletionState;
use PostFinanceCheckout\Sdk\Model\TransactionInvoiceState;
use PostFinanceCheckout\Sdk\Model\TransactionState;
use PostFinanceCheckout\Sdk\Model\TransactionVoidState;
use PostFinanceCheckout\Sdk\Model\WebhookListener;
use PostFinanceCheckout\Sdk\Model\WebhookListenerCreate;
use PostFinanceCheckout\Sdk\Model\WebhookUrl;
use PostFinanceCheckout\Sdk\Model\WebhookUrlCreate;
use PostFinanceCheckout\Sdk\Service\WebhookListenerService;
use PostFinanceCheckout\Sdk\Service\WebhookUrlService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;

/**
 * This service handles webhooks.
 */
class Service
{

    /**
     * The webhook listener API service.
     *
     * @var \PostFinanceCheckout\Sdk\Service\WebhookListenerService
     */
    private $webhookListenerService;

    /**
     * The webhook url API service.
     *
     * @var \PostFinanceCheckout\Sdk\Service\WebhookUrlService
     */
    private $webhookUrlService;
    /**
     * @var Entity[]
     */
    private $webhookEntities = array();

    /**
     * Constructor to register the webhook entites.
     */
    public function __construct()
    {
        $this->webhookEntities[1487165678181] = new Entity(1487165678181, 'Manual Task',
            array(
                ManualTaskState::DONE,
                ManualTaskState::EXPIRED,
                ManualTaskState::OPEN
            ), ManualTask::class);
        $this->webhookEntities[1472041857405] = new Entity(1472041857405, 'Payment Method Configuration',
            array(
                CreationEntityState::ACTIVE,
                CreationEntityState::DELETED,
                CreationEntityState::DELETING,
                CreationEntityState::INACTIVE
            ), MethodConfiguration::class, true);
        $this->webhookEntities[1472041829003] = new Entity(1472041829003, 'Transaction',
            array(
                TransactionState::CONFIRMED,
                TransactionState::AUTHORIZED,
                TransactionState::DECLINE,
                TransactionState::FAILED,
                TransactionState::FULFILL,
                TransactionState::VOIDED,
                TransactionState::COMPLETED,
                TransactionState::PROCESSING
            ), Transaction::class);
        $this->webhookEntities[1472041819799] = new Entity(1472041819799, 'Delivery Indication',
            array(
                DeliveryIndicationState::MANUAL_CHECK_REQUIRED
            ), DeliveryIndication::class);

        $this->webhookEntities[1472041831364] = new Entity(1472041831364, 'Transaction Completion',
            array(
                TransactionCompletionState::FAILED,
                TransactionCompletionState::SUCCESSFUL
            ), TransactionCompletion::class);

        $this->webhookEntities[1472041867364] = new Entity(1472041867364, 'Transaction Void',
            array(
                TransactionVoidState::FAILED,
                TransactionVoidState::SUCCESSFUL
            ), TransactionVoid::class);

        $this->webhookEntities[1472041839405] = new Entity(1472041839405, 'Refund',
            array(
                RefundState::FAILED,
                RefundState::SUCCESSFUL
            ), TransactionRefund::class);
        $this->webhookEntities[1472041806455] = new Entity(1472041806455, 'Token',
            array(
                CreationEntityState::ACTIVE,
                CreationEntityState::DELETED,
                CreationEntityState::DELETING,
                CreationEntityState::INACTIVE
            ), Token::class);
        $this->webhookEntities[1472041811051] = new Entity(1472041811051, 'Token Version',
            array(
                TokenVersionState::ACTIVE,
                TokenVersionState::OBSOLETE
            ), TokenVersion::class);

        $this->webhookEntities[1472041816898] = new Entity(1472041816898, 'Transaction Invoice',
            array(
                TransactionInvoiceState::NOT_APPLICABLE,
                TransactionInvoiceState::PAID
            ), TransactionInvoice::class);
    }

    /**
     * Installs the necessary webhooks in PostFinanceCheckout.
     *
     * @param $spaceId
     * @param $url
     * @throws ApiException
     */
    public function install($spaceId, $url)
    {
        if ($spaceId !== null && !empty($url)) {
            $webhookUrl = $this->getWebhookUrl($spaceId, $url);
            if ($webhookUrl == null) {
                $webhookUrl = $this->createWebhookUrl($spaceId, $url);
            }
            $existingListeners = $this->getWebhookListeners($spaceId, $webhookUrl);
            foreach ($this->webhookEntities as $webhookEntity) {
                $exists = false;
                foreach ($existingListeners as $existingListener) {
                    if ($existingListener->getEntity() == $webhookEntity->getId()) {
                        $exists = true;
                    }
                }
                if (!$exists) {
                    $this->createWebhookListener($webhookEntity, $spaceId, $webhookUrl);
                }
            }
        }
    }

    /**
     * Removes any webhook listeners using the given URL, as well as the WebhookUrl itself.
     *
     * @param $spaceId
     * @param $url
     * @throws ApiException
     */
    public function uninstall($spaceId, $url)
    {
        if ($spaceId !== null && !empty($url)) {
            $webhookUrl = $this->getWebhookUrl($spaceId, $url);
            if ($webhookUrl == null) {
                PostFinanceCheckoutModule::log(Logger::ERROR, "Attempted to uninstall webhook with URL $url, but was not found.");
                return;
            }
            foreach ($this->getWebhookListeners($spaceId, $webhookUrl) as $listener) {
                $this->getWebhookListenerService()->delete($spaceId, $listener->getId());
            }

            $this->getWebhookUrlService()->delete($spaceId, $webhookUrl->getId());
        }
    }

    /**
     *
     * @param int|string $id
     * @return Entity
     */
    public function getWebhookEntityForId($id)
    {
        if (isset($this->webhookEntities[$id])) {
            return $this->webhookEntities[$id];
        }
        return null;
    }

    /**
     * Create a webhook listener.
     * @param Entity $entity
     * @param $spaceId
     * @param WebhookUrl $webhookUrl
     * @return WebhookListener
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function createWebhookListener(Entity $entity, $spaceId, WebhookUrl $webhookUrl)
    {
        $webhookListener = new WebhookListenerCreate();
        $webhookListener->setEntity($entity->getId());
        $webhookListener->setEntityStates($entity->getStates());
        $webhookListener->setName('Oxid ' . $entity->getName());
        /** @noinspection PhpParamsInspection */
        $webhookListener->setState(CreationEntityState::ACTIVE);
        $webhookListener->setUrl($webhookUrl->getId());
        $webhookListener->setNotifyEveryChange($entity->isNotifyEveryChange());
        return $this->getWebhookListenerService()->create($spaceId, $webhookListener);
    }

    /**
     * @param $spaceId
     * @param WebhookUrl $webhookUrl
     * @return WebhookListener[]
     * @throws ApiException
     */
    protected function getWebhookListeners($spaceId, WebhookUrl $webhookUrl)
    {
        $query = new EntityQuery();
        $filter = new EntityQueryFilter();
        /** @noinspection PhpParamsInspection */
        $filter->setType(EntityQueryFilterType::_AND);
        $filter->setChildren(
            array(
                $this->createEntityFilter('state', CreationEntityState::ACTIVE),
                $this->createEntityFilter('url.id', $webhookUrl->getId())
            ));
        $query->setFilter($filter);
        return $this->getWebhookListenerService()->search($spaceId, $query);
    }

    /**
     * @param $spaceId
     * @return WebhookUrl
     * @throws ApiException
     */
    protected function createWebhookUrl($spaceId, $url)
    {
        $webhookUrl = new WebhookUrlCreate();
        $webhookUrl->setUrl($url);
        /** @noinspection PhpParamsInspection */
        $webhookUrl->setState(CreationEntityState::ACTIVE);
        $webhookUrl->setName('Oxid');
        return $this->getWebhookUrlService()->create($spaceId, $webhookUrl);
    }


    /**
     * Returns existing WebhookUrl for the given url, if exists, or null.
     *
     * @param $spaceId
     * @param $url
     * @return null|WebhookUrl
     * @throws \PostFinanceCheckout\Sdk\ApiException
     */
    protected function getWebhookUrl($spaceId, $url)
    {
        $query = new EntityQuery();
        $query->setNumberOfEntities(1);
        $filter = new EntityQueryFilter();
        /** @noinspection PhpParamsInspection */
        $filter->setType(EntityQueryFilterType::_AND);
        $filter->setChildren(
            array(
                $this->createEntityFilter('state', CreationEntityState::ACTIVE),
                $this->createEntityFilter('url', $url)
            ));
        $query->setFilter($filter);
        $result = $this->getWebhookUrlService()->search($spaceId, $query);
        if (!empty($result)) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * Returns the webhook listener API service.
     *
     * @return WebhookListenerService
     */
    protected function getWebhookListenerService()
    {
        if ($this->webhookListenerService == null) {
            $this->webhookListenerService = new WebhookListenerService(PostFinanceCheckoutModule::instance()->getApiClient());
        }
        return $this->webhookListenerService;
    }

    /**
     * Returns the webhook url API service.
     *
     * @return WebhookUrlService
     */
    protected function getWebhookUrlService()
    {
        if ($this->webhookUrlService == null) {
            $this->webhookUrlService = new WebhookUrlService(PostFinanceCheckoutModule::instance()->getApiClient());
        }
        return $this->webhookUrlService;
    }

    /**
     * Creates and returns a new entity filter.
     *
     * @param string $fieldName
     * @param mixed $value
     * @param string $operator
     * @return EntityQueryFilter
     */
    protected function createEntityFilter($fieldName, $value, $operator = CriteriaOperator::EQUALS)
    {
        $filter = new EntityQueryFilter();
        /** @noinspection PhpParamsInspection */
        $filter->setType(EntityQueryFilterType::LEAF);
        /** @noinspection PhpParamsInspection */
        $filter->setOperator($operator);
        $filter->setFieldName($fieldName);
        $filter->setValue($value);
        return $filter;
    }

}