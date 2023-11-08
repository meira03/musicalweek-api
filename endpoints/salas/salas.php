<?php

include("../../db/dbconexao.php");
include("../../classes/usuario.php");

$usuario = new Usuario('','','','','');

try {
    echo json_encode($usuario->getTodasSalas($conn, $idUsuario), JSON_UNESCAPED_UNICODE);
    http_response_code(200);
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}