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
namespace Pfc\PostFinanceCheckout\Application\Model;

use Monolog\Logger;
use PostFinanceCheckout\Sdk\Model\CriteriaOperator;
use PostFinanceCheckout\Sdk\Model\EntityQuery;
use PostFinanceCheckout\Sdk\Model\EntityQueryFilter;
use PostFinanceCheckout\Sdk\Model\EntityQueryFilterType;
use PostFinanceCheckout\Sdk\Model\Label;
use PostFinanceCheckout\Sdk\Model\Refund;
use PostFinanceCheckout\Sdk\Model\TransactionCompletion;
use PostFinanceCheckout\Sdk\Model\TransactionLineItemUpdateRequest;
use PostFinanceCheckout\Sdk\Model\TransactionState;
use PostFinanceCheckout\Sdk\Model\TransactionVoid;
use PostFinanceCheckout\Sdk\Service\RefundService;
use PostFinanceCheckout\Sdk\Service\TransactionCompletionService;
use PostFinanceCheckout\Sdk\Service\TransactionVoidService;
use Pfc\PostFinanceCheckout\Core\Adapter\BasketAdapter;
use Pfc\PostFinanceCheckout\Core\Adapter\SessionAdapter;
use Pfc\PostFinanceCheckout\Core\Service\TransactionService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use PostFinanceCheckout\Sdk\Model\Transaction as sdkTransaction;
use PostFinanceCheckout\Sdk\ApiException;
use Pfc\PostFinanceCheckout\Core\Exception\OptimisticLockingException;

/**
 * Class Transaction.
 * Transaction model.
 */
class Transaction extends \OxidEsales\Eshop\Core\Model\BaseModel {
	private $_sTableName = 'pfcPostFinanceCheckout_transaction';
	private $version = false;
	protected $dbVersion = null;
	/**
	 *
	 * @var sdkTransaction
	 */
	private $sdkTransaction;
	protected $_aSkipSaveFields = [
		'oxtimestamp',
		'pfcversion',
		'pfcupdated' 
	];

	/**
	 * Class constructor.
	 */
	public function __construct(){
		parent::__construct();
		
		$this->init($this->_sTableName);
	}

	public function getTransactionId(){
		return $this->getFieldData('pfctransactionid');
	}

	public function getOrderId(){
		return $this->getFieldData('oxorderid');
	}

	public function getSdkTransaction(){
		return $this->sdkTransaction;
	}

	public function getState(){
		return $this->getFieldData('pfcstate');
	}

	public function getSpaceId(){
		return $this->getFieldData('pfcspaceid');
	}

	/**
	 *
	 * @return \OxidEsales\Eshop\Application\Model\Basket
	 */
	public function getTempBasket(){
		return unserialize(base64_decode($this->getFieldData('pfctempbasket')));
	}

	public function setTempBasket($basket){
		$this->_setFieldData('pfctempbasket', base64_encode(serialize($basket)));
	}

	public function getSpaceViewId(){
		return $this->getFieldData('pfcspaceviewid');
	}

	public function setFailureReason($value){
		$this->_setFieldData('pfcfailurereason', base64_encode(serialize($value)));
	}

	public function getFailureReason(){
		$value = unserialize(base64_decode($this->getFieldData('pfcfailurereason')));
		if (is_array($value)) {
			$value = PostFinanceCheckoutModule::instance()->PostFinanceCheckoutTranslate($value);
		}
		return $value;
	}

	public function getVersion(){
		return $this->version;
	}

	public function setOrderId($value){
		$this->_setFieldData('oxorderid', $value);
	}

	protected function setState($value){
		$this->_setFieldData('pfcstate', $value);
	}

	protected function setSpaceId($value){
		$this->_setFieldData('pfcspaceid', $value);
	}

	protected function setSpaceViewId($value){
		$this->_setFieldData('pfcspaceviewid', $value);
	}

	protected function setTransactionId($value){
		$this->_setFieldData('pfctransactionid', $value);
	}

	protected function setVersion($value){
		$this->version = $value;
	}

	protected function setSdkTransaction($value){
		$this->sdkTransaction = $value;
	}

	public function loadByOrder($orderId){
		$select = $this->buildSelectString(array(
			'oxorderid' => $orderId 
		));
		$this->_isLoaded = $this->assignRecord($select);
		$this->dbVersion = $this->getFieldData('pfcversion');
		return $this->_isLoaded;
	}

