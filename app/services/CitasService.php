<?php
namespace Service;
include_once MODELS."UserModel.php";
include_once MODELS."CitasModel.php";
include_once MODELS."EspecialidadModel.php";
use App\Models\CitasModel;
use App\Models\UserModel;
use App\Models\EspecialidadModel;


class CitasService {

    /**
     * @param $id
     */
    public function getCitaById($id)
    {
        $cita = new CitasModel;
        $citaFind = $cita->find([
            'condition'    => 'AND cita.id=:id',
            'fetch_type'         => 'OBJ',
            'fetch'         => 'ONE',
            'params'       => [
                'id'  => $id
            ]
        ]);
        if($citaFind == null){
            return $citaFind;
        }
        $citaFind->user = (new UserModel)->find([
            'condition' => 'AND id=:id',
            'params'    => ['id' => $citaFind->user_id],
            'fetch'         => 'ONE',
        ]);
        return $citaFind;
    }
    

    /**
     * Listado de citas
     */
    public function getData()
    {
        $citas = new CitasModel;
        
        $data = $citas->find([
            '_sql_params' => 'cita.id, users.email, users.first_name, users.last_name, cita.date_complete, cita.date_cita, cita.time_cita, cita.observation, especialidad.name as especialidad_title '
        ]);
        return $data;
    }
    /***
     * Obtener listado de especialidades
     */
    public function getEspecialidades()
    {
        $model = new EspecialidadModel;
        
        $data = $model->find([]);
        return $data;
    }
    /**
     * Método para validación de citas
     * @param $request
     * @return array
     */
    public function validateCita($request, $id = null)
    {
        $errors = [];
        if($request['especialidad_id'] == null){
            $errors[] = 'Seleccione la especialidad';
        }else{
            // VALIDA USER
            $existData = (new EspecialidadModel)->find([
                'condition' => 'AND id=:id ',
                'fetch'     => 'ONE',
                'params'    => ['id'=> $request['especialidad_id'] ]
            ]);
            if(!$existData ){
                $errors[] = 'Especialidad no existe ';
            }
        }
        if($request['date'] == null){
            $errors[] = 'Seleccione la fecha';
        }else{
            if( !validateDateWithFormat($request['date'])){
                $errors[] = 'La fecha no es válida';
            }
        }
        if($id == null){
            if($request['user_id'] == null){
                $errors[] = 'Se requiere usuario';
            }else{
                // VALIDA USER
                $userFind = (new UserModel)->find([
                    'condition' => 'AND id=:id and type=:type',
                    'fetch'     => 'ONE',
                    'params'    => ['id'=> $request['user_id'] , 'type' => 'client']
                ]);
                if(!$userFind ){
                    $errors[] = 'Usuario no existe';
                }
            }
        }
        if($request['observation'] == null){
            $errors[] = 'Ingrese la observación';
        }
        if($request['time_cita'] == null){
            $errors[] = 'Ingrese la hora de la cita';
        }
        
        return $errors;
    }

    /**
     * Valida cita
     * guardar registro
     * @param $request
     */
    public function saveCita($request)
    {
        $errors = $this->validateCita($request);
        if (count($errors)> 0) {
            return [
                'status'    => 'error',
                'code'      => '400',
                'errors'    => $errors,
                'message'   => 'Errores detectadps',
            ];
        }
        // Crear cita

        $cita = new CitasModel;
        $cita->date_complete = $request['date']. ' '. $request['time_cita'];
        $cita->date_cita = $request['date'];
        $cita->time_cita = $request['time_cita'];

        $cita->observation = $request['observation'];
        $cita->user_id = $request['user_id'];
        $cita->especialidad_id = $request['especialidad_id'];
        $cita->add();

        return [
            'status'    => 'success',
            'code'      => 201,
            'data'      => $cita->id,
            'message'   => 'Cita creada, su número es: '.$cita->id,
        ];
    }
    /**
     * Update item
     * @param $request
     * @param $id
     */
    public function update($request, $id)
    {
        $errors = $this->validateCita($request, $id);
        if (count($errors)> 0) {
            return [
                'status'    => 'error',
                'code'      => '400',
                'errors'    => $errors,
                'message'   => 'Errores detectadps',
            ];
        }
        // Crear cita

        $cita = new CitasModel;
        $exist = $cita->find([
            'condition' => 'and cita.id=:id',
            'params'    => [
                'id'    => $id
            ],
            'fetch' => 'ONE'
        ]);
        if(!$exist) {
            return [
                'status'    => 'error',
                'code'      => '404',
                'message'   => 'Cita no encontrada',
            ];
        }

        $cita->date_complete = $request['date']. ' '. $request['time_cita'];
        $cita->date_cita = $request['date'];
        $cita->time_cita = $request['time_cita'];
        $cita->observation = $request['observation'];
        $cita->especialidad_id = $request['especialidad_id'];
        $cita->id = $id;

        // $cita->user_id = $request['user_id'];
        $cita->update([ 'date_complete', 'date_cita', 'time_cita', 'observation', 'especialidad_id']);

        return [
            'status'    => 'success',
            'code'      => 201,
            'data'      => $cita->id,
            'message'   => 'Cita actualizada, su número es: '.$id,
        ];
    }


    /**
     * Eliminar registro de manera lógica
     */
    public function delete($cita_id)
    {

        $cita = new CitasModel;
        $citaFind = $cita->find([
            'condition' => 'AND cita.id=:id',
            'fetch'     => 'ONE',
            'fetch_type'     => 'OBJ',
            'params'    => ['id'=>$cita_id]
        ]);
        if(!$citaFind){
            return [
                'status'    => 'error',
                'code'      => '404',
                'message'   => 'Cita no encontrada'
            ];
        }

        $cita->delete($cita_id);

        return [
            'status'    => 'succes',
            'code'      => '200',
            'message'   => 'Cita eliminada'
        ];
    }
}