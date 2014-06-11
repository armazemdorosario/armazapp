<?php
use armazemapp\Locale;
use armazemapp\FacebookAdapter;
$loader = require 'vendor/autoload.php';

$locale = new Locale('pt_BR', 'armazemapp');
$facebook = new FacebookAdapter();
$currentUser = $facebook->getUser();

if( !$facebook->userIsPromoter() && !$facebook->userIsAdministrator() ) {
	header("Location: /index.php?accessdeniedfrom=promoters");
}

$bodyClasses = 'promoter-area';
$promotersBenefits = $facebook->getCurrentBenefits(1, 3);

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
<div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h2 class="panel-title">Eventos com Lista VIP</h2>
		</div>
		<ul class="list-group">
<?php
foreach ( $promotersBenefits as $benefit ) {
?>
<li class="list-group-item">
	<div class="col-sm-3 thumbnail">
		<img class="img-responsive" src="<?php echo $benefit['photo']; ?>" />
		ID do evento: <?php echo $benefit['eventfbid']; ?>
	</div>
	<div class="col-sm-9">
	<div class="alert alert-info">Esta lista comporta até <?php echo $benefit['max_num_people_chosen']; ?> <?php echo $facebook->g('mulheres', 'homens', 'homens <strong>e</strong> mulheres', $benefit['accepted_gender']); ?>.</div>
	<table class="table table-hover table-condensed table-striped">
		<thead>
			<tr>
				<th>Promoter</th>
				<th>Pessoas nesta lista</th>
			</tr>
		</thead>
		<tbody>
	<?php
	$promoters_stats = $facebook->getPromotersStats($benefit['eventfbid'], true);
	foreach($promoters_stats as $stat) {
	?>
	<tr>
		<td><?php $facebook->getUserPicture($stat['chosen_by_fbid'], $stat['chosen_by_fbname'], 24, 'pull-left'); ?>&nbsp;<?php echo $stat['chosen_by_fbname']; ?></td>
	<td>
	<details>
		<summary>
			<span class="badge"><?php echo $stat['count']; ?></span> <?php echo $facebook->g('mulheres', 'homens', 'pessoas', $stat['fbgender']); ?></summary>
			<ul class="list-unstyled well">
				<?php foreach ($facebook->getBenefitUsersByPromoter($stat['chosen_by_fbid'], $benefit['eventfbid'], $stat['fbgender']) as $user) { ?>
					<li><a href="/admin.php?userfbid=<?php echo htmlentities($user['fbid']); ?>" target="_blank">
						<?php $facebook->getUserPicture($user['fbid'], $user['fbname'], 16, 'pull-left'); ?>&nbsp;<?php echo htmlentities($user['fbname']); ?>
						<span class="clearfix"></span>
					</a></li>
				<?php } ?>
			</ul>
	</details>
	</td>
	</tr>
	<?php
	}
	?>
		</tbody>
	</table>
	</div>
	<div class="clearfix"></div>
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
<h1 class="h3">Seu desempenho</h1>
<p>Na tabela abaixo, você pode acompanhar quantas pessoas já adicionou em eventos passados.</p>
<table class="table table-condensed table-hover table-striped">
<thead>
<tr>
<th>Evento</th><th>Pessoas adicionadas</th>
</tr>
</thead>
<tbody>
<?php
foreach ($facebook->getPromoterStats() as $stat) {
?>
<tr><td><?php echo htmlentities($stat['eventfbid']); ?></td><td><?php echo htmlentities($stat['count']); ?></td></tr>
<?php
}
?>
</tbody>
</table>
</div>
<?php
include_once 'views/navbar.phtml';
include_once 'views/foot.phtml';
