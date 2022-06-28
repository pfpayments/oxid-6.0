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

use Monolog\Logger;
use PostFinanceCheckout\Sdk\Model\EntityQuery;
use PostFinanceCheckout\Sdk\Model\TransactionCreate;
use PostFinanceCheckout\Sdk\Model\TransactionLineItemUpdateRequest;
use PostFinanceCheckout\Sdk\Model\TransactionPending;
use PostFinanceCheckout\Sdk\Service\TransactionInvoiceService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use \PostFinanceCheckout\Sdk\Service\TransactionService as SdkTransactionService;
use \PostFinanceCheckout\Sdk\Service\TransactionIframeService;
use \PostFinanceCheckout\Sdk\Service\TransactionPaymentPageService;

/**
 * Class TransactionService
 * Handles api interactions regarding transaction.
 *
 * @codeCoverageIgnore
 */
class TransactionService extends AbstractService {
	private $service;
	private $invoiceService;
	private $paymentPageService;
	private $iframeService;

	protected function getService(){
		if (!$this->service) {
			$this->service = new SdkTransactionService(PostFinanceCheckoutModule::instance()->getApiClient());
		}
		return $this->service;
	}

	/**
	 *
	 * @return TransactionInvoiceService
	 */
	protected function getInvoiceService(){
		if (!$this->invoiceService) {
			$this->invoiceService = new TransactionInvoiceService(PostFinanceCheckoutModule::instance()->getApiClient());
		}
		return $this->invoiceService;
	}
	
	/**
	 *
	 * @return TransactionPaymentPageService
	 */
	protected function getPaymentPageService(){
		if (!$this->paymentPageService) {
			$this->paymentPageService = new TransactionPaymentPageService(PostFinanceCheckoutModule::instance()->getApiClient());
		}
		return $this->paymentPageService;
	}
	
	/**
	 *
	 * @return TransactionIframeService
	 */
	protected function getIframeService(){
		if (!$this->iframeService) {
			$this->iframeService = new TransactionIframeService(PostFinanceCheckoutModule::instance()->getApiClient());
		}
		return $this->iframeService;
	}

	/**
	 * Reads a transaction entity from PostFinanceCheckout
	 *
	 * @param $transactionId
	 * @param $spaceId
	 * @return \PostFinanceCheckout\Sdk\Model\Transaction
	 * @throws \PostFinanceCheckout\Sdk\ApiException
	 */
	public function read($transactionId, $spaceId){
		return $this->getService()->read($spaceId, $transactionId);
	}

	/**
	 *
	 * @param TransactionCreate $transaction
	 * @return \PostFinanceCheckout\Sdk\Model\Transaction
	 * @throws \PostFinanceCheckout\Sdk\ApiException
	 */
	public function create(TransactionCreate $transaction){
		return $this->getService()->create(PostFinanceCheckoutModule::settings()->getSpaceId(), $transaction);
	}

	/**
	 *
	 * @param $transactionId
	 * @param $spaceId
	 * @return \PostFinanceCheckout\Sdk\Model\TransactionInvoice
	 * @throws \Exception
	 * @throws \PostFinanceCheckout\Sdk\ApiException
	 */
	public function getInvoice($transactionId, $spaceId){
		$query = new EntityQuery();
		$query->setFilter($this->createEntityFilter('completion.lineItemVersion.transaction.id', $transactionId));
		$query->setNumberOfEntities(1);
		$invoices = $this->getInvoiceService()->search($spaceId, $query);
		if (empty($invoices)) {
			throw new \Exception("No transaction invoice found for transaction $transactionId / $spaceId.");
		}
		return $invoices[0];
	}

	/**
	 *
	 * @param string $transactionId
	 * @param string $spaceId
	 * @return string
	 * @throws \PostFinanceCheckout\Sdk\ApiException
	 */
	public function getPaymentPageUrl($transactionId, $spaceId){
		return $this->getPaymentPageService()->paymentPageUrl($spaceId, $transactionId);
	}

	/**
	 *
	 * @param TransactionPending $transaction
	 * @param bool $confirm
	 * @return \PostFinanceCheckout\Sdk\Model\Transaction
	 * @throws \PostFinanceCheckout\Sdk\ApiException
	 */
	public function update(TransactionPending $transaction, $confirm = false){
		if ($confirm) {
			return $this->getService()->confirm(PostFinanceCheckoutModule::settings()->getSpaceId(), $transaction);
		}
		else {
			return $this->getService()->update(PostFinanceCheckoutModule::settings()->getSpaceId(), $transaction);
		}
	}

	public function updateLineItems($spaceId, TransactionLineItemUpdateRequest $updateRequest){
		return $this->getService()->updateTransactionLineItems($spaceId, $updateRequest);
	}

	/**
	 *
	 * @param $transactionId
	 * @param $spaceId
	 * @return string
	 * @throws \PostFinanceCheckout\Sdk\ApiException
	 */
	public function getJavascriptUrl($transactionId, $spaceId){
		return $this->getIframeService()->javascriptUrl($spaceId, $transactionId);
	}
}