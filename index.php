<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Accept-Ranges, Authorization, Referer");
header("Access-Control-Expose-Headers: Accept-Ranges, Content-Encoding, Content-Length, Content-Range");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
// header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

require_once 'app/classes/Tnx.php';
// Excecute framework
Tnx::fly();