<?php

use armazemapp\Locale;

use armazemapp\FacebookAdapter;

$loader = require 'vendor/autoload.php';

$locale = new Locale('pt_BR', 'armazemapp');

$facebook = new FacebookAdapter();

$user = $facebook->getUser();

if(isset($_GET['code']) && isset($_GET['state'])) {

	header("Location: index.php");

}

$body_classes = 'login';

if(isset($_GET)) {

	if (isset($_GET['error_code']) || isset($_GET['error_message'])) {

		$error_code = isset($_GET['error_code']) ? intval($_GET['error_code']) : '';

		$error_message = isset($_GET['error_message']) ? $_GET['error_message'] : '';

		error_log($error_code, E_USER_ERROR);

		error_log($error_message, E_USER_ERROR);

	}

}

include_once 'views/head.phtml';

?>

<div id="login-box" class="col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4 col-lg-4 col-lg-offset-4">

	<?php if(!$error_code || $error_code==2 || $error_code==200) { ?>

	<?php if($error_code==2 || $error_code==200) { ?>

	<h3><?php echo _('Unable to connect you to') ?>&nbsp;Armaz&eacute;m :(</h3>

	<h4><?php echo _('Please try once again'); ?></h4>

	<?php } else { ?>

	<h3>Conecte-se ao Armaz&eacute;m do Ros&aacute;rio</h3>

	<?php } ?>

	<img alt="Armaz&eacute;m do Ros&aacute;" src="//fbcdn-photos-b-a.akamaihd.net/hphotos-ak-prn1/t39.2081-0/p128x128/851590_240718916089300_1733288286_n.png">

	<?php
    	$parsed_url = parse_url($facebook->login_url);
		$query_url = $parsed_url['query'];
		$query_array = explode('&', $query_url);
	?>
	<form class="fb-login-button" method="get" action="https://<?php echo $parsed_url['host'] . $parsed_url['path']; ?>" target="_top">
    	<?php foreach ($query_array as $query) {
			$q = explode('=', $query);			
		?>
    	<input id="<?php echo $q[0]; ?>" name="<?php echo htmlentities(urldecode($q[0])); ?>" type="hidden" value="<?php echo htmlentities(urldecode($q[1])); ?>" />
        <?php } ?>
		<button
			class="fb_button btn btn-lg"
            onClick="_gaq.push(['_trackEvent', 'login', 'click', 'fb_button']);"
            id="submit"
            name="submit"
			type="submit">
				<span class="facebook-logo"></span>
				<span class="login-text"><?php echo _('Login with Facebook'); ?></span>
		</button>
	</form>

	<p>
	<?php echo _('This app never will post anything without your permission.'); ?>
    </p>

	<?php } else { ?>

		<?php

switch($error_code) {

	case 901: case 1349126:

?>

	<h3><?php echo _('Oops!'); ?></h3>

	<p><?php echo _('This app is for maintenance. Please check back again later.') ?></p>

	<img alt="Armaz&eacute;m do Ros&aacute;" src="//fbcdn-photos-b-a.akamaihd.net/hphotos-ak-prn1/t39.2081-0/p128x128/851590_240718916089300_1733288286_n.png">

	<p><a href="https://app.armazemdorosario.com.br" target="_top" onClick="_gaq.push(['_trackEvent', 'back', 'click', 'from_error']);"><?php echo _('Back'); ?></a></p>

<?php

	}

}

?>
</div>
<div class="clearfix"></div>
<form class="container" action="https://apps.facebook.com/armazemdorosario/privacy.php" target="_top">
	<p class="text-center">
		<button class="btn btn-link">Privacidade</button>
    </p>
</form>
<?php

include_once 'views/foot.phtml';