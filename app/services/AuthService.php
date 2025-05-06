<?php
namespace Service;
include_once MODELS."UserModel.php";

use App\Models\UserModel;
use DomainException;
use Firebase\JWT\JWT;
use UnexpectedValueException;

class AuthService {
    public $key;
    public function __construct(){
        $this->key = 'KAYBNFGA_TOKEN_TNX_GENERATE_RANDOM';
    }
    /**
     * Busca al usuario
     * Verifica las credenciales y crea un token de acceso
     * Devuelve los datos
     * @param $email
     * @param $password
     */
    public function signup($email, $password) {
        $usermodel = new UserModel;
        // Buscar si existe el usuario con email
        $user = $usermodel->find([
            'condition' => 'AND email=:email and type=:type',
            'params'    => [
                'email'    => $email,
                'type'     => 'admin'
            ],
            'fetch'     => 'ONE',
            'fetch_type'=> 'OBJ'
        ]);
        if(!$user){
            return [
                'status'    => 'error',
                'code'      => 404,
                'message'   => 'Correo o contraseña incorrecta',
            ];
        }
        if (!password_verify($password, $user->password)) {
            return [
                'status'    => 'error',
                'code'      => 404,
                'message'   => 'Correo o contraseña incorrecta.',
            ];
        }

        $token = array(
            'sub' => $user->id,
            'user'=> $user,
            'iat'=> time(),
            'exp'=>time()+(7*24*60*60)
        );
           
        $jwt = JWT::encode($token, $this->key, 'HS256');
        // $decode = JWT::decode($jwt,$this->key, ['HS256']);
        // Devolver los datos decodificados o el token, en función de un parámetro
        return [
            "status"=>"success",
            'data'  => [
                "access_token"=>$jwt,
                'user'=> $user
            ],
            'message'   => 'Login existoso',
            "code"=>200,
        ];
    }
    public function checkToken($jwt , $getIdentity = false){
        $auth = false;
        try{
            $jwt = str_replace('"','',$jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
            // die();
        }catch(UnexpectedValueException $e){
            $auth= false;
        }catch(DomainException $ex){
            $auth= false;
        }
        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            // var_dump($decoded->sub);die();
            $auth = true;
        }else{
            $auth = false;
        }
        if($getIdentity)
            return $decoded;
        return $auth;
    }
    /**
     * Verifica la autenticación y en caso de no tener devuelve 401
     */
    public function abortNoAuth(){
        
        $headers = apache_request_headers();
        if(!isset($headers['Authorization']) || $headers['Authorization'] == null){
            responseJson(['message' =>'no autenticado.'], 401);
            die();
        }
        $list = explode(' ', $headers['Authorization']);
        if(!isset($list[1])){
            responseJson(['message' =>'no autenticado',], 401);
            die();
        }
        $result = $this->checkToken($list[1]);
        if(!$result){
            responseJson(['message' =>'no autenticado'], 401);
            die();
        }
    }

}