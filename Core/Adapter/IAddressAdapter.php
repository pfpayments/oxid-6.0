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

namespace Pfc\PostFinanceCheckout\Core\Adapter;

/**
 * Interface IAddressAdapter
 * Defines which methods must be implemented to be consumed with PostFinanceCheckout SDK.
 *
 * @codeCoverageIgnore
 */
interface IAddressAdapter {

	function getBillingAddressData();

	function getShippingAddressData();
}