<form action="{$app_url}/{$app_routes.signup}" class="col-sm-6 col-sm-offset-3" method="POST" role="form">
	<fieldset class="row">
		<legend>{t}Antes de tudo, por favor, faça seu cadastro{/t}</legend>
		<div class="alert alert-info">
		{t}Antes de você começar a usar o app, você precisa confirmar umas coisinhas...{/t}&hellip;
		</div>
		<p class="form-group">
			<label class="control-label" for="name">{t}Qual é seu nome completo?{/t}</label>
			<input autocapitalize="words" autocomplete="name" autocorrect="off" class="form-control" id="name" maxlength="255" min="" name="name" pattern="{$user_patterns.name}" placeholder="{t}Nome completo, sem abreviaturas ou apelidos{/t}" required="required" title="{t}Por favor, use seu nome de verdade, igualzinho ao que está na sua carteira de identidade.{/t}" type="text" value="{post_data key=name}" />
		</p>
		<p class="help-block">
			{t}Você entrou como {/t}
		</p>
		<p class="form-group {if isset($id_card_form_group_class)}{$id_card_form_group_class}{/if}">
			<label class="control-label" for="id_card">{t}Qual o número da sua identidade?{/t}</label>
			<input autocapitalize="characters" autocomplete="off" autocorrect="off" class="form-control" data-ajaxurl="{$app_url}/api/id-card" id="id_card" maxlength="28" min="6" name="id_card" pattern="{$user_patterns.id_card}" placeholder="{t}Número do documento de identidade{/t}" required="required" title="{t}Nós perguntamos por sua identidade porque é ela que será repassada ao Armazém quando você entrar em Listas VIP ou ganhar sorteio. Nós nunca divulgamos esta informação a outros usuáros.{/t}" text="text" value="{post_data key=id_card}" />
		</p>
		<p class="form-group {if isset($ir_number_form_group_class)}{$ir_number_form_group_class}{/if}">
			<label class="control-label" for="ir_number">{t}Qual é o seu número de CPF?{/t}</label>
			<input autocapitalize="none" autocomplete="off" autocorrect="off" class="form-control" id="ir_number" maxlength="14" min="11" name="ir_number" pattern="{$user_patterns.ir_number}" placeholder="{t}Número do CPF{/t}" required="required" title="{t}Por favor, verifique se você informou seu CPF corretamente{/t}" value="{post_data key=ir_number}" />
		</p>
		<p class="help-block">
			{t}Atenção: estamos pedindo seu CPF apenas para evitar cadastros duplicados ou falsos. Esta informação não é divulgada a nenhum outro usuário{/t}
		</p>
		<p class="form-group">
			<input autocomplete="name" type="hidden" pattern="{$user_patterns.fbname}" value="{$current_user_name}" />
			<button class="btn btn-lg btn-primary" id="signupUserSubmit" name="signupUserSubmit" type="submit">
				{t}Fazer meu cadastro{/t}
			</button>
		</p>
		<p class="help-block">
			{t}Ao finalizar seu cadastro, você afirma que todas as informações são verdadeiras. Cadastros falsos ou duplicados serão removidos do app sem aviso prévio. {/t}
			{t}Sua participação está sujeita ao{/t} <a href="http://armazemdorosario.com.br/regulamento" onclick="_gaq.push(['_trackEvent', 'external', 'rules', 'from_user_signup']);" rel="external" target="_blank">{t}regulamento do Armazém do Rosário{/t}</a><!-- {t}and to our{/t} <a href="{$app_url}/privacy.php">{t}privacy policy{/t}</a>.-->
		</p>
	</fieldset>
</form>
<script>{literal}
(function() {
	jQuery('#id_card').change(function() {
		/*jQuery.ajax({
			type: 'POST',
			data: {
				'id_card': jQuery(this).val()
			},
			url: jQuery(this).data('ajaxurl'),
		});*/
	});
})();
{/literal}</script>