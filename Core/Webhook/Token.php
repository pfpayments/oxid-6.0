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
namespace Pfc\PostFinanceCheckout\Core\Webhook;

use \Pfc\PostFinanceCheckout\Core\Service\Token as TokenService;

/**
 * Webhook processor to handle token state transitions.
 */
class Token extends AbstractWebhook {

	public function process(Request $request){
		TokenService::instance()->updateToken($request->getSpaceId(), $request->getEntityId());
	}
}