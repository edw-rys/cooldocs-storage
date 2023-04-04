<?php
namespace Service;
include_once MODELS."UserModel.php";
include_once MODELS."SettingModel.php";
use App\Models\SettingModel;
use App\Models\UserModel;

class UserService{
    /**
     * En caso de no tener la configuración, procede a agregar los usuarios
     * Existe validación para evitar usuarios duplicados
     */
    public function loadUsersInDb()
    {
        $setting = new SettingModel;
        $settingReslut = $setting->find();
        if (!$settingReslut) {
            $url = 'https://dummyjson.com/users';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $data = curl_exec($curl);
            curl_close($curl);
            if(!$data){
                return [
                    'status'    => 'error',
                    'code'      => '400',
                    'message'   => 'End point no es válido'
                ];
            };

            $listUsers = json_decode($data);
            if(!$listUsers){
                return [
                    'status'    => 'error',
                    'code'      => '400',
                    'message'   => 'Error al cargar los usuarios'
                ];
            }
            if (!$listUsers->users) {
                return [
                    'status'    => 'error',
                    'code'      => '400',
                    'message'   => 'No existen usuarios'
                ];
            }
            foreach ($listUsers->users as $key => $userApi) {
                $user = new UserModel;
                $userFind = $user->find([
                    'condition' => 'AND email=:email',
                    'fetch'     => 'one',
                    'params'    => ['email'=>$userApi->email]
                ]);
                // Usuario registrado?
                if ($userFind) {
                    continue;
                }
                $user->first_name = $userApi->firstName;
                $user->last_name = $userApi->lastName;
                $user->age = $userApi->age;
                $user->email = $userApi->email;
                $user->image = $userApi->image;
                $user->status = 'active';
                $user->type = 'client';
                $user->first_name = $userApi->firstName;
                $user->add();
            }
            // Crear config
            $setting->url = $url;
            $setting->load_api = 1;
            $setting->add();
        }
        return [
            'status'    => 'success',
            'code'      => '200',
            'message'   => 'Validación hecha'
        ];
    }
    /**
     * 
     */
    public function getUsers()
    {
        $users = new UserModel;
        return $users->find([
            'condition'    => 'AND type=:type',
            'params'       => [
                'type'  => 'client'
            ]
        ]);
    }
    /**
     * @param  $email
     */
    public function getUserByEmail($email)
    {
        $users = new UserModel;
        return $users->find([
            'condition'    => 'AND type=:type AND email=:email',
            'fetch'         => 'ONE',
            'params'       => [
                'type'  => 'client',
                'email'  => $email
            ]
        ]);
    }

    /**
     * @param $id
     */
    public function getUserById($id)
    {
        $users = new UserModel;
        return $users->find([
            'condition'    => 'AND type=:type AND id=:id',
            'fetch'         => 'ONE',
            'params'       => [
                'type'  => 'client',
                'id'  => $id
            ]
        ]);
    }
    

    /**
     * Eliminar registro de manera lógica
     */
    public function validateUserClient($user_id)
    {
        $user = new UserModel;
        $userFind = $user->find([
            'condition' => 'AND id=:id',
            'fetch'     => 'ONE',
            'fetch_type'     => 'OBJ',
            'params'    => ['id'=>$user_id]
        ]);
        if(!$userFind){
            return [
                'status'    => 'error',
                'code'      => '404',
                'message'   => 'Usuario no encontrado'
            ];
        }

        if($userFind->type == 'admin'){
            return [
                'status'    => 'error',
                'code'      => '400',
                'message'   => 'No se puede eliminar administrador'
            ];
        }
        return [
            'status'    => 'succes',
            'code'      => '200',
            'message'   => 'Usuario válido'
        ];
    }

    /**
     * Eliminar registro de manera lógica
     */
    public function delete($user_id)
    {

        $user = new UserModel;
        $user->delete($user_id);

        return [
            'status'    => 'succes',
            'code'      => '200',
            'message'   => 'Usuario eliminado'
        ];
    }







