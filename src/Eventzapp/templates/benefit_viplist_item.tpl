<article class="has-progress-on-top event-{$benefit->eventfbid} benefit-{$benefit->benefit_type}" id="benefit-{$benefit->eventfbid}-{$benefit->benefit_type}">
	<div class="list-group-item {benefit_classes benefit=$benefit}">
		{include file='benefit_header.tpl'}
		<p>{t}O limite desta Lista VIP é de {/t}{$benefit->max_num_people_chosen} {g gender=$benefit->accepted_gender general='pessoas' females='mulheres' males='homens'}</p>
		{if $benefit->is_full_vip_list != 1 AND $benefit->current_gender_can_attend == 1}
			{if $benefit->current_user_attended == 1}
				<p class="alert alert-success">
					<span class="label label-success">
						<span class="glyphicon glyphicon-check"></span>
						<strong>{t}Aê!{/t}</strong>&mdash;{t}Seu nome tá na lista!{/t}
					</span>
				</p>
				<div class="clear clearfix"></div>
			{else}
				{if $benefit->status == 1}
					{if $current_user_can_enter_vip_lists == 1}
						{include file='benefit_viplist_form.tpl'}
					{else}
						<p class="alert alert-danger">
							{t}Você não pode participar desta Lista VIP porque faltou a algum evento no qual ganhou entrada nos últimos 15 dias :({/t}
						</p>
					{/if}<!-- end if current user can enter vip lists -->
				{elseif $benefit->status == 3}
					<p>{t}Esta Lista VIP é mantida pelos{/t} <a href="http://armazemdorosario.com.br/promoters/" rel="external" target="_blank">{t}promoters da casa{/t}</a>. <a href="http://armazemdorosario.com.br/promoters/" rel="external" target="_blank">{t}Peça-os para entrar na lista.{/t}</a>.</p>
				{else}
				{/if}<!-- end if benefit status test -->
			{/if}<!-- end if current user attended -->
		{/if}<!-- end if vip list is full and current gender can attend -->

		{if $benefit->is_full_vip_list == 1 AND $benefit->current_gender_can_attend == 1}
			{if $benefit->current_user_attended == 1}
				<div class="alert alert-success">
					<label class="label label-success">{t}Ô sorte, hein!{/t}</label> {t}Você conseguiu entrar na Lista antes dela ter sido fechada{/t}</div>
				</div>
			{else}
				<div class="alert alert-warning">
					<strong class="label label-warning">
						<span class="glyphicon glyphicon-ban-circle"></span> {t}Ih... lista cheia!{/t}
					</strong> &mdash; {t}Fica pra próxima!{/t} :(
				</div>
			{/if}
		{/if}
		<p class="text-info">{$benefit->info_text}</p>
		<!--
		<p>
			<a class="btn btn-sm btn-default" href="{$app_url}/benefits/{$benefit->eventfbid}/{$benefit->benefit_type}" onClick="_gaq.push(['_trackEvent', 'see_whos_on_the_list', 'btn_default_click', '{$benefit->eventfbid}/{$benefit->benefit_type}']);" target="_top" >
				{t}See who is on the list{/t}
			</a>
		</p>
		-->
		<footer>
			<p>
		        <small>
		            {t}Data de fechamento da lista{/t}: {$benefit->db_expiration_date|date_format:"%d/%m/%Y, %H:%M"}.
		            <a href="{$app_url}/{$app_routes.viplist}/{$benefit->eventfbid}/{$app_routes.rules}/" onclick="_gaq.push(['_trackEvent', 'rules', 'rules_click', '{$benefit->eventfbid}/{$benefit->benefit_type}']); return false;">{t}Regulamento{/t}</a>
		        </small>
		    </p>
		</footer>
	</div>
	<div class="progress">
        <div class="progress-bar progress-bar-{level_css_class level=$benefit->remaining_percentage}" role="progressbar" aria-valuenow="{$benefit->progress}" aria-valuemin="0" aria-valuemax="100" data-remaining="{$benefit->remaining_percentage}" style="width: {$benefit->progress}%; ?>">
            <span class="sr-only">{t}Lista{/t} {$benefit->progress}% {t}cheia{/t}</span>
        </div>
    </div>
</article>
