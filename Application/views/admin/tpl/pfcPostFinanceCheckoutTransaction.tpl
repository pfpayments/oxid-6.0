[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="cur" value="[{ $oCurr->id }]">
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="order_main">
</form>

[{if $message}]
    <span>[{$message}]</span>
[{/if}]


[{foreach from=$labelGroupings key=groupingKey item=labelGrouping }]
    [{if $labelGrouping.labelGroup}]
    <table cellspacing="0" cellpadding="0" border="0" width="50%" id='PostFinanceCheckout-grouping-[{$groupingKey}]'>
        <tr>
            <td colspan="2"><h3>[{$labelGrouping.title}]</h3></td>
        </tr>
        <tr class="border">
            <td  class="listitem" colspan="2">
                <table>
                    [{foreach from=$labelGrouping.labelGroup item=labelGroup}]
                        <tr>
                            <td  class="listitem" colspan="2"><strong>[{$labelGroup.title}]</strong></td>
                        </tr>
                        [{foreach from=$labelGroup.labels item=label}]
                            <tr>
                                <td><strong title="[{$label.description}]">[{$label.title}]</strong></td>
                                <td>[{$label.value}]</td>
                            </tr>
                        [{/foreach}]
                    [{/foreach}]
                </table>
            </td>
        </tr>
    </table>
    [{/if}]
[{/foreach}]

[{if $oView->canVoid($oxid) }]
    <form name="pfcvoid" id="pfcvoid" method="POST" action="[{$oViewConf->getSelfLink()}]">
        <input type="hidden" name="fnc" value="void">
        <input type="hidden" name="cl" value="pfc_postFinanceCheckout_transaction">
        <input type="hidden" name="cur" value="[{ $oCurr->id }]">
        <input type="hidden" name="oxid" value="[{ $oxid }]">
        <input type="submit" value="[{oxmultilang ident='Void' noerror=true}]">
    </form>
[{/if}]

[{if $oView->canComplete($oxid) }]
    <form name="pfccomplete" id="pfccomplete" method="POST" action="[{$oViewConf->getSelfLink()}]">
        <input type="hidden" name="fnc" value="complete">
        <input type="hidden" name="cl" value="pfc_postFinanceCheckout_transaction">
        <input type="hidden" name="cur" value="[{ $oCurr->id }]">
        <input type="hidden" name="oxid" value="[{ $oxid }]">
        <input type="submit" value="[{oxmultilang ident='Complete' noerror=true}]">
    </form>
[{/if}]

[{if $oView->canRefund($oxid) }]
    <form name="pfcrefund" id="pfcrefund" method="POST" action="[{$oViewConf->getSelfLink()}]">
        <input type="hidden" name="cl" value="pfc_postFinanceCheckout_refundjob">
        <input type="hidden" name="oxid" value="[{ $oxid }]">
        <input type="submit" value="[{oxmultilang ident='Refund' noerror=true}]">
    </form>
[{/if}]

[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]