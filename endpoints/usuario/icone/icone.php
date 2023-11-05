<?php

  $vars = json_decode(file_get_contents('php://input'), true);

  if (!isset($vars['icon'])) {
    $resposta = array();
    $resposta['icon'] = null;
    $resposta['descricao'] = "Icone não enviado";
    http_response_code(400);
    echo json_encode($resposta);
    exit();
  }
  
  include("../../../db/dbconexao.php");
  include("../../../classes/usuario.php");

  $usuario = new Usuario('','','','','');

  if(strlen($vars['icon']) > 1024)  {
    http_response_code(400);
    $erro = array();
    $erro['icon'] = false;
    $erro['descricao'] = "Icone fora do formato";
    echo json_encode($erro);
    exit();
  }
  
  try {

    if ($usuario->trocaIcone($conn, $idUsuario, $vars['icon'])){
      http_response_code(200);
      echo json_encode(
        array(
          'sucesso' => true,
        )
      );
    } else {
      http_response_code(403);
      echo json_encode(
        array(
          'icon' => true,
          'descricao' => "Icone já cadastrado"
        )
      );
    }
  } catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
  }
  
?>