<?php

include("../../db/dbconexao.php");
include("../../classes/usuario.php");

$usuario = new Usuario('','','','','');

try {
    $perfil = $usuario->perfil($conn, $idUsuario);
    http_response_code(200);
    echo json_encode($perfil);
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}