<?php

include("../../db/dbconexao.php");
include("../../classes/usuario.php");

$usuario = new Usuario('','','','','');

try {
    $perfil = $usuario->perfil($conn, $idUsuario);
    $fila = $usuario->getFila($conn, $idUsuario);
    $salas = $usuario->getSalas($conn, $idUsuario);
    $historico = $usuario->getHistorico($conn, $idUsuario);

    if ($historico['total'] == false) {
        $historico = array("total"=> 0);
    }

    http_response_code(200);
    echo json_encode(array(
        "perfil" => $perfil,
        "fila" => $fila,
        "salas" => $salas,
        "historico" => $historico
    ), JSON_UNESCAPED_UNICODE);
    // echo json_encode($perfil);
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}