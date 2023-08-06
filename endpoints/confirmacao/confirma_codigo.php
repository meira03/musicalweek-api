<?php

require_once("../../db/dbconexao.php");
require_once("../../classes/usuario.php");

$codigo = json_decode(file_get_contents('php://input'), true);

if(!isset($codigo['codigo'])) {
    $response = array();
    $response['codigo'] = null;
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$codigo = $codigo['codigo'];

if (!is_int($codigo)) {
    $response = array();
    $response['codigo'] = gettype($codigo);
    http_response_code(400);
    echo json_encode($response);
    exit();
}

if (!($codigo >= 1 && $codigo <= 999999)) {
    $response = array();
    $response['codigo'] = 'Fora do intervalo';
    $response['intervalo'] = '1 a 999999';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$usuario = new Usuario('','','','','');

$idUsuario = 2176;

try {
    $tentativas = $usuario->confirmaCodigo($conn, $idUsuario, $codigo);

    if($tentativas == 0) {
        http_response_code(200);
        echo json_encode(array(
            "confirmacao" => true,
        ), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(401);
        echo json_encode(array(
            "tentativas" => $tentativas,
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