<?php

require_once('../email/src/PHPMailer.php');
require_once('../email/src/SMTP.php');
require_once('../email/src/Exception.php');
require_once("../../db/dbconexao.php");
require_once("../../classes/usuario.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$vars = json_decode(file_get_contents('php://input'), true);

if (!isset($vars['email'])) {
    http_response_code(400);
    echo json_encode(['email' => null, 'descricao' => 'Email não foi enviado']);
    exit();
}

$email = $vars['email'];
$usuario = new Usuario('','','',$email,'');

try {
	$idUsuario = $usuario->getid($conn);
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(array(
		"erro" => $ex->getMessage(),
	), JSON_UNESCAPED_UNICODE);
	exit;
}

if ($idUsuario == null) {
	http_response_code(401);
    echo json_encode(['email' => false, 'descricao' => 'Email não cadastrado']);
    exit();
}

$caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$codigo = '';

for ($i = 0; $i < 40; $i++) {
	$indice = mt_rand(0, strlen($caracteres) - 1);
	$caractere = $caracteres[$indice];
	$codigo .= $caractere;
}

try {
	$usuario->insertCodigoSenha($conn, $idUsuario, $codigo);
} catch (PDOException $e) {
	http_response_code(500);
	echo json_encode(array(
		"erro" => $ex->getMessage(),
	), JSON_UNESCAPED_UNICODE);
	exit;
}
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
	$mail->Subject = 'Recuperação de senha'; 
	$mail->Body = 
    '<div style="max-width: 600px; margin: 0 auto; background-color: black; padding: 20px; text-align: center; border-radius: 10px; box-shadow: 0 0 10px #14defa; font-family: \'Arial\', \'Helvetica\', sans-serif;">
        <h1 style="color: #14defa; text-transform: uppercase;">TROQUE SUA SENHA</h1>

        <div style="border-top: 2px solid #14defa; padding-top: 10px;"></div>

        <p style="color: white;">Recebemos um pedido para redefinir sua senha.</p>
        <p style="color: white;">Caso não deseje mudar a sua senha, você pode ignorar este e-mail.</p>
        <p style="color: white;">Clique no botão abaixo caso queira trocar sua senha:</p>

        <button style="background-color: #14defa; color: white; font-weight: bold; padding: 10px 0; border: none; cursor: pointer; width: 100%;">
            <a href="https://musicalweek.azurewebsites.net/pt/esqueci-senha/' . $codigo . '" style="color: white; text-decoration: none; display: block;">REDEFINIR SENHA</a>
        </button>

        <div style="margin-top: 20px; border-top: 2px solid #14defa; padding-top: 10px;">
            <p style="color: white;">Este e-mail está sendo enviado por MusicalWeek</p>
        </div>
    </div>';
	$mail->AltBody = 'Clique no link para trocar senha: https://musicalweek.azurewebsites.net/pt/esqueci-senha/' . $codigo;

	if($mail->send()) {
        http_response_code(200);
            echo json_encode(array(
                "sucesso" => true,
            ), JSON_UNESCAPED_UNICODE);
	} else {
        http_response_code(500);
        echo json_encode(array(
            "erro" => 'Código no email não enviado',
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