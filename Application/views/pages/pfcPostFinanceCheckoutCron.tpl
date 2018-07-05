[{smarty.block.parent}]
[{assign var=cronUrl value=$oViewConf->getViewConfigParam('pfcCronUrl')}]
[{if $cronUrl}]
<script type="text/javascript" async="async" src="[{$cronUrl}]"></script>
[{/if}]