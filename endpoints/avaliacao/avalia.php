<?php

    $vars = json_decode(file_get_contents('php://input'), true);

    if(!isset($vars['id_musica_sala']) || !isset($vars['nota'])) {
        $resposta = array();
        $resposta['descricao'] = "";
        if (!isset($vars['id_musica_sala'])){
            $resposta['id_musica_sala'] = null;
            $resposta['descricao'] .= "id_musica_sala não enviado. ";
        }
        if (!isset($vars['nota'])){
            $resposta['nota'] = null;
            $resposta['descricao'] .= "Nota não enviada";
        }
        http_response_code(400);
        echo json_encode($resposta);
        exit();
    }

    if(!is_int($vars['id_musica_sala'])) {
        $response = array();
        $response['id_musica_sala'] = false;
        $response['descricao'] = "Id da musica sala deve ser é inteiro, e não " . gettype($vars['id_musica_sala']);
        http_response_code(400);
        echo json_encode($response);
        exit();
    }

    if(!is_int($vars['nota'])) {
        $response = array();
        $response['nota'] = false;
        $response['descricao'] = "Nota deve ser é inteira e não " . gettype($vars['nota']);
        http_response_code(400);
        echo json_encode($response);
        exit();
    }

    if($vars['nota'] < 1 || $vars['nota'] > 100) {
        $response = array();
        $response['nota'] = false;
        $response['descricao'] = "Nota deve ser entre 0 e 100";
        http_response_code(400);
        echo json_encode($response);
        exit();
    }

    include("../../db/dbconexao.php");
    include("../../classes/avaliacao.php");

    $avaliacao = new Avaliacao($idUsuario, $vars['id_musica_sala'], $vars['nota']);
    
    try {

        $codigo = $avaliacao->insere($conn);

        if ($codigo == 1) {
            $avaliacaoMedia = $avaliacao->avaliacaoMedia($conn);
            http_response_code(200);
            echo json_encode(array(
                "sucesso" => true,
                "avaliacao_media" => $avaliacaoMedia
            ), JSON_UNESCAPED_UNICODE);
        } elseif ($codigo == 2) {
            http_response_code(403);
            echo json_encode(array(
                "id_usuario" => false,
                "erro" => "Usuario não esta na sala"
            ), JSON_UNESCAPED_UNICODE);
            exit();
        } elseif ($codigo == 3) {
            http_response_code(403);
            echo json_encode(array(
                "finalizada" => true,
                "erro" => "Sala já acabou"
            ), JSON_UNESCAPED_UNICODE);
            exit();
        } elseif ($codigo == 4) {
            http_response_code(409);
            echo json_encode(array(
                "disponivel" => false,
                "erro" => "Música ainda nao disponível para avaliação",
            ), JSON_UNESCAPED_UNICODE);
        } elseif ($codigo == 0) {
            http_response_code(409);
            echo json_encode(array(
                "id_musica_sala" => false,
                "erro" => "Avaliação ja registrada",
            ), JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }
?>