[{if ($oView->isPostFinanceCheckoutTransaction()) }]
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">[{oxmultilang ident="PAYMENT_INFORMATION"}]</h3>
	</div>
	<div class="panel-body">
		<div id="PostFinanceCheckout-iframe-spinner" class="postfinancecheckout-loader"></div>
		<div id="PostFinanceCheckout-iframe-container" style="display:none"></div>
	</div>
</div>
[{capture name=PostFinanceCheckoutInitScript assign=PostFinanceCheckoutInitScript}]
function initPostFinanceCheckoutIframe(){
	if(typeof PostFinanceCheckout === 'undefined') {
    	setTimeout(initPostFinanceCheckoutIframe, 500);
	} else {
    	PostFinanceCheckout.init('[{$oView->getPostFinanceCheckoutPaymentId()}]');
	}
}
jQuery().ready(initPostFinanceCheckoutIframe);
[{/capture}]
[{oxscript add=$PostFinanceCheckoutInitScript priority=10}]
[{oxscript include=$oView->getPostFinanceCheckoutJavascriptUrl() priority=8}]
[{oxscript include=$oViewConf->getModuleUrl("pfcPostFinanceCheckout", "out/src/js/postFinanceCheckout.js") priority=9}]
[{oxstyle include=$oViewConf->getModuleUrl("pfcPostFinanceCheckout", "out/src/css/spinner.css")}]
[{/if}]
[{$smarty.block.parent}]