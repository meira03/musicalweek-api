<?php

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {    
    return 0;    
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if(isset($_SERVER['HTTP_AUTHORIZATION'])){
        require_once("../../token/auth/auth.php");
        require_once("home_logado.php");
    } else {
        require_once("home.php");
    }
} else {
    http_response_code(405);
    echo json_encode(array('GET' => false));
    exit();
}
?>