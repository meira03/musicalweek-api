<?php

    // header('Access-Control-Allow-Origin: https://musicalweek.vercel.app');
    header('Access-Control-Allow-Origin: http://localhost:3000'); // HTTP Remover
    // header('Access-Control-Allow-Origin: https://localhost:3000');
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

    if(!isset($vars['email']) || !isset($vars['senha'])) {
        $response = array();
        if (!isset($vars['email'])){
            $response['email'] = null;
        }
        if (!isset($vars['senha'])){
            $response['senha'] = null;
        }
        http_response_code(400);
        echo json_encode($response);
        exit();
    }

    include("../db/dbconexao.php");
    include("../classes/usuario.php");
    include("../token/token.php");

    use Firebase\JWT\JWT;
    header('Access-Control-Allow-Origin: *');

    $usuario = new Usuario('','','',$vars['email'],$vars['senha']);

    try {
        if($usuario->login($conn)){
            // echo json_encode(array('id_usuario' => $usuario->getid($conn)));
            http_response_code(200);
            echo json_encode(array('token' => gerarToken($usuario->getid($conn))));
        } else {
            http_response_code(401);
            echo json_encode(array('login'=> false));
            exit();
        }
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }
?>