<?php

    if(!isset($vars['nick'])) {
        http_response_code(400);
        echo json_encode(array(
            "nick" => null,
            "descricao" => "Nick não enviado"
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if(!isset($vars['data_nasc'])) {
        http_response_code(400);
        echo json_encode(array(
            "data_nasc" => null,
            "descricao" => "Data de nascimento não enviada"
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . urlencode($vars['token_google']));

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $resposta = curl_exec($ch);

    if ($resposta === false) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => 'Erro na requisição do Google: '. curl_error($ch)
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    curl_close($ch);

    $resposta = json_decode($resposta, true);

    echo " ";

    if (isset($resposta['error_description'])) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => 'Erro do Google: ' . $resposta['error_description']
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    if (!isset($resposta['email']) || !isset($resposta['name'])) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => 'Erro na requisição do Google'
        ), JSON_UNESCAPED_UNICODE);
        exit();
    }

    include("../../db/dbconexao.php");
    include("../../classes/usuario.php");
    include("../../token/gera/token.php");

    $cadastro = new Usuario($resposta['name'], $vars['nick'], $vars['data_nasc'], $resposta['email'], '');
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
        $cadastro->cadastraGoogle($conn);
        http_response_code(200);
        echo json_encode(
          array(
            'token' => gerarToken($conn->lastInsertId()),
            'nick' => $cadastro->getNick()
          )
        );
      } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }