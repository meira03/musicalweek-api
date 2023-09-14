<?php

header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');

echo $_SERVER['REQUEST_METHOD'];
die();