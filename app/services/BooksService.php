<?php
namespace Service;


class BooksService {

    public function getFileRoute($id, $part_id = null, $_params = [])
    {
        $subdomain = 'admindev';
        if (isset($_REQUEST['subdomain']) && $_REQUEST['subdomain'] != null){
            $subdomain = $_REQUEST['subdomain'];
        }
        $headers = apache_request_headers();
        if(!isset($headers['Authorization']) || $headers['Authorization'] == null){
            responseJson(['message' =>'no autenticado.'], 401);
            die();
        }
        
        $authorization = $headers['Authorization'];

        $url = 'https://'. $subdomain.'.holguinpuentedigital.com/api/book/get-api-data/'. $id ;

        if($part_id != null){
            $url .='/'.$part_id;
        }
        if (isset($_REQUEST['token_refrs']) && $_REQUEST['token_refrs'] != null){
            $url .= '?token_refrs='.$_REQUEST['token_refrs'];
        }

        // echo $url;die;
        $curl = curl_init();
        
        $headers = [
            // 'Content-type: application/json',
            'Accept: application/json',
            'Authorization: '.$authorization,
        ];

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, $headers);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($curl);

        $sentHeaders = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($data, 0, $headerSize);
        $body = substr($data, $headerSize);
        // echo htmlspecialchars($data);die();
        // echo '<pre>';var_dump($body);die();
        curl_close($curl);
        if(!$body){
            responseJson(  [
                'status'    => 'error',
                'code'      => '400',
                'message'   => 'End point no es válido'
            ], 400);die();
        };

        $jsonData = json_decode($body);

        if(!$jsonData){
            responseJson( [
                'status'    => 'error',
                'code'      => '400',
                'message'   => 'Error al cargar los libro',
                // 'data'  => $data
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