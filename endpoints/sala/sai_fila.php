<?php 

$vars = json_decode(file_get_contents('php://input'), true);

if(!isset($vars['id_musica_sala'])) {
    $response = array();
    $response['id_musica_sala'] = null;
    $response['descricao'] = "Id da musica sala não enviado";
    echo json_encode($response);
    exit();
}

if(!is_int($vars['id_musica_sala'])) {
    $response = array();
    $response['id_musica_sala'] = false;
    $response['descricao'] = "Id da musica sala deve ser é inteiro, e não " . gettype($vars['id_musica_sala']);
    echo json_encode($response);
    exit();
}

include("../../db/dbconexao.php");
include("../../classes/sala.php");

$fila = new sala('');

try {
    $codigo = $fila->saiFila($conn, $idUsuario, $vars['id_musica_sala']);

    if ($codigo['CODIGO'] == 2) {
        http_response_code(401);
        echo json_encode(array(
            "id_musica_sala" => false,
            "descricao" => "Esse id da fila não pertence a esse usuário",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($codigo['CODIGO'] == 1) {
        http_response_code(200);
        echo json_encode(array(
            "sucesso" => true
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($codigo['CODIGO'] == 0) {
        http_response_code(409);
        echo json_encode(array(
            "id_sala" => intval($codigo['ID_SALA'])
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}