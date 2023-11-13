<?php

require_once('src/PHPMailer.php');
require_once('src/SMTP.php');
require_once('src/Exception.php');
require_once("../../db/dbconexao.php");
require_once("../../classes/usuario.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$usuario = new Usuario('','','','','');

if($usuario->verificacaoEmail($conn, $idUsuario)) {
	http_response_code(409);
            echo json_encode(array(
                "verificado" => true,
				"descricao" => "Email verificado anteriormente",
            ), JSON_UNESCAPED_UNICODE);
	exit();
}

$codigo = rand(1, 999999);

$usuario->insertCodigo($conn, $idUsuario, $codigo);

$usuario->selectEmail($conn, $idUsuario);

try {
    $mail = new PHPMailer(true);

	//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
	$mail->isSMTP();
	$mail->Host = 'smtp.gmail.com';
	$mail->SMTPAuth = true;
	$mail->Username = 'cadastromusicalweek@gmail.com';
	$mail->Password = 'icpjikwlsdzmiudj';
	$mail->Port = 587;

	$mail->setFrom('cadastromusicalweek@gmail.com');
	$mail->addAddress($usuario->getEmail());

	$mail->isHTML(true);
	$mail->CharSet = 'UTF-8';
	$mail->Subject = 'Confirmação de Email'; 
	$mail->Body = 
	'<div style="max-width: 600px; margin: 0 auto; background-color: black; padding: 20px; text-align: center; border-radius: 10px; box-shadow: 0 0 10px #14defa; font-family: \'Arial\', \'Helvetica\', sans-serif;">
		<h1 style="color: #14defa; text-transform: uppercase;">CONFIRMAÇÃO DE E-MAIL</h1>

		<div style="border-top: 2px solid #14defa; padding-top: 10px;"></div>

		<p style="color: white;">Seu email foi cadastrado no site MusicalWeek!</p>
		<p style="color: white;">Confirme seu email para poder receber notificações quando uma sala começar</p>

		<button style="background-color: #14defa; color: white; font-weight: bold; padding: 10px 0; border: none; cursor: pointer; width: 100%;">
			<a href="https://musicalweek.azurewebsites.net/pt/confirmaEmail/' . $codigo . '" style="color: white; text-decoration: none; display: block;">CONFIRMAR E-MAIL</a>
		</button>

		<div style="margin-top: 20px; border-top: 2px solid #14defa; padding-top: 10px;">
			<p style="color: white;">Este e-mail está sendo enviado por MusicalWeek</p>
		</div>
	</div>';
	$mail->AltBody = 'Clique no link para confirmar email: https://musicalweek.azurewebsites.net/pt/confirmaEmail/' . $codigo;

	if($mail->send()) {
        http_response_code(200);
            echo json_encode(array(
                "sucesso" => true,
            ), JSON_UNESCAPED_UNICODE);
	} else {
        http_response_code(500);
        echo json_encode(array(
            "erro" => 'Email não enviado',
        ), JSON_UNESCAPED_UNICODE);
	}
} catch (Exception $e) {
	http_response_code(500);
        echo json_encode(array(
            "erro" => $mail->ErrorInfo,
        ), JSON_UNESCAPED_UNICODE);
    exit();
}
?>