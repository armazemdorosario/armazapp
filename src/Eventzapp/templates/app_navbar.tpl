<nav id="admin-navbar" class="navbar navbar-fixed-top navbar-inverse" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse"
				data-target="#bs-example-navbar-collapse-1"
				onClick="_gaq.push(['_trackEvent', 'admin_navbar', 'toggle', 'button']);">
				<span class="sr-only">{t}Menu{/t}</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="{$app_url}" itemprop="url" onClick="_gaq.push(['_trackEvent', 'navbar', 'click', 'brand_logo']);"
				title="{$app_name}">
				<img alt="Armazapp"
				src="{$app_url}{$html_meta.image}"
				height="24" width="24" />
			</a>
		</div><!-- /.navbar-header -->
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<!-- admin link -->
			<!-- promoter link -->
			<!-- search -->
			<div class="navbar-right">
				<ul class="nav navbar-nav">
					{if $current_user_id != ''}
					<li>
						<a href="{$app_url}/?userfbid={$current_user_id}" onClick="_gaq.push(['_trackEvent', 'you', 'link_click', '{$current_user_id}']);" title="{t}See your app history{/t}" data-toggle="tooltip" data-placement="bottom">
							<span class="pull-left">{profile_picture size=24}</span>&nbsp;{$current_user_firstname}
						</a>
					</li>
					<!--
					<li>
						<a href="{$app_url}/privacy.php" target="_top">{t}Privacidade{/t}</a>
					</li>
					-->
					{/if}
				</ul>
				<form action="{$app_url}/{$app_routes.logout}" class="nav navbar-form navbar-right" method="get" target="_top">
					<button class="btn btn-danger" type="submit" title="{t}Logout from this app{/t}" onClick="_gaq.push(['_trackEvent', 'logout', 'submit', 'button']);">{t}Sair{/t}</button>
				</form>
			</div>
		</div>
	</div>
</nav>