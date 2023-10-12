<?php
    
    require '../../../vendor/autoload.php';
    
    use \Firebase\JWT\JWT;
    use \Firebase\JWT\Key;
    
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Authorization, Content-Type, x-xsrf-token, x_csrftoken, Cache-Control, X-Requested-With');
    
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
    
    if ($token == '') {
        http_response_code(401);
        echo json_encode(array(
            "Token" => null,
            "descricao" => 'Token jwt não enviado',
        ), JSON_UNESCAPED_UNICODE);
        die();
    }

    try {
        $decoded = JWT::decode($token, new Key('E6wK@8J#2%7z0E$5C1V!3bN@6mQ#2p%8u!4L7rP!5T*5Q#n7r$!9&xU1mG@rC6#3qDvF5zNmX', 'HS256'));
        $idUsuario = $decoded->sub;
    } catch (Throwable $e) {
        http_response_code(401);
        echo json_encode(array(
            "Token" => false,
            "descricao" => 'Token jwt inválido',
        ), JSON_UNESCAPED_UNICODE);
        die();
    }
?>