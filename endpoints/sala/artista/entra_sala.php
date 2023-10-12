<?php 

require_once("../../../db/dbconexao.php");
require_once("../../../classes/sala.php");

$sala = new sala('');

try {
    $codigo = $sala->entraSalaArtista($conn, $vars['id_sala'], $idUsuario);

    if ($codigo == 0) {
        http_response_code(409);
        echo json_encode(array(
            "usuario" => true,
            "descricao" => "Usuário já está na sala",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($codigo == 1) {
        http_response_code(410);
        echo json_encode(array(
            "finalizada" => true,
            "descricao" => "Sala ja acabou",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($codigo == 2 || $codigo == 3) {
        http_response_code(404);
        echo json_encode(array(
            "id_sala" => false,
            "descricao" => "Id Sala errado",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    http_response_code(200);
    echo json_encode(array(
        "sucesso" => true,
    ), JSON_UNESCAPED_UNICODE);
    exit();
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}