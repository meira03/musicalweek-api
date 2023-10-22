<?php

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, DELETE');
header('Content-Type: application/json; charset=utf-8');
echo " ";
require_once("../../../token/auth/auth/auth.php");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {    
    return 0;    
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once("get_sala.php");
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vars = json_decode(file_get_contents('php://input'), true);
    if(isset($vars['id_sala'])) {
        require_once("entra_sala.php");
    } else {
        require_once("cria_sala.php");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    require_once("sai_sala.php");
} else {
    http_response_code(405);
    echo json_encode(array('POST' => false, 'GET' => false, 'DELETE' => false));
    exit();
}
?>