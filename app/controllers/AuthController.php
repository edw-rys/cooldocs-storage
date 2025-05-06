<?php


include_once CLASSES."Controller.php";
include_once SERVICES."AuthService.php";

use Classes\Controller;
use Service\AuthService;


class AuthController extends Controller{
    private $authSrv;
    public function __construct() {
        $this->authSrv = new AuthService();
        parent::__construct();
    }
    /**
     * Autenticación de usuario
     */
    public function login(){
        $email=isset($this->_request['email'])?strtolower($this->_request['email']):'';
        $password=isset($this->_request['password'])?$this->_request['password']:'';

        $data = $this->authSrv->signup($email,$password);
        return responseJson($data, $data['code']);

        
        // View::render("login", $data);        
    }

    public function config($param=null){
        if(is_null($param)){
            $data =[
                "title" => "Configuración",
                "user"  => $this->userDAO->getUserById($_SESSION["ID_USER"])
            ];
            include_once COMPONENTS."user/config.php";
        }elseif ($param="checkusername") {
            $username = isset($_REQUEST["username"])?$_REQUEST["username"]:null;
            if(!is_null($username)){
                $verify=$this->userDAO->verifyUserNotRepeat(strtolower($username),isset($_SESSION['ID_USER'])?$_SESSION['ID_USER']:null);
                if(empty($verify)){
                    $data = [
                        "status" => "success",
                        "code"   => 200,
                        "message"=> ""
                    ];
                }else{
                    $data = [
                        "status" => "error",
                        "code"   => 400,
                        "message"=> "Nombre de usuario existente"
                    ]; 
                }
            }else{
                $data = [
                    "status" => "error",
                    "code"   => 400,
                    "message"=> "Error"
                ];
            }
            echo json_encode($data);
        }
    }

    public function signin($value="login"){
        if(!isset($_SESSION["USER"])){
            if($value=="login")
                require_once COMPONENTS."user/loginComponent.php";
            elseif ($value=="signup") {
                require_once COMPONENTS."user/signupComponent.php";                
            }
        }
    }

    public function update(){
        $status = ValidateField::validateUser($_POST,$_SESSION['ID_USER']);
        if($status["status"]=="success"){
            unset($_POST['password_confirm']);
            unset($_POST['password_current']);
            // actualizar nombre de usuario
            $username = isset($_REQUEST["username"])?$_REQUEST["username"]:null;
            if(isset($_POST["password"])){
                $_POST["password"] = password_hash($_POST["password"], PASSWORD_BCRYPT , ['cost'=>10]);
            }
            if(!is_null($username)){
                $verify=$this->userDAO->verifyUserNotRepeat(strtolower($username),$_SESSION['ID_USER']);
                if(empty($verify)){
                    $res = $this->userDAO->update($_POST,$_SESSION['ID_USER']);
                    if($res){
                        $data=[
                            "status"=>"success",
                            "code"  =>200,
                            "message"=>"datos actualizados"
                        ];
                        $user = $this->userDAO->getUserById($_SESSION["ID_USER"]);
                        if(!is_null($user) && !empty($user) ){
                            $_SESSION["USER"] = serialize($user);
                        }
                    }else{
                        $data=[
                            "status"=>"error",
                            "code"  =>400,
                            "message"=>"error al actualizar"
                        ];
                    }
                }else{
                    $data = [
                        "status" => "error",
                        "code"   => 400,
                        "message"=> "Nombre de usuario existente"
                    ]; 
                }
            }else{
                if(!empty($_FILES)){
                    $data = saveImage('image',ROUTEPHOTO);
                    if($data["status"]=="success"){
                        $_POST["url_photo"] = $data["url"];
                        $res = $this->userDAO->update($_POST,$_SESSION['ID_USER']);
                        if($res){
                            $data=[
                                "status"=>"success",
                                "code"  =>200,
                                "message"=>"datos actualizados"
                            ];
                            $user = $this->userDAO->getUserById($_SESSION["ID_USER"]);
                            if(!is_null($user) && !empty($user) ){
                                $_SESSION["USER"] = serialize($user);
                            }
                        }else{
                            $data=[
                                "status"=>"error",
                                "code"  =>400,
                                "message"=>"error al actualizar"
                            ];
                        }
                    }
                }else{
                    $res = $this->userDAO->update($_POST,$_SESSION['ID_USER']);
                    if($res){
                        $data=[
                            "status"=>"success",
                            "code"  =>200,
                            "message"=>"datos actualizados"
                        ];
                        $user = $this->userDAO->getUserById($_SESSION["ID_USER"]);
                        if(!is_null($user) && !empty($user) ){
                            $_SESSION["USER"] = serialize($user);
                        }
                    }else{
                        $data=[
                            "status"=>"error",
                            "code"  =>400,
                            "message"=>"error al actualizar"
                        ];
                    }
                }

            }
        }else{
            $data=[
                "status"=>"error",
                "code"  =>400,
                "errors"=>$status["errors"]
            ];
        }
        echo json_encode($data);
    }
    public function delete(){
        die("No disponible");
        $deleteUser = $this->userDAO->delete($_SESSION["ID_USER"]);
        if($deleteUser){
            echo json_encode([
                "status" => "success",
                "code"   => 200,
                "message"=> "Usuario eliminado"
            ]);
        }else{
            echo json_encode([
                "status" => "error",
                "code"   => 400,
                "message"=> "Error al eliminar"
            ]);
        }
    }    
    public function disable(){
        $password = isset($_POST["password"])?$_POST["password"]:'';
        $check = $this->userDAO->checkPassword($_SESSION["ID_USER"] , $_POST['password']);
        if($check){
            $disableUser = $this->userDAO->disable($_SESSION["ID_USER"]);
            if($disableUser){
                $data=[
                    "status"=>"success",
                    "code"  =>200,
                    "message"=>"Usuario deshabilitado."
                ];
            }else{
                $data=[
                    "status"=>"error",
                    "code"  =>400,
                    "message"=>"Error al deshabilitar cuenta."
                ];
            }
        }else{
            $data=[
                "status"=>"error",
                "code"  =>400,
                "message"=>"Contraseña incorrecta!"
            ];
        }
        echo json_encode($data);
    }

    public function getMyData(){
        $user = $this->userDAO->getUserById($_SESSION["ID_USER"]);
        // Profile photo
        if(!is_null($user)){
            if(empty($user->geturl_photo())){
                if($user->getid_gender()==1){
                    $user->seturl_photo(IMAGES.'pictures/upload/default/male.png');
                }else{
                    $user->seturl_photo(IMAGES.'pictures/upload/default/female.png');
                }
            }else{
                $user->seturl_photo(URL.$user->geturl_photo() );
            }
            $user = dismount($user);
            if($user["id_gender"]==1){
                $user["url_image_gender"]= IMAGES.'icons/male.svg';
            }else{
                $user["url_image_gender"]= IMAGES.'icons/female.svg';
            }
            echo json_encode($user);
        }else{
            echo json_encode(["status"=>"error","code"=>"404"]);
        }
    }
    public function search($value="",$window=null) {
        $users = $this->userDAO->search($value);
        $data =[
            "title"=>"Buscar usuario",
            "users"=>$users
        ];
        if(!is_null($window)){
            View::render("search",$data);
        }else{
            include_once COMPONENTS."user/panelSearch.php";
        }
    }
}