    // INTERMEDIO

    /**
     * Método para validación de datos de usuario
     * @param $request
     * @return array
     */
    public function validateUser($request, $id = null)
    {
        $errors = [];
        if($request['first_name'] == null){
            $errors[] = 'Ingrese nombres';
        }
        if($request['last_name'] == null){
            $errors[] = 'Ingrese apellidos';
        }
        if($request['age'] == null){
            $errors[] = 'Ingrese la edad';
        
        }
        if($request['email'] == null){
            $errors[] = 'Ingrese el correo';
        }else{
            // VALIDA USER
            if($id == null){
                $userFind = (new UserModel)->find([
                    'condition' => 'AND email=:email and type=:type',
                    'fetch'     => 'one',
                    'params'    => ['email'=> $request['email'] , 'type' => 'client']
                ]);
                if($userFind ){
                    $errors[] = 'El email ya está registrado';
                }
            }else{
                $userFind = (new UserModel)->find([
                    'condition' => 'AND email=:email and type=:type and id!=:id',
                    'fetch'     => 'one',
                    'params'    => ['email'=> $request['email'] , 'type' => 'client', 'id'=>$id]
                ]);
                if($userFind ){
                    $errors[] = 'El email ya está registrado';
                }
            }
        }
        
        return $errors;
    }

    /**
     * Valida cita
     * guardar registro
     * @param $request
     */
    public function store($request)
    {
        $errors = $this->validateUser($request);
        if (count($errors)> 0) {
            return [
                'status'    => 'error',
                'code'      => '400',
                'errors'    => $errors,
                'message'   => 'Errores detectadps',
            ];
        }
        // Crear user

        $user = new UserModel;
        $user->first_name = $request['first_name'];
        $user->last_name = $request['last_name'];
        $user->email = $request['email'];
        $user->password = 'no';
        $user->age = $request['age'];
        $user->type = 'client';
        $user->status = 'active';
        $user->image = null;
        $user->external_image = 0;        

        // Check img
        if(isset($_FILES['picture_upload'])){
            $resultUpload = saveImage('picture_upload');
            if($resultUpload['status'] == 'error'){
                return $resultUpload;
            }
            $user->image = $resultUpload['url'];
            $user->external_image = '0';
            
        }
        $user->add();

        return [
            'status'    => 'success',
            'code'      => 201,
            'data'      => $user->id,
            'message'   => 'Usuario registrado exitosamente',
        ];
    }
    /**
     * @param $request
     * @param $id
     */
    public function update($request, $id)
    {
        $errors = $this->validateUser($request, $id);
        if (count($errors)> 0) {
            return [
                'status'    => 'error',
                'code'      => '400',
                'errors'    => $errors,
                'message'   => 'Errores detectados',
            ];
        }
        $userModel = (new UserModel);
        $user = $userModel->find([
            'condition' => 'and id=:id',
            'fetch'     => 'ONE',
            'fetch_type'=> 'OBJ',
            'params'    => ['id'=>$id]
        ]);
        if($user == null){
            return [
                'status'    => 'error',
                'code'      => '404',
                // 'errors'    => $errors,
                'message'   => 'Usuario no existe',
            ];
        }
        $userModel->first_name = $request['first_name'];
        $userModel->last_name = $request['last_name'];
        $userModel->email = $request['email'];
        $userModel->age = $request['age'];
        $userModel->id = $id;        
        $listUpload = ['first_name','last_name','email','age'];
        // Check img
        if(isset($_FILES['picture_upload'])){
            $resultUpload = saveImage('picture_upload');
            if($resultUpload['status'] == 'error'){
                return $resultUpload;
            }
            $listUpload[]= 'image';
            $listUpload[]= 'external_image';
            $userModel->image = $resultUpload['url'];
            $userModel->external_image = '0';
        }
        $userModel->update($listUpload);

        return [
            'status'    => 'success',
            'code'      => 201,
            'data'      => $user->id,
            'message'   => 'Usuario actualizado exitosamente',
        ];
    }
}