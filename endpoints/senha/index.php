<?php

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, PUT');
header('Content-Type: application/json; charset=utf-8');
echo " ";
date_default_timezone_set('america/sao_paulo');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {    
    return 0;    
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    include("verifica_codigo.php");
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include("gera_codigo.php");
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    include("troca_senha.php");
} else {
    http_response_code(405);
    echo json_encode(array('POST' => false, 'GET' => false, 'PUT' => false));
    exit();
}
?>