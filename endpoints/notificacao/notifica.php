<?php
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json; charset=utf-8');

require_once('../email/src/PHPMailer.php');
require_once('../email/src/SMTP.php');
require_once('../email/src/Exception.php');
require_once("../../db/dbconexao.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_GET['codigo'] != "26Kf7A0m2X9rl5Ai2") {
    http_response_code(403);
    exit();
}

try {
    $stmt = $conn->prepare("SP_RETORNA_SALAS_CRIADAS");
    $stmt->execute();
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if($salas == null) {
        echo json_encode(array(
            "erro" => "Sem salas"
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
    exit();
}

foreach($salas as $sala) {
    try {
        $stmt = $conn->prepare(
            "SELECT u.email from UsuarioMusicaSala ms inner join Usuario u 
            on u.id_usuario = ms.id_usuario 
            where ms.id_sala = :sala and u.status = 1");
        $stmt->bindParam(':sala', $sala['id_sala']);
        $stmt->execute();

        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
        continue;
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

        foreach ($emails as $email) {
            $mail->addAddress($email['email']);
        }

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'A sala começou'; 
        $mail->Body = 'Clique para entrar: <a href="http://localhost:3000/pt/sala/' . $sala['id_sala'] . '"> Sala </a>';
        $mail->AltBody = 'Link da sala: http://localhost:3000/pt/sala/' . $sala['id_sala'];

        if(!$mail->send()) {
            http_response_code(500);
            echo json_encode(array(
                "erro" => "Email Não Enviado",
            ), JSON_UNESCAPED_UNICODE);
        } 
    } catch (Exception $e) {
        http_response_code(500);
            echo json_encode(array(
                "erro" => $mail->ErrorInfo,
            ), JSON_UNESCAPED_UNICODE);
        continue;
    }
}
?>