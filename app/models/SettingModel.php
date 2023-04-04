<?php 
namespace App\Models;

use Exception;
use Model;
class SettingModel extends Model
{
    private $table = 'settings_api';
    public $id;
    public $url;
    public $load_api;
    public $created_at;
    public $update_at;
    public $deleted_at;
    public $item;

    public function __construct() {
    }
    /**
     * Método para retornar un producto
     * @return ["class" || "object anonimus" || Index ASSOC]
     */
    public function find($params =null){
        $params['table'] = $this->table;
        $params = parent::cleanData($params);
        $sql="SELECT ".$params["_sql_params"]." 
            From " . $this->table . " where deleted_at is null";
        if( !is_null($params["condition"]) ){
            $sql  = $sql . $params["condition"];
        }
        try{
            $this->item = parent::sql([
                "sql"           => $sql,
                "params"        => $params["params"],
                "type"          => "query",
                "fetch"         => 'one',
                // "fetch"         => isset($params["fetch"]) ? $params["fetch"] : null,
                "fetch_type"    => 'object',
                // "fetch_type"    => isset($params["fetch_type"]) ? $params["fetch_type"] : null,
                // "class"         => (isset($params["fetch_type"]) && $params["fetch_type"] == "class") ? $this->table : null,
            ]);
        }catch(Exception $ex){
            throw new Exception($ex);
        }
        return $this->item;
    }    
    /**
     * Método para agregar un nuevo producto
     *
     * @return integer
     */
    public function add(){
        $sql = 'INSERT INTO ' . $this->table . ' (url, load_api, created_at, update_at) VALUES (:url, :load_api, :created_at, :update_at)';
        $data =
          [
            'url'       => $this->url,
            'load_api'  => $this->load_api,
            'created_at'=> date('Y-m-d H:i'),
            'update_at' => date('Y-m-d H:i')
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
   * Método para actualizar un registor en la db
   *
   * @return bool
   */
    public function update()
    {
        $sql = 'UPDATE product SET name=:name, type_id=:type_id, url_image=:url_image ,description=:description WHERE id=:id';
        $product = 
        [
            'id'              => $this->id,
            'name'            => $this->name,
            'description'     => $this->description,
            'type_id'            => $this->type,
            'url_image'     => $this->url_image,
        ];

        try {
        return parent::sql([
            "sql"       => $sql,
            "type"      => "update",
            "params"    => $product,
        ]);
        } catch (Exception $e) {
            die($e);
        }
    }
    public function delete()
    {
        $sql = 'DELETE from product WHERE id=:id;';
        $params = [
            "sql"       => $sql,
            'params'    => ["id"=>$this->id],
            "type"      => "delete"
        ];

        try {
        return parent::sql($params);
        } catch (Exception $e) {
        throw $e;
        }
    }
}