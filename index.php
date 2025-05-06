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
$FILE = 'autoload.php';
if(!is_file('vendor/'.$FILE)){
    die(sprintf("Archivo [vendor/".$FILE."] no se encuentra, extrictamente requerido para el funcionamiento."));
}
require_once 'vendor/'.$FILE;
\Dotenv\Dotenv::createImmutable(__DIR__)->load();
// Excecute framework
Tnx::fly();