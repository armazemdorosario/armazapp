<?php

namespace armazemapp;

class Mail {
	
	protected $to;
	protected $subject;
	protected $encoded_subject;
	protected $message;
	
	protected $headers = array(
			'From: Armazapp <webmaster@app.armazemdorosario.com.br>',
			'Reply-To: Armazapp <webmaster@app.armazemdorosario.com.br>',
			'MIME-Version: 1.0',
			'Content-type: text/html; charset=utf-8',
			'Content-Transfer-Encoding: 7bit',
			'Return-Path: webmaster@app.armazemdorosario.com.br',
			'Bcc: Armazapp <webmaster@app.armazemdorosario.com.br>',
			'X-Priority: 1 (Highest)',
			'X-MSMail-Priority: High',
			'Importance: High',
		);
	
	public function header() {
		ob_start();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Armazapp</title>
		<style type="text/css">
			#outlook a {padding:0;}
			body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}
			.ExternalClass {width:100%;}
			.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
			#backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}
			img {outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;}
			a img {border:none;}
			.image_fix {display:block;}
			p {margin: 1em 0;}
			h1, h2, h3, h4, h5, h6 {color: black !important;}
			h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: black !important; text-decoration: none !important;}
			h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active { color: red !important; }
			h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited { color: purple !important; }
			table td {border-collapse: collapse;}
			a {color: blue;}
			@media only screen and (max-device-width: 480px) {
				a[href^="tel"], a[href^="sms"] { text-decoration: none; color: black; pointer-events: none; cursor: default; }
				.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] { text-decoration: default; color: blue !important; pointer-events: auto; cursor: default; }
			}
			@media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
				a[href^="tel"], a[href^="sms"] { text-decoration: none; color: blue; pointer-events: none; cursor: default; }
				.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] { text-decoration: default; color: blue !important; pointer-events: auto; cursor: default; }
			}
			@media only screen and (-webkit-min-device-pixel-ratio: 2) { /* iPhone 4 */ }
			@media only screen and (-webkit-device-pixel-ratio:.75) { /* Android low dpi */ }
			@media only screen and (-webkit-device-pixel-ratio:1) { /* Android medium dpi */ }
			@media only screen and (-webkit-device-pixel-ratio:1.5) { /* Android high dpi */ }
		</style>
		<!-- Targeting Windows Mobile -->
		<!--[if IEMobile 7]>
			<style type="text/css"></style>
		<![endif]-->
		<!--[if gte mso 9]>
			<style></style>
		<![endif]-->
	</head>
	<body>
		<table style="padding: 0px; margin:0px; border: 0px none;" id="backgroundTable">
			<tr>
				<td>
					<table style="padding: 0px; margin:0px; border: 0px none;">
						<thead>
							<tr>
								<th width="600" valign="top">
									<header>
										<h1>
											<a style="color: #000000; text-decoration: none;" target="_blank" href="https://apps.facebook.com/armazemdorosario" title="Acessar o Armazapp">
												<img title="Armazém do Rosário" class="image_fix" alt="Logotipo do Armazém" <?php echo 'src="https://fbcdn-photos-b-a.akamaihd.net/hphotos-ak-prn1/t39.2081-0/p128x128/851590_240718916089300_1733288286_n.png" srcset="https://fbcdn-photos-b-a.akamaihd.net/hphotos-ak-prn1/t39.2081-0/p128x128/851590_240718916089300_1733288286_n.png 2x"'; ?> width="64" height="64" />
												&nbsp;
												Armazapp
											</a>
										</h1>
										<hr style="clear: both" />
									</header>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td width="600" valign="top">
<?php
		echo '<main>';
		return ob_get_clean();
	}
	
	public function footer() {
		ob_start();
		echo '</main>';
?>
									<hr />
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
<?php
		@include 'views/foot.phtml';
		return ob_get_clean();
	}
	
	public function __construct($to, $subject, $message) {
		$this->headers[] = 'X-Mailer: PHP/' . phpversion();
		$this->headers[] = 'Date: ' . date('r', $_SERVER['REQUEST_TIME']);
		$this->headers[] = 'Message-ID: <' . $_SERVER['REQUEST_TIME'] . md5($_SERVER['REQUEST_TIME']) . '@' . $_SERVER['SERVER_NAME'] . '>';
		$this->headers[] = 'X-Originating-IP: ' . $_SERVER['SERVER_ADDR'];
		$this->subject = $subject;
		$this->encoded_subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
		$this->message = $this->header() . $message . $this->footer();
		$this->message = str_replace("\n.", "\n..", $this->message);
		$this->to = $to;
	}
	
	public function send() {

		$mail_result = mail($this->to, $this->encoded_subject, $this->message, implode("\r\n", $this->headers));
		
		if(!$mail_result) {
			error_log('Erro ao enviar e-mail para ' . $this->to . ' Assunto: ' . $this->subject);
		}
		
		return $mail_result;
		
	}
	
	public function __toString() {
		return var_export($this, true);
	}
	
}

?>