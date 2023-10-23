<?php
    
    if(!isset($_GET['id_sala']) || !isset($_GET['posicao'])) {
        $resposta = array();
        $resposta['descricao'] = "";
        if (!isset($_GET['id_sala'])){
            $resposta['id_sala'] = null;
            $resposta['descricao'] .= "id_sala não enviado. ";
        }
        if (!isset($_GET['posicao'])){
            $resposta['posicao'] = null;
            $resposta['descricao'] .= "posicao não enviada";
        }
        http_response_code(400);
        echo json_encode($resposta);
        exit();
    }
    
    require_once("../../../db/dbconexao.php");
    require_once("../../../classes/sala.php");
    
    $sala = new Sala('');

    try {

        $sala = $sala->getMusica($conn, $_GET['id_sala'], $idUsuario, $_GET['posicao']);

        if (isset($sala['codigo'])) {
            if ($sala['codigo'] == 0) {
                http_response_code(403);
                echo json_encode(array(
                    "id_sala" => false,
                    "descricao" => "Usuario não está na sala",
                ), JSON_UNESCAPED_UNICODE);
                exit();
            }
            if ($sala['codigo'] == 1) {
                http_response_code(403);
                echo json_encode(array(
                    "tipo_sala" => false,
                    "descricao" => "Sala não padrão",
                ), JSON_UNESCAPED_UNICODE);
                exit();
            }
            if ($sala['codigo'] == 2) {
                http_response_code(403);
                echo json_encode(array(
                    "historico" => false,
                    "descricao" => "Sala além do historico",
                ), JSON_UNESCAPED_UNICODE);
                exit();
            }
            if ($sala['codigo'] == 3) {
                http_response_code(403);
                echo json_encode(array(
                    "posicao" => false,
                    "descricao" => "Musica não disponível",
                ), JSON_UNESCAPED_UNICODE);
                exit();
            }
            exit();
        } else {
            http_response_code(200);
            echo json_encode($sala);
        }
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }
?>