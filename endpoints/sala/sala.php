<?php
    
    if(!isset($_GET['id_usuario']) || !isset($_GET['sala'])) {
        $response = array();
        if (!isset($_GET['sala'])){
            $response['sala'] = null;
        }
        if (!isset($_GET['id_usuario'])){
            $response['id_usuario'] = null;
        }
        http_response_code(400);
        echo json_encode($response);
        exit();
    }
    
    include("../../db/dbconexao.php");
    include("../../classes/sala.php");
    
    $sala = new Sala('');

    if (!$sala->selectIdMusicaSala($conn, $_GET['sala'], $_GET['id_usuario'])) {
        http_response_code(403);
        echo json_encode(array(
            "acesso" => false,
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    http_response_code(200);
    echo json_encode(array('id' => $sala->getIdMusicaSala()));
    exit();
?>