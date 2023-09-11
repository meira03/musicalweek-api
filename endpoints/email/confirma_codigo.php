<?php

require_once("../../db/dbconexao.php");
require_once("../../classes/usuario.php");

$codigo = json_decode(file_get_contents('php://input'), true);

if(!isset($codigo['codigo'])) {
    $response = array();
    $response['codigo'] = null;
    $response['descricao'] = "codigo não enviado";
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$codigo = $codigo['codigo'];

if (!is_int($codigo)) {
    $response = array();
    $response['codigo'] = gettype($codigo);
    $response['descricao'] = "código não é inteiro";
    http_response_code(400);
    echo json_encode($response);
    exit();
}

if (!($codigo >= 1 && $codigo <= 999999)) {
    $response = array();
    $response['codigo'] = false;
    $response['descricao'] = 'Fora do intervalo';
    $response['intervalo'] = '1 a 999999';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$usuario = new Usuario('','','','','');

try {
    if($usuario->confirmacao($conn, $idUsuario)) {
        http_response_code(409);
        echo json_encode(array(
            "verificado" => true,
            "descricao" => "Email verificado anteriormente",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    $tentativas = $usuario->confirmaCodigo($conn, $idUsuario, $codigo);

    if($tentativas == 0) {
        http_response_code(200);
        echo json_encode(array(
            "verificado" => true,
        ), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(401);
        echo json_encode(array(
            "codigo" => false,
            "tentativas" => $tentativas,
            "descricao" => "código errado",
        ), JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
    exit();
}

?>