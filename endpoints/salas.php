<?php 
    // header('Access-Control-Allow-Origin: https://musicalweek.vercel.app');
    header('Access-Control-Allow-Origin: http://localhost:3000');
    // header('Access-Control-Allow-Origin: https://localhost:3000');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json; charset=utf-8');

    date_default_timezone_set('america/sao_paulo');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {    
        return 0;    
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(array('POST' => false));
        exit();
    }

    $vars = json_decode(file_get_contents('php://input'), true);

    if (!isset($vars['id_usuario'])){
        http_response_code(400);
        echo json_encode(['id_usuario' => null]);
        exit();
    }

    include("../db/dbconexao.php");
    include("../classes/sala.php");

    $busca = new Sala('');

    echo json_encode(array('salas' => $busca->buscaSalas($conn, $vars['id_usuario'])), JSON_UNESCAPED_UNICODE);

?>