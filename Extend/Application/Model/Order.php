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
namespace Pfc\PostFinanceCheckout\Extend\Application\Model;

use PostFinanceCheckout\Sdk\Model\TransactionState;
use Pfc\PostFinanceCheckout\Application\Model\Transaction;
use Pfc\PostFinanceCheckout\Core\PostFinanceCheckoutModule;
use Monolog\Logger;

/**
 * Class Order.
 * Extends \OxidEsales\Eshop\Application\Model\Order.
 *
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 */
class Order extends Order_parent {
	private $confirming = false;
	private static $wleStateOrder = [
		TransactionState::CREATE => 0,
		TransactionState::PENDING => 1,
		TransactionState::CONFIRMED => 2,
		TransactionState::PROCESSING => 3,
		TransactionState::AUTHORIZED => 4,
		TransactionState::COMPLETED => 5,
		TransactionState::FULFILL => 6,
		TransactionState::DECLINE => 6,
		TransactionState::VOIDED => 6,
		TransactionState::FAILED => 6
	];

	public function setConfirming($confirming = true){
		$this->confirming = $confirming;
	}

	public function getPostFinanceCheckoutBasket(){
		// copied from recalculateOrder, minus call of finalizeOrder, and adding new articles.
		$oBasket = $this->_getOrderBasket();
		/* @noinspection PhpParamsInspection */
		$orderArticles = $this->getOrderArticles(true);
		// Here we populate the basket with Articles, not OrderArticles as the email templates expect the Article
		// Article = a product as it is in the catalog. This can change (price, image etc)
		// OrderArticle = a snapshot of the product at the time of ordering. This is fixed
		foreach ($orderArticles as $oOrderArticle) {
			$oArticle = new \OxidEsales\Eshop\Application\Model\Article();
			if ($oOrderArticle->oxorderarticles__oxbundleid->value) {
				// This is a bundle sub-item, skip it
				continue;
			}
			if ($oArticle->load($oOrderArticle->oxorderarticles__oxartid->value)) {
				$amount = (float) $oOrderArticle->oxorderarticles__oxamount->value;
				if ($amount > 0) {
					$oBasketItem = $oBasket->addToBasket($oArticle->getId(), $amount, null, $oOrderArticle->getPersParams(), false, $oOrderArticle->isBundle());
				}
			}
		}
		$oBasket->calculateBasket(true);
		return $oBasket;
	}

	/**
	 * Sets the oxtransstatus and oxfolder according to the given TransactionState
	 *
	 * @param string $state TransactionState enum
	 */
	public function setPostFinanceCheckoutState($state){
		if (!$this->isPfcOrder()) {
			PostFinanceCheckoutModule::log(Logger::WARNING,
					"Attempted to call " . __METHOD__ . " on non-PostFinanceCheckout order {$this->getId()}, skipping.");
			return;
		}
		$oldState = substr($this->getFieldData('OXTRANSSTATUS'), strlen('POSTFINANCECHECKOUT_'));
		if (self::$wleStateOrder[$oldState] > self::$wleStateOrder[$state]) {
			throw new \Exception("Cannot move order from state $oldState to $state.");
		}
		$this->_setFieldData('OXTRANSSTATUS', 'POSTFINANCECHECKOUT_' . $state);
		$this->_setFieldData('OXFOLDER', PostFinanceCheckoutModule::getMappedFolder($state));
	}

	/**
	 * Sends the confirmation email.
	 *
	 * @throws \Exception
	 */
	public function PostFinanceCheckoutAuthorize(){
		if (!$this->isPfcOrder()) {
			PostFinanceCheckoutModule::log(Logger::WARNING,
					"Attempted to call " . __METHOD__ . " on non-PostFinanceCheckout order {$this->getId()}, skipping.");
			return;
		}
		$basket = $this->getPostFinanceCheckoutBasket();
		$basket->onUpdate();
		$basket->calculateBasket();

		if ($this->checkPostFinanceCheckoutEmailSent()) {
			$res = $this->_sendOrderByEmail($this->getOrderUser(), $basket, $this->getPaymentType());
			$this->markPostFinanceCheckoutEmailAsSent();
			PostFinanceCheckoutModule::log(Logger::DEBUG, "Confirmation email was sent {$this->getId()}.");
		}
	}

