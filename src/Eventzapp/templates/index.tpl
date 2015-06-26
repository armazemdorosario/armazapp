{include file='head.tpl'}
{if $logged_in == '1'}
	{include file='app.tpl'}
{else}
	{include file='login.tpl'}
{/if}
{include file='foot.tpl'}