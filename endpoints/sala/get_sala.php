<?php
    
    if(!isset($_GET['id_sala'])) {
        $response = array();
        $response['id_sala'] = null;
        $response['descricao'] = "Id da sala não Enviado";
        http_response_code(400);
        echo json_encode($response);
        exit();
    }
    
    include("../../db/dbconexao.php");
    include("../../classes/sala.php");
    
    $sala = new Sala('');

    try {

        $idMusicaSala = $sala->selectIdMusicaSala($conn, $_GET['id_sala'], $idUsuario);

        if ($idMusicaSala == null) {
            http_response_code(403);
            echo json_encode(array(
                "id_sala" => false,
                "descricao" => "Usuario não está na sala",
            ), JSON_UNESCAPED_UNICODE);
            exit();
        }

        echo json_encode($sala->getInfo($conn, $idMusicaSala, $_GET['id_sala'], $idUsuario));

    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }
?>