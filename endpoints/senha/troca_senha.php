<?php

require_once("../../db/dbconexao.php");
require_once("../../classes/usuario.php");

$vars = json_decode(file_get_contents('php://input'), true);

if(!isset($vars['codigo']) || !isset($vars['senha'])) {
    $response = array();
    if (!isset($vars['codigo'])){
        $response['codigo'] = null;
    }
    if (!isset($vars['senha'])){
        $response['senha'] = null;
    }
    $response['descricao'] = 'Variável com valor = null não foi enviada';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$codigo = $vars['codigo'];
$senha = $vars['senha'];

if (strlen($codigo) != 40) {
    $response = array();
    $response['codigo'] = strlen($codigo);
    $response['descricao'] = 'Código deve ter 40 caracteres';
    http_response_code(400);
    echo json_encode($response);
    exit();
}

$usuario = new Usuario('','','','',$senha);

if(!$usuario->validarSenha($senha)) {
    $response = array();
    $response['senha'] = false;
    $response['descricao'] = 'Senha Fraca';
    http_response_code(400);
    echo json_encode($response);
    exit();
};

try {
    $tentativas = $usuario->verificaCodigoSenha($conn, $codigo);

    if($tentativas == 1) {
        $usuario->trocaSenha($conn, $codigo, $senha);
        http_response_code(200);
        echo json_encode(array(
            "valido" => true,
            "descricao" => 'Senha trocada',
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