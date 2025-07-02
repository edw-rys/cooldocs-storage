<?php
include_once SERVICES."BooksService.php";
include_once SERVICES."StorageService.php";
include_once CLASSES."Controller.php";
include_once SERVICES."SizeBooksService.php";

use Classes\Controller;
use Service\BooksService;
use Service\StorageService;
use Service\SizeBooksService;

class BooksController extends Controller{

    private BooksService $booksSrv;
    private SizeBooksService $sizeBookSrv;

    public function __construct() {
        $this->booksSrv = new BooksService;
        $this->sizeBookSrv =  new SizeBooksService;
        parent::__construct();
    }

    public function show($id, $part_id = null) {
        $jsonData = $this->booksSrv->getFileRoute($id, $part_id, $this->_request, ['checkSession'=>!isset($_SERVER['HTTP_RANGE'])]);
        if ($jsonData->book->disk_storage == 's3') {
            return $this->showS3($jsonData,$id, $part_id);
        }
        return $this->showLocal($jsonData,$id, $part_id);
    }

    public function showLocal($jsonData, $id, $part_id = null)
    {
        // confición momentánea
        $file = $jsonData->path;
        if(!file_exists($file)){
            responseJson(['message'=> 'Libro no encontrado'], 404);
            exit;
        }

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
        header('Content-Disposition: inline; filename="' . "libro.pdf" . '"');

        header('Accept-Ranges: bytes');
        print($data);
    }

    /*public function showStorage($jsonData)
    {*/
    public function showS3($jsonData, $id, $part_id = null)
    {
        /*if (1967859040 != $id) {
            return $this->showx($id, $part_id);
        }*/
        // $jsonData = $this->booksSrv->getFileRoute($id, $part_id, $this->_request, ['checkSession'=>!isset($_SERVER['HTTP_RANGE'])]);
        // Validar ID y obtener clave del archivo en S3

        $s3Reader = StorageService::getInstance();
        $s3Key = $jsonData->book->path_file;
        if(!$s3Reader->exists($s3Key)){
            responseJson(['message'=> 'Libro '.$s3Key.' no encontrado'], 404);
            exit;
        }

        // Obtener metadatos del archivo
        //$fileSize = $s3Reader->getFileSize($s3Key);
        $fileSize = $this->sizeBookSrv->getFileSize($id, $part_id, $s3Key);
        //$contentType = $s3Reader->getContentType($s3Key);
        // Manejar rangos de bytes
        $offset = 0;
        $length = $fileSize;
        $partialContent = false;

        if (isset($_SERVER['HTTP_RANGE'])) {
            $partialContent = true;
            preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
            $offset = intval($matches[1]);
            $length = intval($matches[2]) - $offset + 1 ;
            //if (!preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches)) {
            //    header('HTTP/1.1 416 Requested Range Not Satisfiable');
            //    header('Content-Range: bytes */' . $fileSize);
                /*exit;
            }

            $offset = max(0, intval($matches[1]));
            $end = isset($matches[2]) ? min(intval($matches[2]), $fileSize - 1) : $fileSize - 1;
            
            if ($offset > $end) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');*/
            //    header('Content-Range: bytes */' . $fileSize);
            //    exit;
            //}

            //$length = $end - $offset + 1;
            //$partialContent = true;
        }
        $data = $s3Reader->getByteRange($s3Key, $offset, $offset + $length - 1);

        if ( $partialContent ) {
            // output the right headers for partial content
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes ' . $offset . '-' . ($offset + $length) . '/' . $fileSize);
            header('Content-Length: ' . $length);
        }else {
            header('Content-Length: ' . $fileSize);
        }

                
        // output the regular HTTP headers
        header('Accept-Ranges: bytes');
        header('Content-Type: application/pdf');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: inline; filename="' . "libro.pdf" . '"');

        header('Accept-Ranges: bytes');
        print($data);
    }
    public function checkFlipBook(){
        echo '9661187065405406358209263650625364492668781751488614';
    }
}
