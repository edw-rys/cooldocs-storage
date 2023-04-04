<?php 
include_once SERVICES."AuthService.php";
include_once SERVICES."UserService.php";
include_once CLASSES."Controller.php";

use Service\UserService;
use Classes\Controller;
use Service\AuthService;

class UserController extends Controller{
    private $userSrv;
    private $authSrv;
    public function __construct(){
        $this->userSrv = new UserService();
        $this->authSrv = new AuthService();
        parent::__construct();
        $this->authSrv->abortNoAuth();
    }
    public function index(){
        if($this->get_request_method() == 'OPTIONS'){
            return responseJson([], 200); 
        }
        // get users
        $resultService = $this->userSrv->loadUsersInDb();
        if($resultService['status'] == 'error'){
            return responseJson($resultService, $resultService['code']);
        }

        // Return all registers
        $users = $this->userSrv->getUsers();
        return responseJson([
            'data'  => $users
        ], 200);        
    }

    /**
     * FindUserByID
     * Buscar un usuario por ID
     */
    public function first($id)
    {
        $user = $this->userSrv->getUserById($id);
        if($user == null){
            return responseJson(['message' => 'Usuario no encontrado'], 404);
        }
        return responseJson(['message' => 'Usuario encontrado', 'data' => $user], 200);
    }
    /**
     * FindUserByEmail
     * Buscar un usuario por email
     */
    public function find()
    {
        $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
        if($email == null){
            return responseJson(['message' => 'Usuario no encontrado'], 404);
        }
        $user = $this->userSrv->getUserByEmail($email);
        if($user == null){
            return responseJson(['message' => 'Usuario no encontrado'], 404);
        }
        return responseJson(['message' => 'Usuario encontrado', 'data' => $user], 200);
    }
    /**
     * Eliminar registro
     * @param $id
     */
    public function delete($id){

        if($this->get_request_method() == 'OPTIONS'){
            return responseJson([], 200); 
        }
        
        $resultServiceValid = $this->userSrv->validateUserClient($id);
        if($resultServiceValid['status'] == 'error'){
            return responseJson($resultServiceValid, $resultServiceValid['code']); 
        }
        $resultService = $this->userSrv->delete($id);
        
        return responseJson($resultService, $resultService['code']); 
    }

    
    /**
     * Guardar Registro
     */
    public function store(){
        
        if($this->get_request_method() == 'OPTIONS'){
            return responseJson([], 200); 
        }
        
        $request['first_name'] = isset($this->_request['first_name']) ?$this->_request['first_name'] : null;
        $request['last_name'] = isset($this->_request['last_name']) ?$this->_request['last_name'] : null;
        $request['age'] = isset($this->_request['age']) ?$this->_request['age'] : null;
        $request['email'] = isset($this->_request['email']) ?$this->_request['email'] : null;
        // $request['time_cita'] = isset($this->_request['time_cita']) ?$this->_request['time_cita'] : null;
        $resultService = $this->userSrv->store($request);
        return responseJson($resultService, $resultService['code']);
    }

    /**
     * Actualizar usuario
     */
    public function update($id){
        
        if($this->get_request_method() == 'OPTIONS'){
            return responseJson([], 200); 
        }
        
        $request['first_name'] = isset($this->_request['first_name']) ?$this->_request['first_name'] : null;
        // $request['id'] = isset($this->_request['id']) ?$this->_request['id'] : null;
        $request['last_name'] = isset($this->_request['last_name']) ?$this->_request['last_name'] : null;
        $request['age'] = isset($this->_request['age']) ?$this->_request['age'] : null;
        $request['email'] = isset($this->_request['email']) ?$this->_request['email'] : null;
        // $request['picture_upload'] = isset($this->_request['picture_upload']) ?$this->_request['picture_upload'] : null;
        // $request['time_cita'] = isset($this->_request['time_cita']) ?$this->_request['time_cita'] : null;

        $resultService = $this->userSrv->update($request, $id);
        return responseJson($resultService, $resultService['code']);
    }
    
}