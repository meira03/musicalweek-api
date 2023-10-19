<?php 

require_once("../../db/dbconexao.php");
require_once("../../classes/sala.php");
require_once("../../classes/avaliacao.php");

$sala = new Sala();
$musicas = new Avaliacao('','','');

try {
    
    echo json_encode(array(
        "top_musicas" => $musicas->topMusicas($conn),
        "salas_artista" => $sala->salasArtistasAtivas($conn)
    ), JSON_UNESCAPED_UNICODE);
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}