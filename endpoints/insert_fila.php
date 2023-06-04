<?php 

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('america/sao_paulo');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('POST' => false));
    exit();
}

$vars = json_decode(file_get_contents('php://input'), true);

if(!isset($vars['id_usuario']) || !isset($vars['id_musica']) || !isset($vars['nome_genero'])) {
    $response = array();
    if (!isset($vars['id_usuario'])){
        $response['id_usuario'] = null;
    }
    if (!isset($vars['id_musica'])){
        $response['id_musica'] = null;
    }
    if (!isset($vars['nome_genero'])){
        $response['nome_genero'] = null;
    }
    echo json_encode($response);
    exit();
}

include("../db/dbconexao.php");
include("../classes/fila.php");

$fila = new Fila($vars['id_usuario'], $vars['id_musica'], $vars['nome_genero']);

try {
    echo json_encode(array(
        "id" => $fila->insere($conn),
    ), JSON_UNESCAPED_UNICODE);
    http_response_code(200);
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}