<?php
    
    header('Access-Control-Allow-Origin: http://localhost:3000');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET');
    header('Content-Type: application/json; charset=utf-8');
    
    date_default_timezone_set('america/sao_paulo');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(array('GET' => false));
        exit();
    }

    if (!isset($_GET['id_musica_sala'])) {
        http_response_code(400);
        echo json_encode(['id_musica_sala' => null]);
        exit();
    }

    include("../db/dbconexao.php");
    include("../classes/sala.php");

    $sala = new Sala($_GET['id_musica_sala']);

    try {
        if(!$sala->verifica($conn)){
            http_response_code(404);
            echo json_encode($sala->getFila($conn), JSON_UNESCAPED_UNICODE);
            exit();
        }
    
        echo json_encode($sala->getInfo($conn));
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }

?>