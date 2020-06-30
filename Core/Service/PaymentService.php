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
namespace Pfc\PostFinanceCheckout\Core\Service;

use PostFinanceCheckout\Sdk\Model\EntityQuery;
use PostFinanceCheckout\Sdk\Model\PaymentMethodConfiguration;
use PostFinanceCheckout\Sdk\Service\PaymentMethodConfigurationService;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use \PostFinanceCheckout\Sdk\Service\TransactionService as SdkTransactionService;
use PostFinanceCheckout\Sdk\Model\EntityQueryFilter;
use PostFinanceCheckout\Sdk\Model\EntityQueryFilterType;
use PostFinanceCheckout\Sdk\Model\CriteriaOperator;

/**
 * Class PaymentService
 * Handles api interactions regarding payment methods.
 */
class PaymentService extends AbstractService {
	private static $cache = array();
	private $transactionService;
	private $configurationService;
	
	const INTEGRATION_MODE_IFRAME = 'iframe';

	protected function getTransactionService(){
		if ($this->transactionService === null) {
			$this->transactionService = new SdkTransactionService(PostFinanceCheckoutModule::instance()->getApiClient());
		}
		return $this->transactionService;
	}

	protected function getConfigurationService(){
		if ($this->configurationService === null) {
			$this->configurationService = new PaymentMethodConfigurationService(PostFinanceCheckoutModule::instance()->getApiClient());
		}
		return $this->configurationService;
	}

	public static function getOxPaymentId($PostFinanceCheckoutId){
		return PostFinanceCheckoutModule::PAYMENT_PREFIX . $PostFinanceCheckoutId;
	}

	/**
	 * Fetches a list of available payment methods (oxpayment.oxid).
	 *
	 * @param $transactionId
	 * @param $spaceId
	 * @return array
	 */
	public function fetchAvailablePaymentMethods($transactionId, $spaceId){
		if (isset(self::$cache[$spaceId . $transactionId])) {
			return self::$cache[$spaceId . $transactionId];
		}
		try {
			$possibleMethods = $this->getTransactionService()->fetchPaymentMethods($spaceId, $transactionId, self::INTEGRATION_MODE_IFRAME);
			foreach ($possibleMethods as $paymentMethod) {
				self::$cache[$spaceId . $transactionId][] = PostFinanceCheckoutModule::createOxidPaymentId($paymentMethod->getId());
			}
		}
		catch (\Exception $e) {
			self::$cache[$spaceId . $transactionId] = array();
			throw $e;
		}
		return self::$cache[$spaceId . $transactionId];
	}

	/**
	 *
	 * @throws \Exception
	 * @throws \PostFinanceCheckout\Sdk\ApiException
	 */
	public function synchronize(){
		$paymentMethods = $this->getConfigurationService()->search(PostFinanceCheckoutModule::settings()->getSpaceId(), $this->getQueryFilter('state', \PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE));
		
		$paymentList = oxNew(\OxidEsales\Eshop\Application\Model\PaymentList::class);
		/* @var $paymentList \Pfc\PostFinanceCheckout\Extend\Application\Model\PaymentList */
		$paymentList->loadPostFinanceCheckoutPayments();
		
		foreach ($paymentMethods as $paymentMethod) {
			if (!$this->updatePaymentMethod($paymentMethod)) {
				$existing_found[] = self::getOxPaymentId($paymentMethod->getId());
			}
		}
		
		foreach ($paymentList as $payment) {
			/* @var $payment \OxidEsales\Eshop\Application\Model\Payment */
			if (!in_array($payment->getId(), $existing_found)) {
				self::disablePaymentMethod($payment->getId());
			}
		}
	}
	
	private function getQueryFilter($fieldName, $fieldValue){
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
		$filter->setValue($fieldValue);
		$query->setFilter($filter);
		return $query;
	}
	
	/**
	 *
	 * @param $paymentId
	 * @throws \Exception
	 */
	private static function disablePaymentMethod($paymentId){
		$payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
		/* @var $payment \OxidEsales\Eshop\Application\Model\Payment */
		if ($payment->load($paymentId)) {
			$payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(0);
			$payment->save();
		}
	}

	/**
	 * Adds or updates the given payment method.
	 * Returns true if the method was newly created, or false if an existing payment method was updated.
	 *
	 * @param PaymentMethodConfiguration $paymentMethod
	 * @return bool
	 * @throws \Exception
	 */
	private function updatePaymentMethod(PaymentMethodConfiguration $paymentMethod){
		$newMethod = false;
		
		$payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
		/* @var $payment \OxidEsales\Eshop\Application\Model\Payment */
		if (!$payment->load(self::getOxPaymentId($paymentMethod->getId()))) {
			$payment->setId(self::getOxPaymentId($paymentMethod->getId()));
			$payment->oxpayments__oxactive = new \OxidEsales\Eshop\Core\Field(1);
			$payment->oxpayments__oxaddsum = new \OxidEsales\Eshop\Core\Field(0);
			$payment->oxpayments__oxaddsumtype = new \OxidEsales\Eshop\Core\Field('abs');
			$payment->oxpayments__oxfromboni = new \OxidEsales\Eshop\Core\Field(0);
			$payment->oxpayments__oxfromamount = new \OxidEsales\Eshop\Core\Field(0);
			$payment->oxpayments__oxtoamount = new \OxidEsales\Eshop\Core\Field(100000);
			$newMethod = true;
		}
		
		$payment->oxpayments__oxsort = new \OxidEsales\Eshop\Core\Field($paymentMethod->getSortOrder());
		
		$language = \OxidEsales\Eshop\Core\Registry::getLang();
		$languages = $language->getLanguageIds();
		
		$titles = $paymentMethod->getResolvedTitle();
		$descriptions = $paymentMethod->getResolvedDescription();
		
		/**
		 * @noinspection PhpParamsInspection
		 */
		foreach (array_keys($titles) as $languageCode) {
			$languageId = array_search(substr($languageCode, 0, 2), $languages);
			if ($languageId !== false) {
				$payment->setLanguage($languageId);
				$payment->oxpayments__oxdesc = new \OxidEsales\Eshop\Core\Field($titles[$languageCode]);
				$payment->oxpayments__oxlongdesc = new \OxidEsales\Eshop\Core\Field($descriptions[$languageCode]);
				$payment->save();
			}
		}
		
		return $newMethod;
	}
}