<?php

  if (!isset($vars['nome']) || !isset($vars['nick']) || !isset($vars['data_nasc']) || !isset($vars['email']) || !isset($vars['senha'])) {
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
    if (!isset($vars['email'])) {
        $resposta['email'] = null;
        $resposta['descricao'] .= "Email não enviado. ";
    }
    if (!isset($vars['senha'])) {
        $resposta['senha'] = null;
        $resposta['descricao'] .= "Senha não enviado. ";
    }
    http_response_code(400);
    echo json_encode($resposta);
    exit();
  }
  
  include("../../db/dbconexao.php");
  include("../../classes/usuario.php");
  include("../../token/gera/token.php");

  $cadastro = new Usuario($vars['nome'], $vars['nick'], $vars['data_nasc'], $vars['email'], $vars['senha']);
  $erro = array();

  if(!($cadastro->validarNome()))  $erro['nome'] = false;
  if(!($cadastro->validarNick()))  $erro['nick'] = false;
  if(!($cadastro->validarEmail())) $erro['email'] = false;
  if(!($cadastro->validarData()))  $erro['data_nasc'] = false;
  if(!($cadastro->validarSenha())) $erro['senha'] = false;

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
    $cadastro->cadastra($conn);
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
  
?>