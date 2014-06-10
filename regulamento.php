<?php
$loader = require 'vendor/autoload.php';

use armazemapp\Locale;
use armazemapp\FacebookAdapter;

$locale = new Locale('pt_BR', 'armazemapp');
$facebook = new FacebookAdapter();

$event_array = $facebook->getBenefitInfo(trim($_GET['eventfbid']), trim($_GET['benefit_type']));
$event = $event_array[0];
$benefit = $event['benefit_type'];
$fbevent = $facebook->getEventInfo(trim($_GET['eventfbid']));

$labels = array();
$labels['1'] = array(
	'label_object' => 'A Lista VIP que dá direito a',
	'in' => 'na Lista VIP',
	'will_be_closed' => 'A Lista VIP será fechada',
	'is_promoted' => 'Esta Lista VIP é promovida',
	'chosen_person_adjective' => 'inserida na Lista VIP',
	'of' => 'da Lista VIP',
);
$labels['2'] = array(
	'label_object' => 'O sorteio de',
	'in' => 'no sorteio',
	'will_be_closed' => 'O sorteio será realizado',
	'this_is_promoted' => 'Este sorteio é promovido',
	'chosen_person_adjective' => 'sorteada',
	'of' => 'do sorteio',
);

$homelink = '<a href="http://armazemdorosario.com.br/" rel="external" target="_blank" onClick="_gaq.push([\'_trackEvent\', \'external\', \'click\', \'home_link\']);">Armazém do Rosário Art Music</a>';
$applink = '<a href="http://app.armazemdorosario.com.br/" onClick="_gaq.push([\'_trackEvent\', \'external\', \'click\', \'app_link\']);">Armazapp (o aplicativo do Armazém do Rosário)</a>';
$eventlink = '<a href="http://facebook.com/'.htmlentities($event['fbid']).'" rel="external" target="_blank" onClick="_gaq.push([\'_trackEvent\', \'external\', \'click\', \'event_.'.$fbevent['name'].'._link\']);">'.$fbevent['name'].'</a>';

