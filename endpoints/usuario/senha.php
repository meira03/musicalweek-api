<?php

if(!isset($vars['nova'])) {
    $response = array();
    $response['nova'] = null;
    $response['descricao'] = "Senha nova não enviada";
    http_response_code(400);
    echo json_encode($response);
    exit();
}

include("../../db/dbconexao.php");
include("../../classes/usuario.php");

$usuario = new Usuario('','','','',$vars['nova']);

if($vars['nova'] == $vars['senha']) {
    $response = array();
    $response['nova'] = false;
    $response['descricao'] = "Senhas iguais";
    http_response_code(400);
    echo json_encode($response);
    exit();
}


try {

    if(!($usuario->verificaSenha($conn, $idUsuario, $vars['senha']))) {
        $response = array();
        $response['senha'] = false;
        $response['descricao'] = "Senha errada";
        http_response_code(401);
        echo json_encode($response);
        exit();
    }
    
    if(!($usuario->validarSenha())) {
        $response = array();
        $response['nova'] = false;
        $response['descricao'] = "Senha fraca";
        http_response_code(400);
        echo json_encode($response);
        exit();
    }

    $usuario->novaSenha($conn, $idUsuario);
    http_response_code(200);
    echo json_encode(array(
        "sucesso" => true
    ), JSON_UNESCAPED_UNICODE);
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}