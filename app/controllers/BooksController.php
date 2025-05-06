<?php
include_once SERVICES."BooksService.php";
include_once CLASSES."Controller.php";

use Classes\Controller;
use Service\BooksService;

class BooksController extends Controller{

    private BooksService $booksSrv;


    public function __construct() {
        $this->booksSrv = new BooksService;
        parent::__construct();
    }


    public function show($id, $part_id = null)
    {
        // Validate id por api

        // $id = 42;
        $file = $this->booksSrv->getFileRoute($id, $part_id, $this->_request, ['checkSession'=>!isset($_SERVER['HTTP_RANGE'])]);
        $filesize = filesize($file);

        $offset = 0;
        $length = $filesize;
        $mostrar = true;

        if ( isset($_SERVER['HTTP_RANGE']) ) {
            // if the HTTP_RANGE header is set we're dealing with partial content
        
            $partialContent = true;
            // find the requested range
            // this might be too simplistic, apparently the client can request
            // multiple ranges, which can become pretty complex, so ignore it for now
            preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
        
            $offset = intval($matches[1]);
            $length = intval($matches[2]) - $offset + 1 ;
        } else {
            $partialContent = false;
            // die();
        }
        $file = fopen($file, 'r');
        fseek($file, $offset);
        $data = fread($file, $length);
        fclose($file);

        if ( $partialContent ) {
            // output the right headers for partial content
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes ' . $offset . '-' . ($offset + $length) . '/' . $filesize);
            header('Content-Length: ' . $length);
        }else {
            header('Content-Length: ' . $filesize);
        }

                
        // output the regular HTTP headers
        header('Accept-Ranges: bytes');
        header('Content-Type: application/pdf');

        header('Content-Transfer-Encoding: binary');
        // if($libre == "0"){
            header('Content-Disposition: inline; filename="' . "libro.pdf" . '"');
        // }else{
            // header('Content-Disposition: attachment; filename="' . "libro.pdf" . '"');
        // }

        header('Accept-Ranges: bytes');
        //header('Access-Control-Allow-Origin: *');header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        print($data);

    }    
}
