<?php
namespace Service;


class BooksService {

    public function getFileRoute($id, $part_id = null)
    {
        $subdomain = 'admindev';
        if (!(!isset($_REQUEST['subdomain']) || $_REQUEST['subdomain'] == null)) {
            $subdomain = $_REQUEST['subdomain'];
        }
        $url = 'https://'. $subdomain.'.holguinpuentedigital.com/api/book/get-api-data/'. $id;
        if($part_id != null){
            $url .='/'.$part_id;
        }
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $data = curl_exec($curl);
        curl_close($curl);

        if(!$data){
            responseJson(  [
                'status'    => 'error',
                'code'      => '400',
                'message'   => 'End point no es válido'
            ], 400);die();
        };

        $jsonData = json_decode($data);

        if(!$jsonData){
            responseJson( [
                'status'    => 'error',
                'code'      => '400',
                'message'   => 'Error al cargar los libro'
            ], 400);die();
        }
        $file = $jsonData->path;

        // $file = 'C:/xampp/htdocs/CoolDocs/storage/app/books/4/book/6eoQXeIx3t39mhNSigD8VF7MzCXXAefzZ1bamC3E.pdf';
        // $file = '/webapps/apps/puente-digital/backend/storage/app/books/73/book/1657040558.pdf';
        
        if(!file_exists($file)){
            responseJson(['message'=> 'Libro no encontrado'], 404);
            exit;
        }
        return $file;

    }
}