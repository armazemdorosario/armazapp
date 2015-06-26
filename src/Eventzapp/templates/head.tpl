<!DOCTYPE HTML>
<html class="no-js" lang="{$html_meta.locale}" {$html_schema}>
	<head>

		<meta charset="utf-8" />
		<title>{$html_meta.title}</title>
		<meta name="title" content="{$html_meta.title}" />
		<meta name="description" content="{$html_meta.description}" />

		<!-- stylesheets -->
		<link rel="stylesheet" href="{$app_url}/bower_components/bootstrap/dist/css/bootstrap.min.css" />
		<link rel="stylesheet" href="{$app_url}/css/theme.css" />
		<!-- /stylesheets -->

		<!-- schema.org -->
		<meta itemprop="description" content="{$html_meta.description}" />
		<meta itemprop="datePublished" datetime="{$html_meta.datePublished}" />
		<!-- /schema.org -->

		<!-- app -->
{include file='head_icons.tpl'}
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />
		<meta name="format-detection" content="telephone=no" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta name="msapplication-TileColor" content="#000000" />
		<meta name="msapplication-TileImage" content="{$html_meta.tileImage}" />
		<!-- /app -->

		<!-- open graph -->
		<meta name="og:url" property="og:url" content="{$app_url}" />
		<meta name="og:title" property="og:title" content="{$html_meta.title}" />
		<meta name="og:description" property="og:description" content="{$html_meta.description}" />
		<meta name="og:type" property="og:type" content="{$html_meta.type}" />
		<meta name="og:image" property="og:image" content="{$html_meta.ogImage}" />
		<meta name="og:determiner" property="og:determiner" content="{$html_meta.determiner}" />
		<meta name="og:site_name" property="og:site_name" content="{$html_meta.title}" />
		<meta name="fb:app_id" property="fb:app_id" content="{$app_id}" />
		<!-- /open graph -->

	    <!--[if lt IE 9]>
	    <script src="{$app_url}/bower_components/html5shiv/dist/html5shiv.min.js"></script>
	    <script src="{$app_url}/bower_components/respond/dest/respond.min.js"></script>
	    <![endif]-->

		<!-- scripts -->
		<script data-cfasync="false" src="{$app_url}/js/modernizr.js"></script>

		<script data-cfasync="false" src="{$app_url}/bower_components/jquery/dist/jquery.min.js"></script>
		<script data-cfasync="false" src="{$app_url}/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
		{if $env == 'production'}
		<script type="text/javascript">
		<!--
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-42659029-6']);
		-->
	    </script>
	    <script data-cfasync="false" type="text/javascript" src="{$app_url}/js/google-analytics.js"></script>
	    {/if}
	    <!-- /scripts -->

	    <!--Start of Zopim Live Chat Script-->
		<script type="text/javascript">
		{literal}
		window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
		d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
		_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
		$.src="//v2.zopim.com/?330eFa1oInets6CBaH7d3LjEcZLQK2BZ";z.t=+new Date;$.
		type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");
		{/literal}
		</script>
		
		<!--End of Zopim Live Chat Script-->

	</head>
	<body class="{$body_classes}" id="website"><!-- id="canvas" -->
		<div id="fb-root"></div>
		<div id="wrap">