	/**
	 *
	 * @param \OxidEsales\Eshop\Core\Session $session
	 * @return bool|object|Transaction
	 * @throws ApiException
	 * @throws \Exception
	 */
	public static function loadPendingFromSession(\OxidEsales\Eshop\Core\Session $session){
		$transaction = self::loadFromSession($session, TransactionState::PENDING);
		if (!$transaction) {
			$transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
			/* @var $transaction \Pfc\PostFinanceCheckout\Application\Model\Transaction */
			$transaction->create();
		}
		return $transaction;
	}

	/**
	 *
	 * @param \OxidEsales\Eshop\Core\Session $session
	 * @return bool|object|Transaction
	 * @throws ApiException
	 * @throws \Exception
	 */
	public static function loadConfirmedFromSession(\OxidEsales\Eshop\Core\Session $session){
		return self::loadFromSession($session, TransactionState::CONFIRMED);
	}

	/**
	 *
	 * @param \OxidEsales\Eshop\Core\Session $session
	 * @return bool|object|Transaction
	 * @throws ApiException
	 * @throws \Exception
	 */
	public static function loadFailedFromSession(\OxidEsales\Eshop\Core\Session $session){
		return self::loadFromSession($session, TransactionState::FAILED);
	}

	/**
	 * Loads a transaction from the variables stored in the session, with the given state (In PostFinanceCheckout, not in DB).
	 *
	 * @param \OxidEsales\Eshop\Core\Session $session
	 * @param $expectedState
	 * @return bool|object|Transaction
	 * @throws ApiException
	 * @throws \Exception
	 */
	protected static function loadFromSession(\OxidEsales\Eshop\Core\Session $session, $expectedState){
		$transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
		/* @var $transaction Transaction */
		$transactionId = $session->getVariable('PostFinanceCheckout_transaction_id');
		$spaceId = $session->getVariable('PostFinanceCheckout_space_id');
		$userId = $session->getVariable('PostFinanceCheckout_user_id');
		
		if ($transactionId && $spaceId && $userId == $session->getUser()->getId() && $spaceId == PostFinanceCheckoutModule::settings()->getSpaceId()) {
			if (!$transaction->loadByTransactionAndSpace($transactionId, $spaceId)) {
				$transaction->setSpaceId($spaceId);
				$transaction->setTransactionId($transactionId);
			}
			$transaction->pull();
			if ($transaction->getState() === $expectedState) {
				$transaction->dbVersion = $transaction->getFieldData('pfcversion');
				return $transaction;
			}
		}
		return false;
	}

	public function loadByTransactionAndSpace($transactionId, $spaceId){
		$select = $this->buildSelectString(
				array(
					'pfctransactionid' => $transactionId,
					'pfcspaceid' => $spaceId 
				));
		$this->_isLoaded = $this->assignRecord($select);
		$this->dbVersion = $this->getFieldData('pfcversion');
		return $this->_isLoaded;
	}

	public function getLabels(){
		return array(
			'transaction' => $this->getTransactionLabels(),
			'completions' => array(
				'title' => PostFinanceCheckoutModule::instance()->translate('Completions'),
				'labelGroup' => $this->getCompletionLabels() 
			),
			'voids' => array(
				'title' => PostFinanceCheckoutModule::instance()->translate('Voids'),
				'labelGroup' => $this->getVoidLabels() 
			),
			'refunds' => array(
				'title' => PostFinanceCheckoutModule::instance()->translate('Refunds'),
				'labelGroup' => $this->getRefundLabels() 
			) 
		);
	}

	/**
	 * Creates a query containing a filter for the transaction id.
	 * The field name can be overwritten using the parameter, standard is transaction.id
	 *
	 * @param string $fieldName
	 * @return EntityQuery
	 */
	private function getTransactionQuery($fieldName = 'transaction.id'){
		$query = new EntityQuery();
		$filter = new EntityQueryFilter();
		/**
		 * @noinspection PhpParamsInspection
		 */
		$filter->setType(EntityQueryFilterType::LEAF);
		/**
		 * @noinspection PhpParamsInspection
		 */
		$filter->setOperator(CriteriaOperator::EQUALS);
		$filter->setFieldName($fieldName);
		/**
		 * @noinspection PhpParamsInspection
		 */
		$filter->setValue($this->getTransactionId());
		$query->setFilter($filter);
		return $query;
	}

