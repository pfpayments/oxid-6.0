[{$smarty.block.parent}]
[{foreach from=$oView->getPfcAlerts() item=alert}]
    <li class="sep">
        <a href="[{$oViewConf->getSelfLink()}]&cl=pfc_postFinanceCheckout_Alert&amp;fnc=[{$alert.func}]" target="[{$alert.target}]" class="rc"><b>[{$alert.title}]</b></a>
    </li>
[{/foreach}]