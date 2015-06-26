		</div><!-- /#wrap -->
		<div itemprop="offers" itemscope itemtype="https://schema.org/Offer">
			<meta itemprop="price" content="{$html_meta.price}" />
			<meta itemprop="priceCurrency" content="{$html_meta.priceCurrency}" />
			<link itemprop="availability" href="https://schema.org/InStock" />
		</div>
		<script data-cfasync="false" src="{$app_url}/bower_components/jquery.lazyload/jquery.lazyload.js"></script>
		<script data-cfasync="false" src="{$app_url}/bower_components/switchery/dist/switchery.min.js"></script>
		<script data-cfasync="false" src="{$app_url}/js/typeahead.bundle.js"></script>
		<script data-cfasync="false" src="{$app_url}/js/main.js"></script>
		<script>
		{literal}$zopim(function() {{/literal}
		{if $logged_in eq "1"}
		$zopim.livechat.setName('{$current_user_name}');
		$zopim.livechat.setEmail('{$current_user_email}');
		$zopim.livechat.addTags('{$current_user_gender}');
		{/if}
		$zopim.livechat.addTags('{if $logged_in eq "1"}logged{else}not-logged{/if}');
		{literal}});{/literal}
		/*(function() {
		var s = document.createElement("script");
		s.type = "text/javascript";
		s.async = true;
		s.src = '//api.usersnap.com/load/'+
		        '737c8dd7-7447-4005-92f5-c9b857f58723.js';
		var x = document.getElementsByTagName('script')[0];
		x.parentNode.insertBefore(s, x);
		})();*/
		</script>
	</body>
</html>
