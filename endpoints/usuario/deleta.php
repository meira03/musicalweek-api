<?php

include("../../db/dbconexao.php");
include("../../classes/usuario.php");

$usuario = new Usuario('','','','','');

try {
    if ($usuario->delete($conn, $idUsuario)) {
        http_response_code(200);
        echo json_encode(array('sucesso' => true));
    } else {
        http_response_code(404);
        echo json_encode(
            array(
                'sucesso' => false,
                'descricao' => "Usuário não existe"
            )
        );
    }
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}