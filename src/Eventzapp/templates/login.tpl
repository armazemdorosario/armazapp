<div id="login-box" class="col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">
{if $app_maintenance != 1}
{include file='app_messages.tpl'}
{if $login_error != 1}
	<h3>{t}Conecte-se ao {$app_name}{/t}</h3>
{else}
	<h3>{t}Não foi possível conectar você ao {$app_name} :({/t}</h3>
	<h4>{t}Por favor, tente novamente{/t}</h4>
{/if}
	<img alt="{$app_name}" src="{$app_url}{$html_meta.image}">
	<form action="https://{$login_url.host}{$login_url.path}" class="fb-login-button" method="get" target="_top">
{foreach key=key item=item from=$login_query_args}
		<input id="{$key}" name="{$key}" type="hidden" value="{$item}" />
{/foreach}
		<button class="fb_button btn btn-lg" onClick="_gaq.push(['_trackEvent', 'login', 'click', 'fb_button']);" id="submit" name="submit" type="submit">
			<span class="facebook-logo"></span>
			<span class="login-text">{t}Entrar com o Facebook{/t}</span>
		</button>
	</form>
	{else}
	<h3>Ish!</h3>
	<img alt="{$app_name}" src="{$app_url}{$html_meta.image}">
	<p>
		{t}Estamos fazendo uns ajustes no Armazapp. Por favor, volte um pouco mais tarde.{/t} 
		<a href="{$app_url}" target="_top" onClick="_gaq.push(['_trackEvent', 'back', 'click', 'from_error']);">{t}Back{/t}</a>
	</p>
	{/if}
</div>
<div class="clearfix"></div>
<form class="container" action="https://apps.facebook.com/armazemdorosario/privacy.php" target="_top">
	<p class="text-center">
		{t}Este app nunca postará nada em sua linha do tempo sem sua permissão.{/t} <!--<button class="btn btn-link">{t}Privacidade{/t}</button>-->
    </p>
</form>
