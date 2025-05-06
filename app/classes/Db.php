<?php 

class Db{
    private $link;
    private $engine;
    private $host;
    private $name;
    private $user;
    private $pass;
    private $charset;
    private $port;
    /**
    * Constructor para nuestra clase
    */
    public function __construct(){
        $this->engine  = IS_LOCAL ? LDB_ENGINE : DB_ENGINE;
        $this->name    = IS_LOCAL ? LDB_NAME : DB_NAME;
        $this->user    = IS_LOCAL ? LDB_USER : DB_USER;
        $this->pass    = IS_LOCAL ? LDB_PASS : DB_PASS;
        $this->charset = IS_LOCAL ? LDB_CHARSET : DB_CHARSET;
        $this->host = IS_LOCAL ? LDB_HOST : DB_HOST;
        $this->port = IS_LOCAL ? LDB_PORT : LDB_PORT;
        
        return $this;    
    }
    /**
    * Método para abrir una conexión a la base de datos
    *
    * @return void
    */
    private function connect() {
        try {
        $this->link = new PDO($this->engine.':host='.$this->host.':'.$this->port.';dbname='.$this->name.';charset='.$this->charset, $this->user, $this->pass);
        return $this->link;
        } catch (PDOException $e) {
        die(sprintf('No  hay conexión a la base de datos, hubo un error: %s', $e->getMessage()));
        }
    }
    /**
    * Método para hacer un query a la base de datos
    *
    * @param string $sql
    * @param array $params
    * @return any
    */


