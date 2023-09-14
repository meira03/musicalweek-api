<?php

header('Access-Control-Allow-Origin: https://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');
header('Content-Type: application/json; charset=utf-8');

echo $_SERVER['REQUEST_METHOD'];
die();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {    
    return 0;    
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    include("../../token/auth/auth.php");
    include("perfil.php");
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    include("../../token/auth/auth.php");
    $vars = json_decode(file_get_contents('php://input'), true);
    if (isset($vars['plano'])) {
        include("plano.php");
    } elseif (isset($vars['senha'])) {
        include("senha.php");
    } else {
        include("altera.php");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vars = json_decode(file_get_contents('php://input'), true);
    if (!isset($vars['nome']) && !isset($vars['nick']) && !isset($vars['data_nasc'])) {
        include("login.php");
    } else {
        include("cadastro.php");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    include("../../token/auth/auth.php");
    include("deleta.php");
} else {
    http_response_code(405);
    echo json_encode(array('GET' => false, 'POST' => false, 'PUT' => false, 'DELETE' => false));
    exit();
}
?>
