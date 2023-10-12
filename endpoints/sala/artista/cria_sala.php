<?php 

if(!isset($vars['musicas'])) {
    $response = array();
    $response['musicas'] = null;
    $response['descricao'] = "musicas não enviadas";
    http_response_code(400);
    echo json_encode($response);
    exit();
}

if (!is_array($vars['musicas'])) {
    $response = array();
    $response['musicas'] = false;
    $response['descricao'] = "musicas não é um array";
    http_response_code(400);
    echo json_encode($response);
    exit();
}

if (count($vars['musicas']) !== 7) {
    $response = array();
    $response['musicas'] = count($vars['musicas']);
    $response['descricao'] = "musicas deve ter 7 músicas, e não " . count($vars['musicas']);
    http_response_code(400);
    echo json_encode($response);
    exit();
}


for ($i = 0; $i < 7; $i++) {
    for ($j = $i + 1; $j < 7; $j++) {
        if ($vars['musicas'][$i] === $vars['musicas'][$j]) {
            $response = array();
            $response['musicas'] = false;
            $response['descricao'] = "Musica repetida enviada";
            http_response_code(400);
            echo json_encode($response);
            exit();
        }
    }
}

require_once("../../../db/dbconexao.php");
require_once("../../../classes/sala.php");
require_once("../../../classes/usuario.php");

$sala = new Sala('');

try {
    $id = $sala->criaSalaArtista($conn, $idUsuario, $vars['musicas']);

    if ($id == 1) {
        http_response_code(401);
        echo json_encode(array(
            "sala" => true,
            "descricao" => "Já existe uma sala ativa",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($id == 0) {
        http_response_code(401);
        echo json_encode(array(
            "tipo_plano" => false,
            "descricao" => "Usuário não é um artista",
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    http_response_code(200);
    echo json_encode(array(
        "id_sala" => $id
    ), JSON_UNESCAPED_UNICODE);

} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}