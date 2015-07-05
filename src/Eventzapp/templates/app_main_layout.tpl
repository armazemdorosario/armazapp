<div class="clearfix"></div>
<main role="main" style="margin-top: 1em;">
		<!--
	    <div class="col-sm-6 col-lg-4">
	    	<div class="panel panel-default">
	    		<div class="panel-heading">
		    		<h2 class="panel-title">{t}Sorteios{/t}</h2>
		    	</div>
		        <div class="list-group">
					{t}Nenhum sorteio por enquanto{/t}
		        </div>
		        <div class="panel-footer">
		        </div>
	        </div>
		</div>
	-->
			{if isset($all_active_vip_lists)}
				<ul class="list-unstyled" id="container">
			{foreach from=$all_active_vip_lists item=benefit}
				<li class="item col-xs-12 col-sm-6 col-md-4 col-lg-4">
				{include file='benefit_viplist_item.tpl'}
				<hr />
				</li>
			{foreachelse}
					<li class="alert alert-warning">
						<h4>{t}Nenhuma lista VIP por enquanto :({/t}</h4>
						<p>{t}Por favor, volte novamente em breve{/t}</p>
					</li>
			{/foreach}
				</ul>
				{else}
				<div class="alert alert-danger">Houve algum problema ao mostrar as Listas VIP :/</div>
			{/if}
		<!--
		<div class="col-sm-12 col-lg-4">
			{t}Users crush will come back someday... or not{/t}
		</div>
		-->
		<div class="clearfix"></div>
</main>
