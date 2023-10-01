<?php

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

    $usuario = new Usuario('','','',$resposta['email'],'');

    $id = $usuario->getid($conn);

    if ($id == null) {
        http_response_code(404);
        echo json_encode(
            array(
                'cadastro' => false,
                'descricao' => "Email não cadastrado"
            )
        );
        exit();
    }

    include("../../token/gera/token.php");

    $select = $usuario->selectLogin($conn);
    http_response_code(200);
    echo json_encode(
        array(
            'token' => gerarToken($select['id_usuario']),
            'nick' => $select['username'],
            'plano' => $select['tipo_plano']
        )
    );
    exit();
?>
