<div class="clearfix"></div>
<main role="main" style="margin-top: 1em;">
	<div class="row">
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
		<div class="col-sm-6 col-lg-4">
			<div class="panel panel-default">
				<div class="panel-heading">
	    			<h2 class="panel-title">{t}Listas VIP{/t}</h2>
	    		</div>
					{if isset($all_active_vip_lists)}
	        	<div class="list-group">
					{foreach from=$all_active_vip_lists item=benefit}
						{include file='benefit_viplist_item.tpl'}
					{foreachelse}
						<div class="alert">
							<h4>{t}Nenhuma lista VIP por enquanto :({/t}</h4>
							<p>{t}Por favor, volte novamente em breve{/t}</p>
						</div>
					{/foreach}
	        	</div>
						{else}
						<div class="alert alert-danger">Houve algum problema ao mostrar as Listas VIP :/</div>
						{/if}
	        	<div class="panel-footer">
	        	</div>
	        </div>
		</div>
		<!--
		<div class="col-sm-12 col-lg-4">
			{t}Users crush will come back someday... or not{/t}
		</div>
		-->
		<div class="clearfix"></div>
	</div>
	<div class="clearfix"></div>
</main>
