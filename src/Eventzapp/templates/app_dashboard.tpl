<main class="container">
  <header class="page-header">
  	<h1><span class="glyphicon glyphicon-cog"></span> Painel do app</h1>
  </header>
  <div class="col-sm-4">
    <div class="panel panel-success">
      <div class="panel-heading">
        <h2 class="panel-title">Listas VIP ativas</h2>
      </div>
      <div class="panel-body">
        <ul class="list-group">
        {foreach from=$published_viplists item=benefit}
          {include file='loop_benefit.tpl'}
        {/foreach}
        </ul>
      </div>
    </div>
    <div class="panel panel-warning">
      <div class="panel-heading">
        <h2 class="panel-title">Listas VIP fechadas</h2>
      </div>
      <div class="panel-body">
        <ul class="list-group">
        {foreach from=$closed_viplists item=benefit}
          {include file='loop_benefit.tpl'}
        {foreachelse}
          Nenhuma lista VIP fechada. Verifique as listas ocultas ou publicadas.
        {/foreach}
        </ul>
      </div>
    </div>
    <div class="panel panel-danger">
      <div class="panel-heading">
        <h2 class="panel-title">Listas VIP ocultas</h2>
      </div>
      <div class="panel-body">
        Em breve.
      </div>
    </div>
  </div>
  <div class="col-sm-8">
    <iframe id="benefits-viewer" name="benefits-viewer"
    style="border: 0px; min-height: 999px; min-width: 320px; width: 100%;">
    </iframe>
  </div>
</main>
