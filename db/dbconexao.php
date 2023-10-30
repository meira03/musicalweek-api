<?php
try {
    $conn = new PDO("sqlsrv:server = tcp:musicalweek.database.windows.net,1433; Database = MusicalWeek_DB", "MusicalWeek", "x[ep428F.xID");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array(
        "erro" => $ex->getMessage(),
    ), JSON_UNESCAPED_UNICODE);
    exit;
}
?>