	private function getTransactionLabels(){
		$paymentMethod = $paymentDescription = '';
		if ($this->getSdkTransaction()->getPaymentConnectorConfiguration()) {
			if ($this->getSdkTransaction()->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()) {
				$paymentDescription = PostFinanceCheckoutModule::instance()->PostFinanceCheckoutTranslate(
						$this->getSdkTransaction()->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getResolvedDescription());
				$paymentMethod = PostFinanceCheckoutModule::instance()->PostFinanceCheckoutTranslate(
						$this->getSdkTransaction()->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getResolvedTitle());
			}
			else {
				$paymentMethod = $this->getSdkTransaction()->getPaymentConnectorConfiguration()->getName();
				$paymentDescription = $this->getSdkTransaction()->getPaymentConnectorConfiguration()->getId();
			}
		}
		
		$openText = PostFinanceCheckoutModule::instance()->translate('Open');
		$labels = array(
			'title' => PostFinanceCheckoutModule::instance()->translate('Transaction information'),
			'labelGroup' => array(
				array(
					'title' => PostFinanceCheckoutModule::instance()->translate("Transaction #!id", true, array(
						'!id' => $this->getTransactionId() 
					)),
					'labels' => array(
						array(
							'title' => PostFinanceCheckoutModule::instance()->translate('Status'),
							'description' => PostFinanceCheckoutModule::instance()->translate('Status in the PostFinance Checkout system'),
							'value' => $this->getState() 
						),
						array(
							'title' => PostFinanceCheckoutModule::instance()->translate('PostFinance Checkout Link'),
							'description' => PostFinanceCheckoutModule::instance()->translate('Open in your PostFinance Checkout backend'),
							'value' => $this->getPostFinanceCheckoutLink('transaction', $this->getSpaceId(), $this->getTransactionId(), $openText) 
						),
						array(
							'title' => PostFinanceCheckoutModule::instance()->translate('Authorization amount'),
							'description' => PostFinanceCheckoutModule::instance()->translate(
									'The amount which was authorized with the PostFinance Checkout transaction.'),
							'value' => $this->getSdkTransaction()->getAuthorizationAmount() 
						),
						array(
							'title' => PostFinanceCheckoutModule::instance()->translate('Payment method'),
							'description' => $paymentDescription,
							'value' => $paymentMethod 
						) 
					) 
				) 
			) 
		);
		
		$order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
		if (!$order->load($this->getOrderId())) {
			throw new \Exception("Unable to load order {$this->getOrderId()} for transaction {$this->getTransactionId()}.");
		}
		
		foreach ($order->getPostFinanceCheckoutDownloads() as $download) {
			$labels['labelGroup'][0]['labels'][] = array(
				'title' => $download['text'],
				'description' => $download['text'],
				'value' => "<a href='{$download['link']}' target='_blank' style='text-decoration: underline;'>$openText</a>" 
			);
		}
		
		return $labels;
	}

	private function getPostFinanceCheckoutLink($type, $space, $id, $link_text){
		$base_url = PostFinanceCheckoutModule::settings()->getBaseUrl();
		$url = "$base_url/s/$space/payment/$type/view/$id";
		return "<a href='$url' target='_blank' style='text-decoration: underline;'>$link_text</a>";
	}

	/**
	 *
	 * @return array
	 * @throws ApiException
	 */
	private function getCompletionLabels(){
		$service = new TransactionCompletionService(PostFinanceCheckoutModule::instance()->getApiClient());
		$completions = $service->search($this->getSpaceId(), $this->getTransactionQuery('lineItemVersion.transaction.id'));
		return $this->convertJobLabels($completions);
	}

