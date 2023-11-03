<?php

  if (!isset($vars['nome']) || !isset($vars['nick']) || !isset($vars['data_nasc']) || !isset($vars['icon'])) {
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
    if (!isset($vars['icon'])) {
      $resposta['icon'] = null;
      $resposta['descricao'] .= "Icone não enviado.";
    }
    http_response_code(400);
    echo json_encode($resposta);
    exit();
  }
  
  include("../../db/dbconexao.php");
  include("../../classes/usuario.php");

  $usuario = new Usuario($vars['nome'], $vars['nick'], $vars['data_nasc'], '', '');
  $erro = array();

  if(!($usuario->validarNome()))  $erro['nome'] = false;
  if(!($usuario->validarNick()))  $erro['nick'] = false;
  if(!($usuario->validarData()))  $erro['data_nasc'] = false;
  if(strlen($vars['icon']) > 1024)  $erro['icon'] = false;

  if($erro!= array()) {
    http_response_code(400);
    $erro['descricao'] = "Variável(s) fora do formato";
    echo json_encode($erro);
    exit();
  }

  if ($usuario->getUsername($conn, $idUsuario) != $vars['nick']){
      if(!($usuario->verificaNick($conn))) {
        http_response_code(409);
        $erro['nick'] = true;
        $erro['descricao'] = "Nick já está cadastrado";
        echo json_encode($erro);
        exit();
    }
  }
  
  try {
    $usuario->atualiza($conn, $idUsuario, $vars['icon']);
    http_response_code(200);
    echo json_encode(
      array(
        'sucesso' => true,
      )
    );
  } catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
  }
  
?>