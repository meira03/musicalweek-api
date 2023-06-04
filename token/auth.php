<?php
    
    require '../vendor/autoload.php';

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Authorization, Content-Type, x-xsrf-token, x_csrftoken, Cache-Control, X-Requested-With');
    
    $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
    
    try {
        $decoded = JWT::decode($token, new Key('E6wK@8J#2%7zE$5C1V!3bN@6mQ#2p%8u!4L7rP!5T*5Q#n7r$!9&xU1mG@rC6#3qDvF5zNmX', 'HS256'));
        echo json_encode($decoded);
    } catch (Throwable $e) {
        if ($e->getMessage() === 'Expired token') {
            http_response_code(401);
            die('EXPIRED');
        }
    }
?>