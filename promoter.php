<?php
use armazemapp\Locale;
use armazemapp\FacebookAdapter;
use Event\Model\EventTable;
$loader = require 'vendor/autoload.php';

$locale = new Locale('pt_BR', 'armazemapp');
$facebook = new FacebookAdapter();
$current_user = $facebook->getUser();

$body_classes = 'promoter-area';
$event = new EventTable();

include_once 'views/head.phtml';
?>
<div id="wrap" class="container">
<header class="page-header">
	<h1>Espa&ccedil;o do Promoter</h1>
	<p>Bem-vind@! Se quiser adicionar algu&eacute;m a alguma Lista VIP,
	comece digitando o seu nome na caixa de busca acima.</p>
</header>
<?php
echo '<main>';
?>
<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 row">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h2 class="panel-title">Eventos com Lista VIP</h2>
		</div>
		<ul class="list-group">
<?php
foreach ( $event->fetchAll() as $viplistBenefit ) {
?>
<li class="list-group-item">
	<?php var_dump($viplistBenefit); ?>
</li>
<?php
}
?>
		</ul>
	</div>
</div>
<?php
echo '</main>';
?>
</div>
<?php
include_once 'views/navbar.phtml';
include_once 'views/foot.phtml';
