<?php 

if(!isset($_GET['id_sala'])) {
    $response = array();
    $response['id_sala'] = null;
    $response['descricao'] = "Id da sala não enviado";
    http_response_code(400);
    echo json_encode($response);
    exit();
}

require_once("../../../db/dbconexao.php");
require_once("../../../classes/sala.php");

$sala = new sala('');

try {
    $codigo = $sala->saiSalaArtista($conn, $_GET['id_sala'], $idUsuario);

    if ($codigo == 5) {
        http_response_code(200);
        echo json_encode(array(
            "sucesso" => true,
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($codigo == 3) {
        http_response_code(404);
        echo json_encode(array(
            "usuario" => false,
            "descricao" => "Usuário não está na sala",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($codigo == 2) {
        http_response_code(410);
        echo json_encode(array(
            "finalizada" => true,
            "descricao" => "Sala ja acabou",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($codigo == 0) {
        http_response_code(404);
        echo json_encode(array(
            "id_sala" => false,
            "descricao" => "Id Sala errado",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($codigo == 1) {
        http_response_code(404);
        echo json_encode(array(
            "artista" => true,
            "descricao" => "Artista não pode sair da sala",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($codigo == 4) {
        http_response_code(500);
        echo json_encode(array(
            "update" => false,
            "descricao" => "Erro ao sair da sala",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}