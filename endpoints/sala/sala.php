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

    try {

        if (!$sala->selectIdMusicaSala($conn, $_GET['sala'], $_GET['id_usuario'])) {
            http_response_code(403);
            echo json_encode(array(
                "acesso" => false,
            ), JSON_UNESCAPED_UNICODE);
            exit();
        }
    
        if(!$sala->verifica($conn)){
            http_response_code(404);
            echo json_encode($sala->getFila($conn), JSON_UNESCAPED_UNICODE);
            exit();
        }
    
        echo json_encode($sala->getInfo($conn));

    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }
?>