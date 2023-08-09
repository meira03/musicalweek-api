<?php

header('Access-Control-Allow-Origin: https://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET');
header('Content-Type: application/json; charset=utf-8');

// include("../../token/auth.php");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {    
    return 0;    
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    include("perfil.php");
} else {
    http_response_code(405);
    echo json_encode(array('POST' => false, 'GET' => false));
    exit();
}
?>