	/**
	 *
	 * @param TransactionCompletion[]|TransactionVoid[]|Refund[] $jobs
	 * @return array
	 */
	private function convertJobLabels($jobs){
		$labelGroup = array();
		foreach ($jobs as $job) {
			$jobLabels = array(
				array(
					'title' => PostFinanceCheckoutModule::instance()->translate('Status'),
					'description' => PostFinanceCheckoutModule::instance()->translate('Status in the PostFinance Checkout system.'),
					'value' => $job->getState() 
				),
				array(
					'title' => PostFinanceCheckoutModule::instance()->translate('PostFinance Checkout Link'),
					'description' => PostFinanceCheckoutModule::instance()->translate('Open in your PostFinance Checkout backend.'),
					'value' => $this->getPostFinanceCheckoutLink($this->getJobLinkType($job), $job->getLinkedSpaceId(), $job->getId(),
							PostFinanceCheckoutModule::instance()->translate('Open')) 
				) 
			);
			foreach ($job->getLabels() as $label) {
				$jobLabels[] = $this->convertLabel($label);
			}
			if ($job instanceof Refund) {
				$message = 'Refund #!id';
			}
			else if ($job instanceof TransactionCompletion) {
				$message = 'Completion #!id';
			}
			else if ($job instanceof TransactionVoid) {
				$message = 'Void #!id';
			}
			else {
				$message = get_class($job) . ' !id';
			}
			
			$labelGroup[$job->getId()] = array(
				'title' => PostFinanceCheckoutModule::instance()->translate($message, true, array(
					'!id' => $job->getId() 
				)),
				'labels' => $jobLabels 
			);
		}
		return $labelGroup;
	}

	private function getJobLinkType($job){
		if ($job instanceof TransactionVoid) {
			return 'void';
		}
		else if ($job instanceof TransactionCompletion) {
			return 'completion';
		}
		else if ($job instanceof Refund) {
			return 'refund';
		}
		$type = get_class($job);
		PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to match job link type for $type.");
		return $type;
	}

	private function convertLabel(Label $label){
		/**
		 * @noinspection PhpParamsInspection
		 */
		return array(
			'title' => PostFinanceCheckoutModule::instance()->PostFinanceCheckoutTranslate($label->getDescriptor()->getName()),
			'description' => PostFinanceCheckoutModule::instance()->PostFinanceCheckoutTranslate($label->getDescriptor()->getDescription()),
			'value' => $label->getContentAsString() 
		);
	}

	/**
	 *
	 * @return array
	 * @throws ApiException
	 */
	private function getVoidLabels(){
		$service = new TransactionVoidService(PostFinanceCheckoutModule::instance()->getApiClient());
		$voids = $service->search($this->getSpaceId(), $this->getTransactionQuery());
		return $this->convertJobLabels($voids);
	}

	/**
	 *
	 * @return array
	 * @throws ApiException
	 */
	private function getRefundLabels(){
		$service = new RefundService(PostFinanceCheckoutModule::instance()->getApiClient());
		$refunds = $service->search($this->getSpaceId(), $this->getTransactionQuery());
		return $this->convertJobLabels($refunds);
	}

	/**
	 *
	 * @throws ApiException
	 * @throws \Exception
	 */
	public function pull(){
		PostFinanceCheckoutModule::log(Logger::DEBUG, "Start transaction pull.");
		if (!$this->getTransactionId()) {
			throw new \Exception('Transaction id must be set to pull.');
		}
		$this->apply(TransactionService::instance()->read($this->getTransactionId(), $this->getSpaceId()));
		PostFinanceCheckoutModule::log(Logger::DEBUG, "Transaction pull complete.");
	}

	/**
	 *
	 * @param bool $confirm
	 * @return sdkTransaction
	 * @throws ApiException
	 * @throws \Exception
	 */
	public function updateFromSession($confirm = false){
		PostFinanceCheckoutModule::log(Logger::DEBUG, "Start update from session.");
		$this->pull(); // ensure updateable
		if ($this->getState() !== TransactionState::PENDING) {
			throw new \Exception('Transaction not in state PENDING may no longer be updated:' . $this->getTransactionId());
		}
		
		$adapter = new SessionAdapter($this->getSession());
		$transaction = TransactionService::instance()->update($adapter->getUpdateData($this), $confirm);
		$this->apply($transaction);
		PostFinanceCheckoutModule::log(Logger::DEBUG, "Complete update from session.");
		return $transaction;
	}
	
	public function getPaymentPageUrl() {
		return TransactionService::instance()->getPaymentPageUrl($this->getTransactionId(), $this->getSpaceId());
	}

