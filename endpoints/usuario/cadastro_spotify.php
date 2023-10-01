<?php

    if (!isset($vars['nome']) || !isset($vars['nick']) || !isset($vars['data_nasc'])) {
        $resposta = array();
        $resposta['descricao'] = "";
        if (!isset($vars['nome'])) {
            $resposta['nome'] = null;
            $resposta['descricao'] .= "Nome não enviado. ";
        }
        if (!isset($vars['nick'])) {
            $resposta['nick'] = null;
            $resposta['descricao'] .= "Nick não enviado. ";
        }
        if (!isset($vars['data_nasc'])) {
            $resposta['data_nasc'] = null;
            $resposta['descricao'] .= "Data de nascimento não enviada. ";
        }
        http_response_code(400);
        echo json_encode($resposta);
        exit();
    }
    
    $ch = curl_init('https://api.spotify.com/v1/me?access_token=' . urlencode($vars['token_spotify']));

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $resposta = curl_exec($ch);

    if ($resposta === false) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => 'Erro na requisição do Spotify: '. curl_error($ch)
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    curl_close($ch);

    $resposta = json_decode($resposta, true);

    echo " ";

    if (isset($resposta['error'])) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => 'Erro do Spotify: ' . $resposta['error']['message']
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if (!isset($resposta['email'])) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => 'Erro na requisição do Spotify'
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    include("../../db/dbconexao.php");
    include("../../classes/usuario.php");
    include("../../token/gera/token.php");

    $cadastro = new Usuario($vars['nome'], $vars['nick'], $vars['data_nasc'], $resposta['email'], '');
    $erro = array();

    if(!($cadastro->validarNome()))  $erro['nome'] = false;
    if(!($cadastro->validarNick()))  $erro['nick'] = false;
    if(!($cadastro->validarData()))  $erro['data_nasc'] = false;

    if($erro!= array()) {
        http_response_code(422);
        $erro['descricao'] = "Variável(s) fora do formato";
        echo json_encode($erro);
        exit();
    }

    if(!($cadastro->verificaNick($conn)))  $erro['nick'] = true;
    if(!($cadastro->verificaEmail($conn))) $erro['email'] = true;

    if($erro!= array()) {
        http_response_code(409);
        $erro['descricao'] = "Variável(s) já está cadastrada";
        echo json_encode($erro);
        exit();
    }

    try {
        $cadastro->cadastraSpotify($conn);
        http_response_code(200);
        echo json_encode(
          array(
            'token' => gerarToken($conn->lastInsertId()),
            'nick' => $cadastro->getNick(),
            'plano' => 0
          )
        );
      } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }