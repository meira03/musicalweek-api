<?php

    header('Access-Control-Allow-Origin: https://musicalweek.azurewebsites.net');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: POST');
    header('Content-Type: application/json; charset=utf-8');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {    
        return 0;    
    }  
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(array('POST' => false));
        exit();
    }

    $vars = json_decode(file_get_contents('php://input'), true);

    if(!isset($vars['id_usuario']) || !isset($vars['id_musica_sala']) || !isset($vars['nota'])) {
        $response = array();
        if (!isset($vars['id_usuario'])){
            $response['id_usuario'] = null;
        }
        if (!isset($vars['id_musica_sala'])){
            $response['id_musica_sala'] = null;
        }
        if (!isset($vars['nota'])){
            $response['nota'] = null;
        }
        http_response_code(400);
        echo json_encode($response);
        exit();
    }

    include("../db/dbconexao.php");
    include("../classes/avaliacao.php");

    date_default_timezone_set('america/sao_paulo');

    $avaliacao = new Avaliacao($vars['id_usuario'], $vars['id_musica_sala'], $vars['nota']);
    
    try {
        if (!$avaliacao->validaUsuarioSala($conn)) {
            http_response_code(403);
            echo json_encode(array(
                "erro" => "Usuario não esta na sala",
            ), JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($avaliacao->registra($conn)) {
            http_response_code(200);
            echo json_encode(array(
                "sucesso" => true,
            ), JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(409);
            echo json_encode(array(
                "erro" => "Avaliação ja registrada",
            ), JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }
?>