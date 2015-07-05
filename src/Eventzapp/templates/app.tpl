{include file='app_header.tpl'}
<div class="container">
{include file='app_messages.tpl'}
{if $current_user_signed_up == 1}
	{include file=$layout}
{else}
	{include file='user_signup.tpl'}
{/if}
</div>
