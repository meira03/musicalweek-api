<?php

require_once("../../db/dbconexao.php");
require_once("../../classes/usuario.php");

if(!isset($_GET['codigo'])) {
    $response = array();
    $response['codigo'] = null;
    $response['descricao'] = 'Código não foi enviado';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$codigo = $_GET['codigo'];

if (strlen($codigo) != 40) {
    $response = array();
    $response['codigo'] = strlen($codigo);
    $response['descricao'] = 'Código deve ter 40 caracteres';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$usuario = new Usuario('','','','','');

try {
    $tentativas = $usuario->verificaCodigoSenha($conn, $codigo);

    if($tentativas == 1) {
        http_response_code(200);
        echo json_encode(array(
            "valido" => true,
            "descricao" => 'Código é válido',
        ), JSON_UNESCAPED_UNICODE);
    } elseif ($tentativas == -1) {
        http_response_code(401);
        echo json_encode(array(
            "expirado" => true,
            "descricao" => 'Código expirou, foi gerado a mais de um dia',
        ), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(401);
        echo json_encode(array(
            "valido" => false,
            "descricao" => 'Código inválido',
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