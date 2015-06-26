<header id="header">
	<div class="clear clearfix"></div>
	<div class="container page-header">
		<a class="col-xs-5 col-sm-2 col-md-2" href="{$app_url}/?userfbid={$current_user_id}" data-placement="bottom" data-toggle="tooltip" title="{t}Uau, que gatx! :P{/t}">
			{profile_picture}
			<span class="label label-info" style="position: absolute; bottom: 0px; left: 33.33px;">
				<span class="glyphicon glyphicon-hand-up"></span>
				{t}It's you!{/t}
			</span>
		</a>
		<h3 class="hidden-xs col-sm-7 col-md-8">
			{t}Olá, {/t}{$current_user_name}
		</h3>
		<h4 class="visible-xs col-xs-7">
			{t}Oi, {/t}{$current_user_name}
		</h4>
		<div class="clear clearfix"></div>
	</div>
	<div class="clear clearfix"></div>
</header>