    public static function sql($attributes){
        $db = new self();
        $link = $db->connect(); // nuestra conexión a la db
        $link->beginTransaction(); // por cualquier error, checkpoint
        $query = $link->prepare($attributes["sql"]);

        // printObj($query);

        // die();
        if(!$query->execute($attributes["params"])) {
            $link->rollBack();
            $error = $query->errorInfo();
            // index 0 es el tipo de error
            // index 1 es el código de error
            // index 2 es el mensaje de error al usuario
            throw new Exception($error[2]);
        }
        // add type
        if(!isset($attributes["type"])){
            $attributes["type"]="query";
        }
        $attributes["type"]= strtolower($attributes["type"]);

        if(!isset($attributes["fetch_type"])){
            $attributes["fetch_type"]="OBJ";
        }

        if(!isset($attributes["fetch"])){
            $attributes["fetch"]="ALL";
        }
        // CHECK TYPE SENTENCE SQL
        if($attributes["type"] =="query"){
            if(isset($attributes["class"])){
                if($attributes["fetch"]=="ALL"){
                    return 
                    $query->fetchAll(PDO::FETCH_CLASS,$attributes["class"]);
                }else{
                    $resulSet = 
                    $query->fetchAll(PDO::FETCH_CLASS,$attributes["class"]);
                    return empty($resulSet)?$resulSet:$resulSet[0];
                }
            }else{
                return
                    $attributes["fetch"]=="ALL"?
                        $query->fetchAll(
                            $attributes["fetch_type"] == "OBJ" ?PDO::FETCH_OBJ:
                            PDO::FETCH_ASSOC
                        )
                        :
                        $query->fetch(
                            $attributes["fetch_type"] == "OBJ" ?PDO::FETCH_OBJ:
                            PDO::FETCH_ASSOC
                        )
                        ;
            }
        }elseif ($attributes["type"]=="insert") {
            $id = $link->lastInsertId();
            $link->commit();
            return $id;
        }elseif ($attributes["type"]=="delete") {
            if($query->rowCount() > 0) {
                $link->commit();
                return true;
            }
            $link->rollBack();
            return false; // Nada ha sido borrado
        }elseif ($attributes["type"]=="update") {
            $link->commit();
            return true;
        }
    }
    public static function cleanData($data=null){
        if(is_null($data)){
            $data =[];
        }
        if(!isset($data["table"]) || is_null($data["table"])){
            throw new Exception("Table not found");
        }
        if(!isset($data["params"])){
            $data["params"] = [];
        }
        if(!isset($data["_sql_params"])){
            $data["_sql_params"] = "*";
        }
        if(!isset($data["condition"])){
            $data["condition"]=null;
        }
        if(!isset($data["inner_join"])){
            $data["inner_join"]=null;
        }
        return $data;
    }
    /**
     * Recibe un array de string, string se separa con espacios, tendrá un tamaño de 3 palabra
     * "TABLE llave_primaria llave_de_referencia identificador(opcional)"
     * genera un array con llaves para hacer un inner join
     * @return $innerJoinArrayData
     */
    public static function generateInnerJoin($params=[]) {
        $innerJoinArrayData=[];
        foreach ($params as $value) {
            $value = explode(' ',$value);
            $inner =[
                "table"=>$value[0],
                "this_col_reference"=>$value[1],
                "colReference"=>$value[2],
            ] ;
            if(isset($value[3]) && !empty($value[3])){
                $inner["identify"] = $value[3];
            }
            array_push($innerJoinArrayData, $inner);
        }
        return $innerJoinArrayData;
    }
    /**
     * $params =[
     *  _sql_params , table, params, inner_join, 
     * ]
     */
    public static function set($params=[]) {
        if(!is_array($params)){throw new Exception("Array required", 1);}
        if(!isset($params["table"])){throw new Exception("table required", 1);}
        if(!isset($params["params"])){throw new Exception("Parameters is required", 1);}
        if(!isset($params["condition"])){throw new Exception("condition is required", 1);}
        if(!isset($params["id_table"])){throw new Exception("Id table is required", 1);}
        $sql = "UPDATE ".$params["table"]." SET ";
        $_col = '';
        $keys = array_keys($params["params"]);
        foreach ($params["params"] as $key => $value) {
            // die();
            if( $key === end($keys)){
                $_col = $_col .' '. $key .'=:'.$key . ' ';
            }else{
                $_col = $_col .' '. $key .'=:'.$key . ', ';
            }
        }
        $sql = $sql . $_col .$params["condition"]. ';';
        // echo $sql;die();
        $data_sql =[
            "sql"           => $sql,
            "params"        =>array_merge( $params["params"] , $params["id_table"]  ),
            "type"          => "update",
        ];
        return self::sql($data_sql);
    }
    /**
     * @return mixed
     */
    public static function get($params=[]) {
        try {
            $params = self::cleanData($params);
        } catch (Exception $th) {
            die($th);
        }
        // query
        $as = isset($params['as_table'])?' as '. $params['as_table']:'';
        $sql = 'SELECT '.$params['_sql_params'].' from '. $params['table']. $as.' ';
        // Inner
        if(!is_null($params["joins"])){
            $as_table =isset($params['as_table'])?$params['as_table']:$params['table'];
            foreach ($params["joins"] as $value) {
                if (!isset($value['mode'])) {
                    $value['mode'] = 'inner';
                }
                if(isset($value['identify']) && $value['identify']){
                    $sql = $sql .
                    ' '. $value['mode'].' join '. $value['table'] . ' as '. $value['identify'].
                        ' on '. $value['identify'] .'.'.$value['this_col_reference'].' = '.$value['parent_table'].'.'.$value['colReference'] ;
                }else{
                    $sql = $sql .
                    ' '. $value['mode'].' join '. $value['table'] .
                        ' on '. $value['table'].'.'.$value['this_col_reference'].' = '.$value['parent_table'].'.'.$value['colReference'] ;
                }
            }
        }
        if( !is_null($params['condition']) ){
            $sql  = $sql . " where ".$params['condition'];
        }

        if(isset($params['group_by']) && !is_null($params['group_by']) ){
            $sql  = $sql . " GROUP BY ".$params['group_by'];
        }
        // var_dump($sql);

        $data_sql =[
            "sql"           => $sql,
            "params"        => $params["params"],
            "type"          => "query",
            "fetch"         => isset($params["fetch"])?$params["fetch"]:null,
            "fetch_type"    => isset($params["fetch_type"])?$params["fetch_type"]:null,
            "class"         => (isset($params["class"]) && !empty($params["class"]))?$params["class"]:null,
        ];
        // printObj($data_sql);
        // die();
        try{
            return self::sql($data_sql);
        }catch(Exception $ex){
            die($ex);
        }
    }
}