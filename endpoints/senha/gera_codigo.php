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
$idUsuario = $usuario->getid($conn);

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

$usuario->insertCodigoSenha($conn, $idUsuario, $codigo);

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
	$mail->Subject = 'Troque sua senha'; 
	$mail->Body = 'Clique para trocar senha <a href="http://localhost:3000/pt/esqueci-senha/' . $codigo . '"> Trocar Senha </a>';
	$mail->AltBody = 'Clique no link para trocar senha: http://localhost:3000/pt/esqueci-senha/' . $codigo;

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