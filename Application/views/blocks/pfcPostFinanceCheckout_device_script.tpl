[{$smarty.block.parent}]

[{if $oViewConf && $oViewConf->getActiveClassName() == 'basket' && $PostFinanceCheckoutDeviceScript}]
	<script src="[{$PostFinanceCheckoutDeviceScript}]" async></script>
[{/if}]
