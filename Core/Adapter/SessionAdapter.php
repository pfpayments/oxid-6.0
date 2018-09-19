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
namespace Pfc\PostFinanceCheckout\Core\Adapter;

use PostFinanceCheckout\Sdk\Model\AbstractTransactionPending;
use PostFinanceCheckout\Sdk\Model\TransactionCreate;
use PostFinanceCheckout\Sdk\Model\TransactionPending;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use Pfc\PostFinanceCheckout\Application\Model\Transaction;

/**
 * Class SessionAdapter
 * Converts Oxid Session Data into data which can be fed into the PostFinanceCheckout SDK.
 *
 * @codeCoverageIgnore
 */
class SessionAdapter implements ITransactionServiceAdapter {
	private $session = null;
	private $basketAdapter = null;
	private $addressAdapter = null;

	/**
	 * SessionAdapter constructor.
	 *
	 * Checks if user is logged in and basket is present as well, and throws an exception if either is not present.
	 *
	 * @param \OxidEsales\Eshop\Core\Session $session
	 * @throws \Exception
	 */
	public function __construct(\OxidEsales\Eshop\Core\Session $session){
		if (!$session->getUser() || !$session->getBasket()) {
			throw new \Exception("User must be logged in and basket must be present.");
		}
		$this->session = $session;
		$this->basketAdapter = new BasketAdapter($session->getBasket());
		$this->addressAdapter = new AddressAdapter($session->getUser()->getSelectedAddress(), $session->getUser());
	}

	public function getCreateData(){
		$transactionCreate = new TransactionCreate();
		if (isset($_COOKIE['PostFinanceCheckout_device_id'])) {
			$transactionCreate->setDeviceSessionIdentifier($_COOKIE['PostFinanceCheckout_device_id']);
		}
		$transactionCreate->setAutoConfirmationEnabled(false);
		$this->applyAbstractTransactionData($transactionCreate);
		return $transactionCreate;
	}

	public function getUpdateData(Transaction $transaction){
		$transactionPending = new TransactionPending();
		$transactionPending->setId($transaction->getTransactionId());
		$transactionPending->setVersion($transaction->getVersion());
		$this->applyAbstractTransactionData($transactionPending);
		
		if ($transaction->getOrderId()) {
			$transactionPending->setFailedUrl(
					PostFinanceCheckoutModule::getControllerUrl('order', 'pfcError', $transaction->getOrderId()));
			$order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
			/* @var $order \OxidEsales\Eshop\Application\Model\Order */
			if ($order->load($transaction->getOrderId())) {
				$transactionPending->setMerchantReference($order->oxorder__oxordernr->value);
				$transactionPending->setAllowedPaymentMethodConfigurations(
						array(
							PostFinanceCheckoutModule::extractPostFinanceCheckoutId($order->oxorder__oxpaymenttype->value) 
						));
			}
		}
		
		return $transactionPending;
	}

	private function applyAbstractTransactionData(AbstractTransactionPending $transaction){
		$transaction->setCustomerId($this->session->getUser()->getId());
		$transaction->setCustomerEmailAddress($this->session->getUser()->getFieldData('oxusername'));
		/**
		 * @noinspection PhpUndefinedFieldInspection
		 */
		$transaction->setCurrency($this->session->getBasket()->getBasketCurrency()->name);
		$transaction->setLineItems($this->basketAdapter->getLineItemData());
		$transaction->setBillingAddress($this->addressAdapter->getBillingAddressData());
		$transaction->setShippingAddress($this->addressAdapter->getShippingAddressData());
		$transaction->setLanguage(\OxidEsales\Eshop\Core\Registry::getLang()->getLanguageAbbr());
		$transaction->setSuccessUrl(PostFinanceCheckoutModule::getControllerUrl('thankyou'));
		$transaction->setFailedUrl(PostFinanceCheckoutModule::getControllerUrl('order', 'pfcError'));
	}
}