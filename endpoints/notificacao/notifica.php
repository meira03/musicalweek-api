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
    $stmt = $conn->prepare("SP_ATUALIZA_STATUS_SALA");
    $stmt->execute();
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
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

    foreach ($emails as $email) {
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
            $mail->addAddress($email['email']);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'A sala começou'; 
            $mail->Body = 
            '<div style="background-color: black; padding: 20px; text-align: center; border-radius: 10px; box-shadow: 0 0 10px #14defa; font-family: \'Arial\', \'Helvetica\', sans-serif;">
                <h1 style="color: #14defa; text-transform: uppercase;">SALA PADRÃO INICIADA!</h1>
                
                <button style="background-color: #14defa; color: white; font-weight: bold; padding: 10px 0; border: none; cursor: pointer; width: 100%;">
                    <a href="musicalweek.azurewebsites.net/pt/sala/' . $sala['id_sala'] . '/1" style="color: white; text-decoration: none; display: block;">ENTRAR</a>
                </button>
        
                <div style="margin-top: 20px; border-top: 2px solid #14defa; padding-top: 10px;">
                    <p style="color: white;">Este e-mail está sendo enviado por MusicalWeek</p>
                </div>
            </div>';
            
            $mail->AltBody = 'Link da sala: musicalweek.azurewebsites.net/pt/sala/' . $sala['id_sala'] . '/1';
            $mail->ContentType = 'text/html';

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
}
?>