	public function updateLineItems(){
		PostFinanceCheckoutModule::log(Logger::DEBUG, "Start update line items.");
		$order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
		/* @var $order\OxidEsales\Eshop\Application\Model\Order */
		if (!$order->load($this->getOrderId())) {
			throw new \Exception("Unable to load order {$this->getOrderId()} for transaction {$this->getTransactionId()}.");
		}
		$adapter = new BasketAdapter($order->getPostFinanceCheckoutBasket());
		$adapter->getLineItemData();
		$update = new TransactionLineItemUpdateRequest();
		$update->setNewLineItems($adapter->getLineItemData());
		$update->setTransactionId($this->getTransactionId());
		TransactionService::instance()->updateLineItems($this->getSpaceId(), $update);
		$this->pull();
		PostFinanceCheckoutModule::log(Logger::DEBUG, "Complete update line items.");
		return $this->getSdkTransaction();
	}

	/**
	 *
	 * @return sdkTransaction
	 * @throws ApiException
	 * @throws \Exception
	 */
	public function create(){
		PostFinanceCheckoutModule::log(Logger::DEBUG, "Start transaction create.");
		$adapter = new SessionAdapter($this->getSession());
		$transaction = TransactionService::instance()->create($adapter->getCreateData());
		$this->dbVersion = 0;
		$this->apply($transaction);
		
		PostFinanceCheckoutModule::log(Logger::DEBUG, "Complete transaction create.");
		return $transaction;
	}

	/**
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function save(){
		PostFinanceCheckoutModule::log(Logger::DEBUG, "Start transaction save.");
		// only save to db with order, otherwise save relevant ids to session.
		if ($this->getOrderId()) {
			PostFinanceCheckoutModule::log(Logger::DEBUG, "Saving to database.");
			return parent::save();
		}
		else if ($this->getSession()->getUser()) {
			PostFinanceCheckoutModule::log(Logger::DEBUG, "Saving to session.");
			$this->getSession()->setVariable('PostFinanceCheckout_transaction_id', $this->getTransactionId());
			$this->getSession()->setVariable('PostFinanceCheckout_space_id', $this->getSpaceId());
			$this->getSession()->setVariable('PostFinanceCheckout_user_id', $this->getSession()->getUser()->getId());
		}
		return false;
	}

	/**
	 *
	 * @param sdkTransaction $transaction
	 * @throws \Exception
	 */
	protected function apply(sdkTransaction $transaction){
		$this->setSdkTransaction($transaction);
		$this->setTransactionId($transaction->getId());
		$this->setVersion($transaction->getVersion());
		$this->setState($transaction->getState());
		$this->setSpaceId($transaction->getLinkedSpaceId());
		$this->setSpaceViewId($transaction->getSpaceViewId());
		$this->save();
	}

	/**
	 * Overwrite _update method to introduce optimistic locking.
	 *
	 * {@inheritdoc}
	 * @see \OxidEsales\EshopCommunity\Core\Model\BaseModel::_update()
	 */
	protected function _update(){
		//do not allow derived item update
		if (!$this->allowDerivedUpdate()) {
			return false;
		}
		
		if (!$this->getId()) {
			$exception = oxNew(\OxidEsales\Eshop\Core\Exception\ObjectException::class);
			$exception->setMessage('EXCEPTION_OBJECT_OXIDNOTSET');
			$exception->setObject($this);
			throw $exception;
		}
		$coreTableName = $this->getCoreTableName();
		
		$idKey = \OxidEsales\Eshop\Core\Registry::getUtils()->getArrFldName($coreTableName . '.oxid');
		$this->$idKey = new \OxidEsales\Eshop\Core\Field($this->getId(), \OxidEsales\Eshop\Core\Field::T_RAW);
		$database = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
		
		$dbVersion = $this->dbVersion;
		if (!$dbVersion) {
			$dbVersion = 0;
		}
		$updateQuery = "update {$coreTableName} set " . $this->_getUpdateFields() . " , pfcversion=pfcversion + 1 " .
				 " where {$coreTableName}.oxid = " . $database->quote($this->getId()) .
				 " and {$coreTableName}.pfcversion = {$dbVersion}";
		PostFinanceCheckoutModule::log(Logger::DEBUG, "Updating  transaction with query [$updateQuery]");
		
		$this->beforeUpdate();
		$affected = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($updateQuery);
		
		if ($affected === 0) {
			throw new OptimisticLockingException($this->getId(), $this->_sTableName, $updateQuery);
		}
		
		return true;
	}
}