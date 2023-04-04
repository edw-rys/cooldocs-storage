<?php

include_once SERVICES."AuthService.php";
include_once SERVICES."UserService.php";
include_once SERVICES."CitasService.php";
include_once CLASSES."Controller.php";

use Service\AuthService;
use Classes\Controller;
use Service\CitasService;
class CitasController extends Controller{
    private $authSrv;
    private $citasSrv ;
    public function __construct(){
        $this->citasSrv = new CitasService();
        $this->authSrv = new AuthService();
        parent::__construct();
        $this->authSrv->abortNoAuth();
    }

    /**
     * Listado de citas
     */
    public function index(){
        // get users
        $data = $this->citasSrv->getData();
        // Return all registers
        return responseJson([
            'data'  => $data
        ], 200);        
    }
    /**
     * Guardar Registro
     */
    public function store(){
        $request['especialidad_id'] = isset($this->_request['especialidad_id']) ?$this->_request['especialidad_id'] : null;
        $request['date'] = isset($this->_request['date']) ?$this->_request['date'] : null;
        $request['user_id'] = isset($this->_request['user_id']) ?$this->_request['user_id'] : null;
        $request['observation'] = isset($this->_request['observation']) ?$this->_request['observation'] : null;
        $request['time_cita'] = isset($this->_request['time_cita']) ?$this->_request['time_cita'] : null;
        $resultService = $this->citasSrv->saveCita($request);
        return responseJson($resultService, $resultService['code']);
    }

    /**
     * Guardar Registro
     */
    public function update($id){
        if($this->get_request_method() == 'OPTIONS'){
            return responseJson([], 200); 
        }
        $request['especialidad_id'] = isset($this->_request['especialidad_id']) ?$this->_request['especialidad_id'] : null;
        $request['date'] = isset($this->_request['date']) ?$this->_request['date'] : null;
        // $request['user_id'] = isset($this->_request['user_id']) ?$this->_request['user_id'] : null;
        $request['observation'] = isset($this->_request['observation']) ?$this->_request['observation'] : null;
        $request['time_cita'] = isset($this->_request['time_cita']) ?$this->_request['time_cita'] : null;
        $resultService = $this->citasSrv->update($request, $id);
        return responseJson($resultService, $resultService['code']);
    }
    /**
     * Obtener especialidades
     */
    public function especialidades()
    {
        $data = $this->citasSrv->getEspecialidades();
        // Return all registers
        return responseJson([
            'data'  => $data
        ], 200);  
    }


    /**
     * Eliminar registro
     * @param $id
     */
    public function delete($id){

        if($this->get_request_method() == 'OPTIONS'){
            return responseJson([], 200); 
        }
        
        $resultService = $this->citasSrv->delete($id);
        
        return responseJson($resultService, $resultService['code']); 
    }

    /**
     * FindCitaByID
     * Buscar una cita por ID
     */
    public function first($id)
    {
        $cita = $this->citasSrv->getCitaById($id);
        if($cita == null){
            return responseJson(['message' => 'Cita no encontrada'], 404);
        }
        return responseJson(['message' => 'Cita encontrada', 'data' => $cita], 200);
    }



}