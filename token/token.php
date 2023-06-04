<?php

    require '../vendor/autoload.php';

    use \Firebase\JWT\JWT;

    header('Access-Control-Allow-Origin: *');

    function gerarToken($userId) {
        $chaveSecreta = 'E6wK@8J#2%7zE$5C1V!3bN@6mQ#2p%8u!4L7rP!5T*5Q#n7r$!9&xU1mG@rC6#3qDvF5zNmX'; // Defina sua chave secreta

        $payload = array(
            'sub' => $userId,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24 * 30)
        );

        $token = JWT::encode($payload, $chaveSecreta, 'HS256');

        return $token;
    }
?>