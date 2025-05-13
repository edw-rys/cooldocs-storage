<?php
namespace Service;
use Aws\S3\S3Client;
use Exception;
use Generator;

class StorageService {
    private static $instance ;
    private $s3;
    private $bucket;

    private function __construct() {
        $this->s3 = new S3Client([
            'version' => 'latest',
            'region'  => $_ENV['AWS_DEFAULT_REGION'],
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);
        $this->bucket = $_ENV['AWS_BUCKET'];
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Leer archivo en fragmentos
    public function readFileInChunks(string $fileKey, int $chunkSize = 1024): Generator
    {
        try {
            // Obtener tamaño del archivo
            $result = $this->s3->headObject([
                'Bucket' => $this->bucket,
                'Key'    => $fileKey
            ]);
            
            $fileSize = $result['ContentLength'];

            // Leer en fragmentos
            for ($start = 0; $start < $fileSize; $start += $chunkSize) {
                $end = min($start + $chunkSize - 1, $fileSize - 1);
                
                $result = $this->s3->getObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $fileKey,
                    'Range'  => "bytes=$start-$end"
                ]);

                yield $result['Body']->getContents();
            }
        } catch (Exception $e) {
            throw new Exception("Error reading file: " . $e->getMessage());
        }
    }

    public function getContentType(string $fileKey): string {
        $result = $this->s3->headObject([
            'Bucket' => $this->bucket,
            'Key'    => $fileKey
        ]);
        
        return $result['ContentType'];
    }

    public function getObjectStream(string $fileKey) {
        $result = $this->s3->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $fileKey
        ]);
        
        return $result['Body'];
    }

    public function getFileSize(string $fileKey): int
    {
        try {
            $result = $this->s3->headObject([
                'Bucket' => $this->bucket,
                'Key'    => $fileKey
            ]);
            return $result['ContentLength'];
        } catch (Exception $e) {
            throw new Exception("Archivo no encontrado en S3: $fileKey");
        }
    }

    public function getByteRange(string $fileKey, int $start, int $end): string
    {
        try {
            $result = $this->s3->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $fileKey,
                'Range'  => "bytes=$start-$end"
            ]);
            //echo($result['Body']->getContents() );die;
            return $result['Body']->getContents();
        } catch (Exception $e) {
            throw new Exception("Error al leer rango de bytes: " . $e->getMessage());
        }
    }

    public function exists(string $fileKey): bool
    {
        try {
            return $this->s3->doesObjectExist(
                $this->bucket,
                $fileKey
            );
        } catch (Exception $e) {
            throw new Exception("Error verificando existencia del archivo: " . $e->getMessage());
        }
    }

    // Evitar clonación y deserialización
    private function __clone() {}
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
