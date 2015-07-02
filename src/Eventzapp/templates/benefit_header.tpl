<header>
	<h1 class="h4" style="margin-top: 2em;">
		<a href="{$app_url}/{$app_routes.viplist}/{$benefit->eventfbid}" onClick="_gaq.push(['_trackEvent', 'external', 'header_link_click', '{$benefit->eventfbid}']);" rel="external" title="{$benefit->name}">
			<time datetime="{$benefit->db_start_time}">{$benefit->db_start_time|date_format:"%d/%m/%Y, %H:%M"}</time>
			<strong>{$benefit->name}</strong>
		</a>
	</h1>
	<img alt="{$benefit->name}, {$benefit->db_start_time}" class="img-responsive" src="{$benefit->cdn_photo}" />
</header>
