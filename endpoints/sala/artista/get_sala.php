<?php
    
    if(!isset($_GET['id_sala'])) {
        $response = array();
        $response['id_sala'] = null;
        $response['descricao'] = "Id da sala não Enviado";
        http_response_code(400);
        echo json_encode($response);
        exit();
    }
    
    require_once("../../../db/dbconexao.php");
    require_once("../../../classes/sala.php");
    
    $sala = new Sala();

    try {

        $idArtista = $sala->getArtista($conn, $_GET['id_sala'], $idUsuario);

        if ($idArtista === null) {
            http_response_code(404);
            echo json_encode(array(
                "id_sala" => false,
                "descricao" => "Id Sala errado",
            ), JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($idArtista == $idUsuario) {
            date_default_timezone_set('America/Sao_Paulo');
            echo json_encode($sala->getSalaArtistaTotal($conn, $_GET['id_sala'], $idUsuario));
        } else {
            echo json_encode($sala->getSalaArtista($conn, $_GET['id_sala'], $idUsuario));
        }
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }
?>