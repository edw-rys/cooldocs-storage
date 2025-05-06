<?php
// composer dump-autoload
namespace App\Models;

use Efren\Classes\Connection;
use Exception;
use Model;

class EspecialidadModel extends Model
{
    protected $fillables = [
        'id'    => 'id'
    ];
    public $table = 'especialidad';

    public $id;
    public $name;
    public $status;
    public $created_at;
    public $update_at;
    public $deleted_at;
    public $item;



    public function __construct()
    {
        $this->created_at = date('Y-m-d H:i:s');
        $this->update_at  = date('Y-m-d H:i:s');
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
}
