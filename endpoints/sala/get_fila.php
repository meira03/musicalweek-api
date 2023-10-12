<?php
    
    include("../../db/dbconexao.php");
    include("../../classes/sala.php");
    
    $sala = new Sala('');

    try {

        $fila = $sala->verifica($conn, $_GET['id_musica_sala']);

        if ($fila['id_usuario'] != $idUsuario) {
            http_response_code(403);
            echo json_encode(array(
                "id_musica_sala" => false,
                "descricao" => "o Id_musica_sala não pertence a esse usuario"
            ), JSON_UNESCAPED_UNICODE);
            exit();
        } elseif ($fila['id_sala'] != null) {
            http_response_code(301);
            echo json_encode(array(
                "id_sala" => $fila['id_sala'],
                "descricao" => "A sala já começou"
            ), JSON_UNESCAPED_UNICODE);
            exit();
        } else {
            http_response_code(202);
            echo json_encode(array(
                "id_musica" => $fila['id_musica'],
                "data_adicao_musica" => $fila['data_entrada'],
                "tempo_estimado" => $fila['tempo_estimado']
            ), JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $ex) {
        http_response_code(500);
        echo json_encode(array(
            "erro" => $ex->getMessage(),
        ), JSON_UNESCAPED_UNICODE);
    }
?>