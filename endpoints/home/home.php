<?php 

require_once("../../db/dbconexao.php");
require_once("../../classes/sala.php");

$sala = new Sala();

try {
    echo json_encode($sala->salasArtistasAtivas($conn), JSON_UNESCAPED_UNICODE);
} catch (PDOException $ex) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
}