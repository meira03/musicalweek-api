<?php

  // header('Access-Control-Allow-Origin: https://musicalweek.vercel.app');
  header('Access-Control-Allow-Origin: http://localhost:3000');
  // header('Access-Control-Allow-Origin: https://localhost:3000');
  header('Access-Control-Allow-Headers: Content-Type, Authorization');
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Allow-Methods: POST');
  header('Content-Type: application/json; charset=utf-8');
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {    
    return 0;    
  }
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('POST' => false));
    exit();
  }

  $vars = json_decode(file_get_contents('php://input'), true);

  if (!isset($vars['nome']) || !isset($vars['nick']) || !isset($vars['data_nasc']) || !isset($vars['email']) || !isset($vars['senha'])) {
    $resposta = array();
    if (!isset($vars['nome'])) {
        $resposta['nome'] = null;
    }
    if (!isset($vars['nick'])) {
        $resposta['nick'] = null;
    }
    if (!isset($vars['data_nasc'])) {
        $resposta['data_nasc'] = null;
    }
    if (!isset($vars['email'])) {
        $resposta['email'] = null;
    }
    if (!isset($vars['senha'])) {
        $resposta['senha'] = null;
    }
    http_response_code(400);
    echo json_encode($resposta);
    exit();
  }
  
  include("../db/dbconexao.php");
  include("../classes/usuario.php");
  // include("../token/token.php");

  $cadastro = new Usuario($vars['nome'], $vars['nick'], $vars['data_nasc'], $vars['email'], $vars['senha']);
  $erro = array();

  if(!($cadastro->validarNome()))  $erro['nome'] = false;
  if(!($cadastro->validarNick()))  $erro['nick'] = false;
  if(!($cadastro->validarEmail())) $erro['email'] = false;
  if(!($cadastro->validarData()))  $erro['data_nasc'] = false;
  //if(!($cadastro->validarSenha())) $erro['senha'] = false;

  if($erro!= array()) {
    http_response_code(422);
    echo json_encode($erro);
    exit();
  }

  if(!($cadastro->verificaNick($conn)))  $erro['nick'] = true;
  if(!($cadastro->verificaEmail($conn))) $erro['email'] = true;

  if($erro!= array()) {
    http_response_code(409);
    echo json_encode($erro);
    exit();
  }
  
  try {
    $cadastro->cadastra($conn);
    // echo json_encode(array('token' => gerarToken($cadastro->getEmail())));
    echo json_encode(array('id_usuario' => $conn->lastInsertId()));
    http_response_code(200);
  } catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
  }
  
?>