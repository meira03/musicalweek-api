<?php

if (!is_int($vars['plano'])) {
    $response = array();
    $response['plano'] = false;
    $response['descricao'] = "O plano deve ser inteiro, não " . gettype($vars['plano']);
    http_response_code(400);
    echo json_encode($response);
    exit();
}

if ($vars['plano'] !== 0 && $vars['plano'] !== 1 && $vars['plano'] !== 2) {
    $response = array();
    $response['plano'] = false;
    $response['descricao'] = 'O plano deve ser 0, 1 ou 2';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

include("../../db/dbconexao.php");
include("../../classes/usuario.php");

$usuario = new Usuario('','','','','');

try {
    http_response_code(200);
    echo json_encode(array(
        "plano" => $usuario->trocaPlano($conn, $idUsuario, $vars['plano'])
    ), JSON_UNESCAPED_UNICODE);
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}