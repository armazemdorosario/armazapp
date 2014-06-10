<?php
$loader = require 'vendor/autoload.php';
use armazemapp\FacebookAdapter;
use armazemapp\Mail;
$facebook = new FacebookAdapter();
$current_user = $facebook->getUser();
$current_user_access_level = $facebook->getUserLevel();
$current_user_is_admin = $facebook::userIsAdmin();
$current_user_is_promoter = $facebook->userIsPromoter();
// Se o usuário não for administrador ou não for promoter
if(!$current_user_is_admin && $current_user_access_level!=1) {
	header("Location: /index.php?accessDeniedFromAdmin=1");
}
$body_classes = 'admin';
include_once 'views/head.phtml';
echo '<main class="container">';
if(isset($_GET) && isset($_GET['s'])) {
	include 'views/admin-search-user.phtml';
}
if(isset($_POST) && isset($_POST['delete']) && isset($_POST['userfbid']) && !isset($_POST['eventfbid']) && $current_user_is_admin) {
	$facebook->removeUser($_POST['userfbid']);
}
if(isset($_POST) && isset($_POST['delete']) && isset($_POST['benefit']) && isset($_POST['eventfbid']) && isset($_POST['userfbid']) && $current_user_is_admin) {
	$facebook->removeBenefitFromUser($_POST['eventfbid'], $_POST['userfbid'], $_POST['benefit']);
	include 'views/message-benefit-removed-from-user.phtml';
}

if(isset($_POST) && isset($_POST['add_to_viplist'])&& isset($_POST['eventfbid']) && isset($_POST['userfbid'])) {
	$facebook->attendToEvent($_POST['eventfbid'], false, $_POST['userfbid'], $_POST['chosen_by_fbid']);
}

if(isset($_GET) && isset($_GET['eventfbid']) && isset($_GET['benefit']) && isset($_GET['set_status']) && 
!empty($_GET['eventfbid']) && !empty($_GET['benefit']) && $current_user_is_admin) {
	$facebook->changeBenefitStatus($_GET['eventfbid'], $_GET['benefit'], $_GET['set_status']);
?>
<div class="alert alert-success">
	<p class="container">O evento de ID <?php echo $_GET['eventfbid']; ?> teve seu status alterado com sucesso.</p>
</div>
<?php
}

if(isset($_GET) && isset($_GET['userfbid'])&& !isset($_POST['add_to_viplist'])) {
	$userfbid = strval(trim($_GET['userfbid']));
	$user = $facebook->getUserInfo($userfbid);
	$user = $user[0];
	$user_events = $facebook->getUserEvents($userfbid);
	include ('views/show-user-info.phtml');
}

if(isset($_POST) && isset($_POST['eventfbid']) && !isset($_POST['delete']) && !isset($_POST['add_to_viplist']) && $current_user_is_admin) {
	if(isset($_POST['userfbids']) && is_array($_POST['userfbids'])) {
		$facebook->chooseUsers($_POST['eventfbid'], $_POST['userfbids']);
		include 'views/message-users-chosen.phtml';
	}
	else {
		include 'views/admin-pre-draw.phtml';
	} // end if
} // end if
else {
	if(!isset($_GET['s']) && !isset($_GET['userfbid'])) {
?>
<h2>Listas VIP</h2>
<?php include 'views/admin-viplist.phtml'; ?>
<?php if($current_user_is_admin) { ?>
	<h2>Sorteios</h2>
	<?php include_once 'views/admin-benefits.phtml'; ?>
	<h2>Usuários com cadastro inv&aacute;lido</h2>
	<?php include_once 'views/users-panel.phtml'; ?>
<?php } // end if current user is admin ?>
<?php
	}
	
}
echo '</main>';
?>
<?php if($current_user_is_admin) { ?>
<link href="/vendor/abpetkov/switchery-master/dist/switchery.min.css" rel="stylesheet" type="text/css" />
<script data-cfasync="false" src="/vendor/abpetkov/switchery-master/dist/switchery.min.js"></script>
<script data-cfasync="false" src="/js/admin.js"></script>
<?php } // end if $current_user_is_admin
include_once 'views/navbar.phtml';
include_once 'views/foot.phtml';