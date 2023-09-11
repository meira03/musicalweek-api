<?php 

date_default_timezone_set('america/sao_paulo');

$vars = json_decode(file_get_contents('php://input'), true);

if(!isset($vars['id_musica'])) {
    $response = array();
    $response['id_musica'] = null;
    $response['descricao'] = "Id da música não enviado";
    echo json_encode($response);
    exit();
}

include("../../db/dbconexao.php");
include("../../classes/sala.php");

$fila = new sala('');

try {
    $limite = $fila->limite($conn, $idUsuario);
    if ($limite > 0) {
        http_response_code(401);
        echo json_encode(array(
            "limite" => $limite,
            "descricao" => "Limite de salas atingido",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }
    echo json_encode(array(
        "id" => $fila->insere($conn, $idUsuario, $vars['id_musica']),
    ), JSON_UNESCAPED_UNICODE);
    http_response_code(200);
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}