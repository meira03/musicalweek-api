<?php

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET');
header('Content-Type: application/json; charset=utf-8');

include("../../token/auth/auth.php");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {    
    return 0;    
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if(isset($_GET['id_musica_sala'])) {
        include("get_fila.php");
    } else {
        include("get_sala.php");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include("entra_fila.php");
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    include("sai_fila.php");
} else {
    http_response_code(405);
    echo json_encode(array('POST' => false, 'GET' => false, 'DELETE' => false));
    exit();
}
?>