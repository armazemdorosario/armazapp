<?php
$loader = require 'vendor/autoload.php';

use armazemapp\Locale;
use armazemapp\FacebookAdapter;
use armazemapp\Mail;
use armazemapp\Router;

$locale = new Locale('pt_BR', 'armazemapp');
$facebook = new FacebookAdapter();
$user = $facebook->getUser();
$current_user = $facebook->getUser();
$router = new Router();

if(isset($_POST)) {
	$ticket_sweepstakes_post = isset($_POST['eventfbid']) && isset($_POST['claimBenefit']) && !empty($_POST['eventfbid']);
	$vip_list_post = isset($_POST['eventfbid']) && isset($_POST['attendToEventSubmit']) && !empty($_POST['eventfbid']);
	$user_signup = isset($_POST['nome']) && isset($_POST['identidade']) && isset($_POST['signupUserSubmit']) && !empty($_POST['nome']) && !empty($_POST['identidade']);


	if(isset($_POST['grantWritePermissions'])) {
		$facebook->grantPermission('publish_stream, rsvp_event');
	}
	if($user_signup) {
		$signupData = array(
				'fbid' => trim($current_user['id']),
				'name' => trim($_POST['nome']),
				'identidade' => trim($_POST['identidade']),
				'fbname' => trim($current_user['name']),
				'fbgender' => trim($current_user['gender']),
		);
		
		$trust_level = $facebook->calculateUserTrustLevel($signupData['name'], $signupData['fbname']);
			
		if(!$facebook->userAppearsToBeFake($trust_level) && strpos($signupData['name'], ' ') !== false) {
			$facebook->signupUser($signupData);
			header('Location: /?msg=justSignedUp');
		}
	}


	/* Inicio Sorteio de Ingressos */
	if($ticket_sweepstakes_post) {
		$eventname = isset($_POST['eventname']) ? 'do evento "'.trim($_POST['eventname']).'"' : 'de um evento';
		$defaultpicture = 'http://armazemdorosario.com.br/apple-touch-icon-144x144-precomposed.png';
		$eventpicture = isset($_POST['photo']) ? trim($_POST['photo']) : $defaultpicture;
		$data = array(
				'link' => 'http://app.armazemdorosario.com.br/?utm_source=facebook&utm_medium=app_publish_stream&utm_campaign=sorteio-'.trim($_POST['eventname']),
				'message' => 'Estou concorrendo ao sorteio de ' . ($_POST['max_num_people_chosen'] > 1 ? $_POST['plural_name'] : 'um ' . $_POST['objectname']) . ' ' . $eventname . ' usando o Armazapp. Participe você também! https://app.armazemdorosario.com.br/',
				'picture' => $eventpicture,
				'name' => 'Concorra a ' . ($_POST['max_num_people_chosen'] > 1 ? $_POST['plural_name'] : 'um ' . $_POST['objectname']) . ' ' . $eventname,
				'description' => 'Com o Armazapp, você pode participar de listas VIP, concorrer a ingressos e outros benefícios. Experimente e conte pra galera!',
				'actions' => array(
						'name'=>'Ver o regulamento',
						'link' => 'https://apps.facebook.com/armazemdorosario/regulamento.php?eventfbid='.$_POST['eventfbid'].'&benefit_type=2&utm_source=facebook&utm_medium=app_publish_stream&utm_campaign=sorteio-'.$_POST['eventfbid'],

				),
		);
		if($facebook->checkPermission('publish_stream')) {
			$post_id = $facebook->postOnTimeline($data);
		}
		else {
			error_log('Sorteio: um usuário tentou postar na sua linha do tempo a respeito '.$eventname.', mas não deu permissão para isso.');
		}
		if($facebook->checkPermission('rsvp_event')) {
			$claim_status = $facebook->competeToTicketSweepstakes($_POST['eventfbid']); 
		}
		else {
			error_log('Sorteio: um usuário tentou participar '.$eventname.' , mas não deu permissão para isso.');
		}		

		$notification_data = array(

			'template' => 'Você está participando do sorteio de ingressos ' .$eventname,

			'href' => '?utm_source=facebook&utm_medium=ticket_sweepstakes_notification',

		);

		$facebook->sendNotification($notification_data);
		$message = 'Você está concorrendo a ' . ($_POST['max_num_people_chosen'] > 1 ? $_POST['plural_name'] : 'um ' . $_POST['objectname']) . '. Não se esqueça de ler o <a href="http://app.armazemdorosario.com.br/regulamento.php?eventfbid=' . $_POST['eventfbid'] . '&benefit_type=2" target="_blank">regulamento</a>.';
		$message .= 'Avisaremos a você por e-mail e pelo Facebook se você ganhar.';
		$email = new Mail($current_user['email'], $notification_data['template'], $message);
		$email->send();

	}
	/* Fim sorteio de ingressos */


	if($vip_list_post) {
		$private = ($_POST['private']=='on');
		$eventname = isset($_POST['eventname']) ? 'do evento '.trim($_POST['eventname']) : 'de um evento';


		if($private) {
			error_log('1 usuário entrou escondido na Lista VIP');
		}


		else {
			$defaultpicture = 'http://armazemdorosario.com.br/apple-touch-icon-144x144-precomposed.png';
			$eventpicture = isset($_POST['photo']) ? trim($_POST['photo']) : $defaultpicture;
			$data =  array(
				'link' => 'https://www.facebook.com/ArmazemDoRosario/app_208195102528120',
				'message' => 'Entrei para a Lista VIP '.$eventname.' usando o novo aplicativo do Armazém do Rosário!',
				'picture' => $eventpicture,
				'name' => 'App do Armazém do Rosário para Facebook',
				'description' => 'Com o aplicativo do Armazém do Rosário para Facebook, você pode participar de listas VIP e confirmar presença em eventos da casa. Experimente e conte pra galera!',
				'actions' => array(
						'name'=>'Ver o regulamento',
						'link' => 'https://apps.facebook.com/armazemdorosario/regulamento.php?eventfbid='.$_POST['eventfbid'].'&benefit_type=1&utm_source=facebook&utm_medium=app_publish_stream&utm_campaign=lista-vip-'.$_POST['eventfbid'],
				),
);
			$post_id = $facebook->postOnTimeline($data);
			$event_attend = $facebook->attendOnFacebook(trim($_POST['eventfbid']));
		} // end if
		
		$attend_status = $facebook->attendToEvent($_POST['eventfbid'], $private);
		
		$notification_data = array(
				'template' => 'Você está participando da Lista VIP ' .$eventname,
				'href' => '?utm_source=facebook&utm_medium=ticket_viplist_notification',
		);
		$facebook->sendNotification($notification_data);
		
		$message = 'Você está participando desta Lista VIP. Não se esqueça de ler o <a href="http://app.armazemdorosario.com.br/regulamento.php?eventfbid=' . $_POST['eventfbid'] . '&benefit_type=1" target="_blank">regulamento</a>.';
		
		$email = new Mail($current_user['email'], $notification_data['template'], $message);
		$email->send();
		
	} // end if
} // end if
include_once 'views/head.phtml';
include 'views/navbar.phtml';
include 'views/header.phtml';
if(isset($_GET['msg']) && $_GET['msg'] == 'justSignedUp') {
?>
<div class="alert alert-success">
	<h2 class="h3"><?php echo _('Your registration is ready!'); ?></h2>
	<p><?php echo _('Check now events with VIP list'); ?> e sorteios</p>
</div>
<?php }
	if($facebook->userSignedUp()) {
		
		if(isset($_GET['userfbid']) && $_GET['userfbid'] == $current_user['id']) {
			$userfbid = $current_user['id'];
			$user_events = $facebook->getUserEvents($userfbid);
			$user = $current_user;
			$user['fbid'] = $userfbid;
			include_once('views/show-user-info.phtml');
		}
		else {

if(!$facebook->checkPermission('publish_stream') || !$facebook->checkPermission('rsvp_event')) {
	?>
<div class="alert alert-warning jumbotron">
	<form class="container" method="post" target="_top">
	    <h2>Aten&ccedil;&atilde;o!</h2>
	    <p>Para participar dos benef&iacute;cios como Sorteio e Lista VIP,<br />voc&eacute; precisa permitir acesso &agrave; sua linha do tempo:</p>
	    <p><button class="btn btn-lg btn-primary" type="submit" id="grantWritePermissions" name="grantWritePermissions" onClick="_gaq.push(['_trackEvent', 'grant_write_permissions', 'submit']);">Permitir</button></p>
	</form>
</div>
<?php
}
			echo '<div class="clearfix"></div><main>';
?>

<div class="row">
    <div class="col-sm-6">
    	<div class="panel panel-default">
    		<div class="panel-heading">
	    		<h2 class="panel-title">Sorteios</h2>
	    	</div>
	        <div class="list-group">
			<?php include_once 'views/listagem-de-sorteios.phtml'; ?>
	        </div>
	        <div class="panel-footer">
	        </div>
        </div>
        <?php include_once 'views/crush-panel.phtml'; ?>
	</div>
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-heading">
    			<h2 class="panel-title">Listas VIP</h2>
    		</div>
        	<div class="list-group">
				<?php include_once 'views/listagem-de-eventos.phtml'; ?>
        	</div>
        	<div class="panel-footer">
        	</div>
        </div>
	</div>
</div>
<div class="clearfix"></div>
<?php	
			echo '</main>';
		}
	}
	else {
		include_once 'views/cadastro-usuario.phtml';
	}
include 'views/foot.phtml';