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

echo $idUsuario;
//$idUsuario = 2176;

// $codigo = rand(1, 999999);

// $usuario->insertCodigo($conn, $idUsuario, $codigo);

// $usuario->selectEmail($conn, $idUsuario);

// try {
//     $mail = new PHPMailer(true);

// 	//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
// 	$mail->isSMTP();
// 	$mail->Host = 'smtp.gmail.com';
// 	$mail->SMTPAuth = true;
// 	$mail->Username = 'cadastromusicalweek@gmail.com';
// 	$mail->Password = 'icpjikwlsdzmiudj';
// 	$mail->Port = 587;

// 	$mail->setFrom('cadastromusicalweek@gmail.com');
// 	$mail->addAddress($usuario->getEmail());

// 	$mail->isHTML(true);
// 	$mail->Subject = 'Confirme o seu Email'; 
// 	$mail->Body = 'Seu código de confirmação de email é: <strong> ' . $codigo . '<strong>';
// 	$mail->AltBody = 'Seu código de confirmação de email é: ' . $codigo;

// 	if($mail->send()) {
//         http_response_code(200);
//             echo json_encode(array(
//                 "sucesso" => true,
//             ), JSON_UNESCAPED_UNICODE);
// 	} else {
//         http_response_code(500);
//         echo json_encode(array(
//             "erro" => 'Email não enviado',
//         ), JSON_UNESCAPED_UNICODE);
// 	}
// } catch (Exception $e) {
// 	http_response_code(500);
//         echo json_encode(array(
//             "erro" => $mail->ErrorInfo,
//         ), JSON_UNESCAPED_UNICODE);
//     exit();
// }
// ?>