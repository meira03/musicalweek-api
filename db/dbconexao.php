<?php

// $serverName = "localhost";
// $database = "musicalweek";

// try {
//     $conn = new PDO("sqlsrv:Server=$serverName;Database=$database;ConnectionPooling=0");
//     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die("Erro na conexão: " . $e->getMessage());
// }

try {
    $conn = new PDO("sqlsrv:server = tcp:musicalweek.database.windows.net,1433; Database = MusicalWeek_DB", "MusicalWeek", "x[ep428F.xID");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    print("Error connecting to SQL Server.");
    die(print_r($e));
    $conn = null;
}

$connectionInfo = array("UID" => "MusicalWeek", "pwd" => "x[ep428F.xID", "Database" => "MusicalWeek_DB", "LoginTimeout" => 300, "Encrypt" => 1, "TrustServerCertificate" => 0);
$serverName = "tcp:musicalweek.database.windows.net,1433";
// $conn = sqlsrv_connect($serverName, $connectionInfo);
?>