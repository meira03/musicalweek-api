<?php 

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
    // $limite = $fila->limite($conn, $idUsuario);

    // if ($limite != 0) {
    //     http_response_code(401);
    //     echo json_encode(array(
    //         "limite" => $limite,
    //         "descricao" => "Limite de salas atingido",
    //     ), JSON_UNESCAPED_UNICODE);
    //     exit();
    // }

    $insert = $fila->insereFila($conn, $idUsuario, $vars['id_musica']);

    if($insert['id_sala'] != null){
        http_response_code(201);
        echo json_encode(array(
            "id_sala" => $insert['id_sala']
        ), JSON_UNESCAPED_UNICODE);
        exit();
    } else {
        http_response_code(200);
        echo json_encode(array(
            "id_musica_sala" => $insert['id_musicasala']
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}