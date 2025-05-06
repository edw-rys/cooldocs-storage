<?php
namespace Service;

class RedisService
{
    private static $redis;    
    
    private function __construct() {
    }
    public static function instance() {
        if (self::$redis != null) {
            return self::$redis;
        }
        $redis = new \Redis();

        $host = $_ENV['REDIS_HOST'];
        $port = $_ENV['REDIS_PORT'];
        $password = $_ENV['REDIS_PASSWORD'];
        $db = $_ENV['REDIS_DB'];

        if (!$redis->connect($host, $port)) {
            die("❌ No se pudo conectar a Redis");
        }

        if (!$redis->auth($password)) {
            die("❌ Autenticación fallida");
        }

        $redis->select($db);
        self::$redis = $redis;
        /*var_dump($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT'], $_ENV['REDIS_PASSWORD']);
        self::$redis = new \Predis\Client([
            'scheme' => 'tcp',
            'host'   => $_ENV['REDIS_HOST'],
            'port'   => $_ENV['REDIS_PORT'],
            'password' => $_ENV['REDIS_PASSWORD']
        ]);*/
        //self::$redis = new Redis();
        //self::$redis->connect(getenv('REDIS_HOST'), getenv('REDIS_PORT'));
        //self::$redis->auth(getenv('REDIS_PASSWORD'));
        return self::$redis;
    }

    public static function set($key, $value) {
        self::$redis->set($key, $value, 345600); // 4 días
    }

    public static function get($key) {
        return self::$redis->get($key);
    }
}
