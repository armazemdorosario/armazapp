<form action="{$app_url}/{$app_routes.viplist}/{$benefit->eventfbid}/" class="row" method="post">
	<input id="eventfbid" name="eventfbid" type="hidden" value="{$benefit->eventfbid}" />
	<input id="eventname" name="eventname" type="hidden" value="{$benefit->name}" />
	<input id="eventpicture" name="eventpicture" type="hidden" value="https://graph.facebook.com/{$benefit->eventfbid}/picture?width=206&amp;height=206" />
	<p class="col-xs-12">
		<button class="btn btn-primary btn-lg" id="attendToEventSubmit" name="attendToEventSubmit" onClick="_gaq.push(['_trackEvent', 'attend_to_viplist', 'btn_primary_click', '{$benefit->eventfbid}']);" type="submit">
			{t}Entrar na Lista VIP{/t}
		</button>
	</p>
	<p class="col-xs-12">
		<label data-placement="bottom" data-toggle="tooltip" for="private">
			<input onClick="_gaq.push(['_trackEvent', 'private', 'checkbox_click', '{$benefit->eventfbid}']);" id="private" name="private" type="checkbox" />
			{t}Modo privado{/t}
		</label>
		<small>{t}(Se você ativar este recurso, outros usuários não saberão que você está na Lista VIP){/t}</small>
	</p>
</form>