	/**
	 * Check if the email was sent when the transaction state is updated to authorized to avoid duplicate emails.
	 * Return true if the email hasn't been emailed yet
	 *
	 * @return bool
	 */
	public function checkPostFinanceCheckoutEmailSent(){
		$currentState = substr($this->getFieldData('OXTRANSSTATUS'), strlen('POSTFINANCECHECKOUT_'));
		$transaction = $this->getPostFinanceCheckoutTransaction();

		if (!$transaction->getEmailSent()) {
			return true;
		}

		$sent = $transaction->getEmailSent() ? 'yes' : 'no';
		PostFinanceCheckoutModule::log(Logger::DEBUG,
			"Attempted to send an email on order {$this->getId()} with current state {$currentState}, was sent? {$sent}.");

		return false;
	}

	/**
	 * Marks as sent when the transaction state is updated to authorized only the first time.
	 */
	public function markPostFinanceCheckoutEmailAsSent(){
		$transaction = $this->getPostFinanceCheckoutTransaction();
		$transaction->markEmailAsSent();
		$transaction->save();
	}

	public function setPostFinanceCheckoutPaid(){
		if (!$this->isPfcOrder()) {
			PostFinanceCheckoutModule::log(Logger::WARNING,
					"Attempted to call " . __METHOD__ . " on non-PostFinanceCheckout order {$this->getId()}, skipping.");
			return;
		}
		$this->_setFieldData('oxpaid', date('Y-m-d H:i:s'), \OxidEsales\Eshop\Core\Field::T_RAW);
	}

	/**
	 * Sets the order state to the given state, and saves the message on the associated transaction.
	 *
	 * @param $message
	 * @param $state
	 * @param bool $cancel If the order should be cancelled
	 * @param bool $rethrow if exceptions should be thrown.
	 */
	public function PostFinanceCheckoutFail($message, $state, $cancel = false, $rethrow = false){
		if (!$this->isPfcOrder()) {
			PostFinanceCheckoutModule::log(Logger::WARNING,
					"Attempted to call " . __METHOD__ . " on non-PostFinanceCheckout order {$this->getId()}, skipping.");
			return;
		}
		$transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);

