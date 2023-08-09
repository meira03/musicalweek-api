<?php

if (!isset($_GET['id_usuario'])) {
    http_response_code(400);
    echo json_encode(['id_usuario' => null]);
    exit();
}

include("../../db/dbconexao.php");
include("../../classes/usuario.php");

$usuario = new Usuario('','','','','');

try {
    if (!$usuario->select($conn, $_GET['id_usuario'])) {
        http_response_code(404);
        echo json_encode(array('id_usuario' => false));
        exit();
    }
    $plano = $usuario->getPlano($conn, $_GET['id_usuario']);
    
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
        'email' => $usuario->getEmailCensurado(),
        'nick' => $usuario->getNick(),
        'data_nasc' => date("d/m/Y", strtotime($usuario->getDataNasc())),
        'tipo_plano' => $plano
    ), JSON_UNESCAPED_UNICODE
);