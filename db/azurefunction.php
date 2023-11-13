<?php
    header('Access-Control-Allow-Methods: GET');
    header('Content-Type: application/json; charset=utf-8');

    if ($_GET['codigo'] != "26Kf7A0m2X9rl5Ai2") {
        http_response_code(500);
        exit;
    }

    require_once("dbconexao.php");
    
    try {
        $stmt = $conn->prepare("SP_CARGA_TOPS");
        $stmt->execute();
        http_response_code(200);
        echo json_encode(array(
            "sucesso" => true,
        ), JSON_UNESCAPED_UNICODE);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }
?>