include_once 'views/head.phtml';
?>
<article class="container">
	<header class="page-header">
    <h1>Regulamento <?= $labels[$benefit]['of']; ?> de <?= $event['objectname']; ?> - <?=$eventlink?></h1>
    </header>
	<p><?= $labels[$benefit]['label_object']; ?> <?php echo $event['max_num_people_chosen'] ?> unidade(s) do <?php echo $event['objectname'] ?> relativo ao evento <?=$eventlink?> é destinado a pessoas cadastradas no <?=$applink?> que acionarem o botão relativo a participação <?= $labels[$benefit]['in']; ?> e frequentarem o evento <?=$eventlink?>, que acontece no <?=$homelink?>, à <?=$fbevent['venue']['street']?>, em <?=$fbevent['venue']['city']?> <?php if(isset($fbevent['end_time'])) { ?>entre <?php echo date('d\\/m H\\:i', strtotime($fbevent['start_time'])); ?> e <?php echo date('d\\/m H\\:i', strtotime($fbevent['end_time'])); ?><?php } else { ?>em <?php echo date('d\\/m H\\:i', strtotime($fbevent['start_time'])); ?><?php } ?>. <?= $labels[$benefit]['will_be_closed']; ?> em <?php echo date('d\\/m H\\:i', strtotime($event['expiration_date'])); ?>.</p>
	<ol>
    	<li><?= $labels[$benefit]['this_is_promoted']; ?> pelo <?=$homelink?>, e tem por objetivo incentivar a participação no evento <?=$eventlink?>.
        	<ol>
            	<li>Fica desde já consignado que a pessoa cadastrada e que foi <?= $labels[$benefit]['chosen_person_adjective']; ?>, deverá comparecer/ter comparecido ao <?=$homelink?> <?php if(isset($fbevent['end_time'])) { ?>entre <?php echo date('d\\/m H\\:i', strtotime($fbevent['start_time'])); ?> e <?php echo date('d\\/m H\\:i', strtotime($fbevent['end_time'])); ?><?php } else { ?>em <?php echo date('d\\/m H\\:i', strtotime($fbevent['start_time'])); ?><?php } ?>. <?= $labels[$benefit]['will_be_closed']; ?> em <?php echo date('d\\/m H\\:i', strtotime($event['expiration_date'])) ?>, tendo sua entrada no local comprovada por sistema próprio.</li>
                <li>Fica desde já consignado que a pessoa que for <?= $labels[$benefit]['chosen_person_adjective']; ?> deverá comprovar a veracidade de seus dados através de apresentação de documento oficial de identidade com foto no momento do resgate do <?=$event['objectname'];?>.</li>
                <?php if($event['benefit_type']==2) { ?>
                <li>Em caso de não atendimento de um dos requisitos mencionados nos itens anteriores, o <?=$homelink?> se reserva ao direito de gerar um novo sorteio.</li>
                <?php } ?>
			</ol>
        </li>
        <li>Para participar <?= $labels[$benefit]['of']; ?>, a pessoa deverá possuir ou efetuar cadastro no <?=$applink?> e acionar o botão de participação relativo ao mesmo. O mero cadastro realizado no aplicativo não é aceito como participação. O mero cadastro no local físico do <?= $homelink ?> também não corresponde ao cadastro realizado no aplicativo.</li>
        <?php if($event['benefit_type']==2) { ?>
        <li>O sorteio será realizado por um ou mais representantes do <?=$homelink?> com apoio técnico da <a href="http://ultracomunica.com.br/" rel="external" target="_blank" onClick="_gaq.push(['_trackEvent', 'external', 'click', 'ultra_link']);">Ultra Comunicação</a>.</li>
        <li>O <?php echo $event['objectname'] ?> deverá ser retirado no <?= $homelink ?> - <?=$fbevent['venue']['street']?> - <?=$fbevent['venue']['city']?>, na data e horário que serão divulgados à pessoa <?= $labels[$benefit]['chosen_person_adjective']; ?>.</li>
        <?php } ?>
        <li>O <?php echo $event['objectname'] ?> só será entregue à pessoa <?= $labels[$benefit]['chosen_person_adjective']; ?>, mediante apresentação de documentos pessoais com foto (RG e CPF). Não terá validade documento que apresentar rasuras, adulterações ou emendas que impossibilitem a identificação de sua autenticidade.</li>
        <li>A forma <?= $labels[$benefit]['of']; ?> considerará apenas os cadastros efetuados que acionarem o botão de participação do mesmo. <?php if($event['benefit_type']==2) { ?>Para o sorteio, será gerada uma lista randômica através de consulta especial a banco de dados, fornecida pelo próprio sistema de gestão do <?=$applink?>, alocado no mesmo servidor de hospedagem do website do <?=$homelink?><?php } ?></li>
        <li>O <?=$homelink?> não se responsabilizará por eventuais defeitos ou problemas apresentados pelo <?php echo $event['objectdescription']; ?>, devendo sua troca ou reparo serem feitos na empresa onde o mesmo foi adquirido: <?php echo $event['provided_by']; ?>.</li>
        <li>Ao participar <?= $labels[$benefit]['of']; ?>, você declara concordar que sua imagem e/ou nome poderão ser utilizados pelo <?=$homelink?> para divulgação do mesmo, sem que haja a necessidade de qualquer remuneração ou aviso.</li>
        <?php if($event['benefit_type']==2) { ?>
        <li>O sorteio ocorrerá independente de um número mínimo ou máximo de cadastros durante o período.</li>
      	<?php } ?>
        <li>O <?=$homelink?> não se responsabiliza por eventuais falhas de energia ou de conexão à Internet, ou mesmo problemas oriundos do serviço de hospedagem, roteamento e/ou DNS’s que venham a impedir a participação de qualquer pessoa <?= $labels[$benefit]['in']; ?> ou a geração do resultado no horário marcado, podendo haver atrasos na emissão do mesmo.</li>
        <li>Se a pessoa <?= $labels[$benefit]['chosen_person_adjective']; ?> for juridicamente incapaz, será impossibilitada de receber o <?php echo $event['objectname'] ?>, considerando principalmente as restrições de idade. Como já é divulgado frequentemente pela casa, apenas pessoas com idade equivalente ou maior a 18 anos podem ingressar no Armazém do Rosário, utilizar o <?=$applink ?> e, portanto, participar de qualquer outro tipo de benefício fornecido por este aplicativo.</li>
        <li>As dúvidas e controvérsias oriundas <?= $labels[$benefit]['of']; ?> devem ser submetidas ao Setor Jurídico do <a href="http://grupoagito.com/" rel="external" target="_blank" onClick="_gaq.push(['_trackEvent', 'external', 'click', 'ga_link']);">Grupo Agito</a> - grupo que controla as atividades comerciais do <?=$homelink?> - através do telefone (38) 3531-3344 ou pelo e-mail juridico<abbr title="arroba">@</abbr><a href="http://grupoagito.com/" rel="external" target="_blank" onClick="_gaq.push(['_trackEvent', 'external', 'click', 'ga_link']);">grupoagito.com</a>.</li>
        <li>Este regulamento estará disponível no site do <?=$homelink?>, através do endereço <a href="http://app.armazemdorosario.com.br/regulamento?eventfbid=<?=$_GET['eventfbid'];?>&amp;benefit_type=2"> http://app.armazemdorosario.com.br/regulamento?eventfbid=<?=$_GET['eventfbid'];?>&amp;benefit_type=2</a>.
</li>
    </ol>
    <p>Diamantina, <?php echo htmlentities($event['timestamp']); ?></p>
</article>
<?php
include_once 'views/foot.phtml';