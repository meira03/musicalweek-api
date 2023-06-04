<?php
    
    header('Access-Control-Allow-Origin: http://localhost:3000');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET');
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(array('GET' => false));
        exit();
    }

    if (!isset($_GET['id_usuario'])) {
        http_response_code(400);
        echo json_encode(['id_usuario' => null]);
        exit();
    }

    include("../db/dbconexao.php");
    include("../classes/usuario.php");

    $usuario = new Usuario('','','','','');

    try {
        if (!$usuario->select($conn, $_GET['id_usuario'])) {
            http_response_code(404);
            echo json_encode(array('id_usuario' => false));
            exit();
        }
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }

    http_response_code(200);
    echo json_encode(
        array(
            'nome' => $usuario->getNome(),
            'email' => $usuario->getEmail(),
            'nick' => $usuario->getNick(),
            'data_nasc' => date("d/m/Y", strtotime($usuario->getDataNasc()))
        )
    );
?>
