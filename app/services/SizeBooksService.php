<?php
namespace Service;

include_once SERVICES."RedisService.php";

class SizeBooksService {
    private $keyBook = 'size_book__';
    public function findRedis($id, $part_id = '') {
        RedisService::instance();
        $element = RedisService::get($this->keyBook. $id.'_'.($part_id??''));
        if($element == null){
            return null;
        }
        return $element;
    }

    public function setRedis($id, $data, $part_id = '') {
        RedisService::instance();
        return RedisService::set($this->keyBook. $id. '_'.($part_id??''), $data);
    }


    public function getFileSize($id, $part_id, $s3Key)
    {
        $size = $this->findRedis($id, ($part_id??''));
        if ($size != null) {
            return $size;
        }
        $s3Reader = StorageService::getInstance();
        $fileSize = $s3Reader->getFileSize($s3Key);

        $this->setRedis($id, $fileSize,($part_id??''));
        return $fileSize;
    }
}