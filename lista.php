<?php
$loader = require 'vendor/autoload.php';
use armazemapp\Locale;
use armazemapp\FacebookAdapter;
if (!isset($_GET['eventfbid']) || empty($_GET['eventfbid'])) {
	header("Location: index.php");
}
$locale = new Locale('pt_BR', 'armazemapp');
$facebook = new FacebookAdapter();
$current_user = $facebook->getUser();
$event = $facebook->getEventInfo($_GET['eventfbid']);
$fbevent = &$event;
$results_benefit = $facebook->getBenefitInfo($_GET['eventfbid'], $_GET['benefit']);
$benefit = $results_benefit[0];
include_once 'views/head.phtml';
if ($facebook->isUserOver18()) {
	include 'views/navbar.phtml';
}
?>
<div class="container">
	<div class="col-xs-12 col-sm-8 col-md-6">
		<?php include 'views/benefit-header.phtml'; ?>
	</div>
	<div class="col-xs-12 col-sm-4 col-md-6">
		<p>
        	<a onClick="_gaq.push(['_trackEvent', 'back', 'click', 'to_homepage']);" class="btn btn-default btn-lg" href="index.php" title="<?php echo _('Back to homepage'); ?>">
				<span class="glyphicon glyphicon-chevron-left"></span>&nbsp;<?php echo _('Back'); ?>
			</a>
		</p>
	</div>
	<div class="clearfix"></div>
<?php
echo '<main>';
$user_is_admin = $facebook->userIsAdmin();
$event_attendees = $facebook->getEventAttendees($_GET['eventfbid'], $_GET['benefit']);

if(is_null($event_attendees)) {
?>
	<div class="alert alert-info">
		<p>Ninguém entrou na lista ainda</p>
	</div>
<?php
}
?>
<ul class="list-inline has-crush">
<?php

$previous_user_id = null;

foreach ($event_attendees as $attendee) {
	if($previous_user_id != null && $previous_user_id == $attendee['userfbid']) {
	}
	else {
	if($attendee['private']!='1' || $user_is_admin || $attendee['userfbid'] == $current_user['id']) {
		$previous_user_id = $attendee['userfbid'];
?>
	<li class="col-xs-6 col-sm-2">
		<a onClick="_gaq.push(['_trackEvent', 'attendee', 'click_image', '<?php echo($attendee['userfbid']); ?>']);" id="attendee-<?php echo $attendee['userfbid']; ?>" class="img-responsive attendee-gender-<?php echo $attendee['fbgender']; ?>" href="<?php echo $user_is_admin ? '/admin.php?userfbid='.$attendee['userfbid'] : $attendee['userfbid']; ?>" rel="external" target="_blank" data-placement="bottom" data-toggle="tooltip" title="<?php echo $attendee['fbname']; ?>" style="position: relative;">
			<span class="clear clearfix"></span>
			<?php $facebook->getUserPicture($attendee['userfbid'], $attendee['fbname'], 190, $attendee['chosen'] ? 'success' : ''); ?>

<?php if ($user_is_admin && $attendee['private']=='1') { ?>
	        <span class="label label-info" style="position: absolute; bottom: 0px; right: 0px;"><span class="glyphicon glyphicon-lock"></span> Privado</span>
<?php } ?>

<?php if($attendee['chosen'] == '1') { ?>
			<span class="label label-success" style="position: absolute; bottom: 20px; right: 0px;">
				<span class="glyphicon glyphicon-star"></span>&nbsp;Ganhou!
			</span>
<?php } ?>

<?php if($attendee['userfbid'] == $current_user['id']) { ?>
	<?php if ($attendee['private']=='1') { ?>
			<span class="label label-info" style="position: absolute; bottom: 0px; right: 0px;">
				<span class="glyphicon glyphicon glyphicon-lock"></span>&nbsp;<?php echo _("It's you. Secret."); ?>
            </span>
	<?php } else { ?>
            <span class="label label-info" style="position: absolute; bottom: 0px; right: 0px;">
                <span class="glyphicon glyphicon-hand-up"></span>&nbsp;<?php echo _("It's you!"); ?>
            </span>
			<?php } ?>
		<?php } ?>
			<span class="clear clearfix"></span>
		</a>
        <?php if($attendee['userfbid'] != $current_user['id']) { ?>
        <ul id="crush" class="list-inline">
        	<li><a data-status="2" data-userfbid="<?php echo $attendee['userfbid']; ?>" href="#attendee-<?php echo $attendee['userfbid']; ?>/to-afim" title="Tô Afim" data-toggle="tooltip" data-placement="bottom" class="crush-2"><span class="sr-only">Tô Afim</span></a></li>
            <li><a data-status="1" data-userfbid="<?php echo $attendee['userfbid']; ?>" href="#attendee-<?php echo $attendee['userfbid']; ?>/amizade" title="Amizade" data-toggle="tooltip" data-placement="bottom" class="crush-1"><span class="sr-only">Amizade</span></a></li>
            <li><a data-status="0" data-userfbid="<?php echo $attendee['userfbid']; ?>" href="#attendee-<?php echo $attendee['userfbid']; ?>/nem-pensar" title="Nem Pensar" data-toggle="tooltip" data-placement="bottom" class="crush-0"><span class="sr-only">Nem Pensar</span></a></li>
        </ul>
        <div class="clearfix"></div>
        <div class="crush-current-status" id="crush-current-status-<?php echo $attendee['userfbid']; ?>">
       		
        </div>
        <?php } ?>
	</li>
<?php
	} // end if
	}
} // end for
?>
<li class="clearfix"></li>
</ul>
<div class="clear clearfix"></div>
</div>
<hr />
<?php echo '</main>'; ?>
<script type="text/javascript">
<!--
$('.has-crush #crush li a').click(function(e) {

	var current_element = $(this);
	var target_userfbid_value = $(this).data('userfbid');

	var xhr_data = {
		type: "POST",
		url: "/ajax-crush.php",
		cache: false,
		data: {
			target_userfbid: target_userfbid_value,
			status: $(this).data('status')
		}
	};
	console.log('Carregando...');
	$.ajax(xhr_data)
		.done(function(msg) {
			$('#crush-current-status-'+target_userfbid_value).html(current_element.parent().html());
		})
		.fail(function(msg) {
			
		})
		.always(function(msg) {
			console.log(msg);
		});
});

<?php foreach($facebook->getUserCrushes() as $crush) { ?>
$('#crush-current-status-<?php echo $crush['target_userfbid']; ?>').html('<a data-status="<?php echo $crush['status']; ?>" data-userfbid="<?php echo $crush['target_userfbid']; ?>" href="#attendee-<?php echo $crush['target_userfbid']; ?>/<?php echo $crush['status']; ?>" class="crush-<?php echo $crush['status']; ?>"></a>');
<?php } ?>
-->
</script>
<?php
include_once 'views/foot.phtml';