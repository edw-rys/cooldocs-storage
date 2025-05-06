<?php
// composer dump-autoload
namespace App\Models;

use Efren\Classes\Connection;
use Exception;
use Model;

class UserModel extends Model
{
  protected $fillables = [
    'id'    => 'id'
  ];
  public $table = 'users';
  public $id ;
  public $first_name ;
  public $last_name ;
  public $age ;
  public $email ;
  public $image ;
  public $password ;
  public $status ;
  public $type ;
  public $external_image;
  public $created_at ;
  public $update_at ;
  public $deleted_at ;
  public $item;



  public function __construct()
  {
    $this->created_at = date('Y-m-d H:i:s');
    $this->update_at  = date('Y-m-d H:i:s');
    $this->external_image = 1;
  }




  /**
   * Método para agregar un nuevo registro a la BD
   *
   * @return integer
   */
  public function add()
  {
    // $pass = bcrypt_pass('edw-toni@hotmail.com');
    // var_dump($pass, password_verify('edw-toni@hotmail.com', $pass));die();
    $sql = 'INSERT INTO '.$this->table.' (first_name, last_name, age, email, image, password, status, type, external_image, created_at, update_at) VALUES (:first_name, :last_name, :age, :email, :image, :password, :status, :type, :external_image, :created_at,:update_at)';
    $data =
      [
        'first_name'    =>$this->first_name, 
        'last_name'   =>$this->last_name, 
        'age'   =>$this->age, 
        'email'   =>$this->email, 
        'image'   =>$this->image, 
        'password'    =>$this->password, 
        'status'    =>$this->status, 
        'type'    =>$this->type, 
        'external_image'  => $this->external_image,
        'created_at'    => date('Y-m-d H:i'),
        'update_at' => date('Y-m-d H:i'),
      ];

    try {
      return ($this->id = parent::sql(
        [
            "sql"   => $sql,
            "params" => $data,
            "type"  => "insert"
        ]
      )) ? $this->id : false;
    } catch (Exception $e) {
      throw $e;
    }
  }
  /**
   * Método para retornar datos del cuestionario
   * @return ["class" || "object anonimus" || Index ASSOC]
   */
  public function find($params = null)
  {
    $params['table'] = $this->table;
    $params = parent::cleanData($params);
    $sql = "SELECT " . $params["_sql_params"] . " 
            From " . $this->table . "
            WHERE 1=1 AND deleted_at is null
            ";

    if (!is_null($params["condition"])) {
      $sql  = $sql . $params["condition"];
    }
    try {
      $this->item = parent::sql([
        "sql"           => $sql,
        "params"        => $params["params"],
        "type"          => "query",
        "fetch"         => isset($params["fetch"]) ? $params["fetch"] : null,
        "fetch_type"    => isset($params["fetch_type"]) ? $params["fetch_type"] : null,
        "class"         => (isset($params["fetch_type"]) && $params["fetch_type"] == "class") ? $this->table : null,
      ]);
    } catch (Exception $ex) {
      throw new Exception($ex);
    }
    return $this->item;
  }


  /**
   * Método para actualizar un registor en la db
   *
   * @return 
   */
  public function update($updateFields = [])
  {
    $sql = 'UPDATE ' . $this->table . ' SET ';

    $updateValues = [];
    foreach ($updateFields as $key => $field) {
      $updateValues[$field] = $this->{$field};
      $sql .= ' ' . $field . '=:' . $field . ($key == count($updateFields) - 1 ? ' ' : ', ');
    }
    $sql .= ' WHERE ' . $this->fillables['id'] . '=:id';
    $updateValues['id'] = $this->{$this->fillables['id']};

    try {
      return parent::sql([
        "sql"       => $sql,
        "type"      => "update",
        "params"    => $updateValues,
      ]);
    } catch (Exception $ex) {
      throw new Exception($ex);
    }
  }



    /**
     * Método para eliminar un registo de manera lógica en la tabla
     * @param $id
     * @return 
     */
    public function delete($id)
    {
      $sql = 'UPDATE ' . $this->table . " SET deleted_at=now() , status ='deleted' WHERE " . $this->fillables['id'] . '=:id';
      $updateValues['id'] = $id;
      try {
          return parent::sql([
              "sql"       => $sql,
              "type"      => "update",
              "params"    => $updateValues,
          ]);
      } catch (Exception $ex) {
          throw new Exception($ex);
      }
    }
}
