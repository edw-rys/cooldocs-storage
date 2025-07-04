<?php
namespace Service;

include_once SERVICES."RedisService.php";

class BooksService {
    private $keyBook = 'pd__red_book__';
    public function findRedis($id, $part_id = '') {
        RedisService::instance();
        $element = RedisService::get($this->keyBook. $id.'_'.($part_id??''));
        if($element == null){
            return null;
        }
        return json_decode(json_decode($element));
    }

    public function setRedis($id, $data, $part_id = '') {
        RedisService::instance();
        return RedisService::set($this->keyBook. $id. '_'.($part_id??''), json_encode($data));
    }


    public function getFileRoute($id, $part_id = null, $request =[],$_params = [])
    {
        if (isset($_params['checkSession']) && !$_params['checkSession']) {
            $book = $this->findRedis($id, ($part_id??''));
            if ($book != null && isset($book->book->disk_storage)) {
                return $book;
            }
        }
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

        // $url = 'https://'. $subdomain.'.holguinpuentedigital.com/api/book/get-api-data/'. $id ;
        $url = $_ENV['API_BACK_EXTERNAL_PD']. '/api/book/get-api-data/'. $id ;

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
        if (!isset($jsonData->path)) {
            responseJson( [
                'status'    => 'error',
                'code'      => '403',
                'message'   => $jsonData->message
            ], 400);die();
        }
        $this->setRedis($id, json_encode($jsonData), $part_id);

        return $jsonData;
        /*$file = $jsonData->path;
        if(!file_exists($file)){
            responseJson(['message'=> 'Libro no encontrado'], 404);
            exit;
        }
        return $file;*/
    }
}