		/* @var $transaction Transaction */
		if ($transaction->loadByOrder($this->getId())) {
			try {
				$transaction->setFailureReason($message);
				$transaction->save();
				$this->resetCoupon();
			}
			catch (\Exception $e) {
				// treat optimisticlockingexception equally.
				PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to save transaction with ID {$transaction->getId()}: {$e->getMessage()}.");
				if ($rethrow) {
					throw $e;
				}
			}
		}
		else {
			PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to save failure message '{$message}' on transaction for order {$this->getId()}.");
		}
		$this->getSession()->deleteVariable("sess_challenge"); // allow new orders
		try {
			$this->setPostFinanceCheckoutState($state);
			if ($cancel) {
				$this->cancelOrder();
			}
		}
		catch (\Exception $e) {
			PostFinanceCheckoutModule::log(Logger::ERROR, "Unable to cancel order: {$e->getMessage()}.");
			if ($rethrow) {
				throw $e;
			}
		}
	}

	private function resetCoupon() {
		// have to reset the coupon to empty as they are already generated in the database
		$userId = $this->getSession()->getUser()->getId();
		$orderId = $this->getId();
		$query = "UPDATE oxvouchers 
			SET
				oxorderid = '',
				oxuserid = '',
				oxdiscount = NULL,
				oxdateused = NULL,
				oxreserved = 0
			WHERE 
				oxorderid = '{$orderId}' AND
				oxuserid = '{$userId}'";
		PostFinanceCheckoutModule::log(Logger::ERROR, "query {$query}.");
		\OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
	}

	public function getPostFinanceCheckoutDownloads(){
		$downloads = array();
		if ($this->isPfcOrder()) {
			$transaction = $this->getPostFinanceCheckoutTransaction();
			if ($transaction && in_array($transaction->getState(),
					array(
						TransactionState::COMPLETED,
						TransactionState::FULFILL,
						TransactionState::DECLINE
					))) {
				if (PostFinanceCheckoutModule::settings()->isDownloadInvoiceEnabled()) {
					$downloads[] = array(
						'link' => PostFinanceCheckoutModule::getControllerUrl('pfc_postFinanceCheckout_Pdf', 'invoice',
								$this->getId()),
						'text' => PostFinanceCheckoutModule::instance()->translate('Download Invoice')
					);
				}
				if (PostFinanceCheckoutModule::settings()->isDownloadPackingEnabled()) {
					$downloads[] = array(
						'link' => PostFinanceCheckoutModule::getControllerUrl('pfc_postFinanceCheckout_Pdf', 'packingSlip',
								$this->getId()),
						'text' => PostFinanceCheckoutModule::instance()->translate('Download Packing Slip')
					);
				}
			}
		}
		return $downloads;
	}

	public function finalizeOrder(\OxidEsales\Eshop\Application\Model\Basket $oBasket, $oUser, $blRecalculatingOrder = false){
		if (!$this->isPfcOrder($oBasket)) {
			return $this->_Order_finalizeOrder_parent($oBasket, $oUser, $blRecalculatingOrder);
		}

		if ($this->getFieldData('oxtransstatus') === 'POSTFINANCECHECKOUT_' . TransactionState::PENDING) {
			if ($this->confirming) {
				return self::ORDER_STATE_OK;
			}
			else {
				$transaction = $this->getPostFinanceCheckoutTransaction();
				if ($transaction) {
					PostFinanceCheckoutModule::instance()->getUtils()->redirect($transaction->getPaymentPageUrl());
					exit();
				}
				return self::ORDER_STATE_PAYMENTERROR;
			}
		}

		$result = $this->_Order_finalizeOrder_parent($oBasket, $oUser, $blRecalculatingOrder);

		if ($result == self::ORDER_STATE_OK && !$blRecalculatingOrder) {
			$result = 'POSTFINANCECHECKOUT_' . TransactionState::PENDING;
			$this->_setOrderStatus($result);

			// update transaction, confirm transaction, and redirect away
			if (!$this->confirming) {
				PostFinanceCheckoutModule::log(Logger::ERROR, "Attempted to finalize order without confirmation. Redirecting to payment page.",
						array(
							$this
						));
				$transaction = Transaction::loadPendingFromSession($this->getSession());
				$transaction->setTempBasket($this->getBasket());
				$transaction->setOrderId($this->getId());
				$transaction->updateFromSession(true);
				PostFinanceCheckoutModule::instance()->getUtils()->redirect($transaction->getPaymentPageUrl());
				exit();
			}
		}

		return $result;
	}

	protected function _Order_finalizeOrder_parent(\OxidEsales\Eshop\Application\Model\Basket $oBasket, $oUser, $blRecalculatingOrder = false){
		return parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
	}

	protected function _sendOrderByEmail($oUser = null, $oBasket = null, $oPayment = null){

		$isPfcOrder = $this->isPfcOrder();
		$isSettingEnabled = PostFinanceCheckoutModule::settings()->isEmailConfirmationActive();

		if ($isPfcOrder && $isSettingEnabled) {
			try {
				$transaction = Transaction::loadPendingFromSession($this->getSession());
				if ($transaction === null) {
					return self::ORDER_STATE_OK;
				}

				$sdkTransaction = $transaction->getSdkTransaction();
				$state = $sdkTransaction->getState();
				if (!PostFinanceCheckoutModule::isAuthorizedState($state)) {
					return self::ORDER_STATE_OK;
				}
			} catch (\Exception $e) {
				// OK, It's an edge case. Let's send email for webhook, we validated it earlier.
			}
		}

		return $this->_Order_sendOrderByEmail_parent($oUser, $oBasket, $oPayment);
	}

	protected function _sendOrderByEmailForced($oUser = null, $oBasket = null, $oPayment = null){
		$basketItem = oxNew(\OxidEsales\Eshop\Application\Model\BasketItem::class);
		/* @var $basketItem \Pfc\PostFinanceCheckout\Extend\Application\Model\BasketItem */
		$basketItem->pfcDisableCheckProduct(true);

		$result = $this->_sendOrderByEmail($oUser, $oBasket, $oPayment);

		$basketItem->pfcDisableCheckProduct(false);

		return $result;
	}

	protected function _Order_sendOrderByEmail_parent($oUser = null, $oBasket = null, $oPayment = null){
		return parent::_sendOrderByEmail($oUser, $oBasket, $oPayment);
	}

	public function isPfcOrder($basket = null){
		$paymentType = $this->getFieldData('oxpaymenttype');
		if (empty($paymentType)) {
			if ($this->getBasket()) {
				$paymentType = $this->getBasket()->getPaymentId();
			}
			else if ($basket instanceof \OxidEsales\Eshop\Application\Model\Basket) {
				$paymentType = $basket->getPaymentId();
			}
		}
		return substr($paymentType, 0, strlen(PostFinanceCheckoutModule::PAYMENT_PREFIX)) === PostFinanceCheckoutModule::PAYMENT_PREFIX;
	}

	public function getPostFinanceCheckoutTransaction(){
		if ($this->getId()) {
			$transaction = oxNew(\Pfc\PostFinanceCheckout\Application\Model\Transaction::class);
			/* @var $transaction Transaction */
			if ($transaction->loadByOrder($this->getId())) {
				return $transaction;
			}
		}
		return null;
	}
}
