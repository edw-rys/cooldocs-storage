<?php
namespace Service;


class BooksService {

    public function getFileRoute($id)
    {
        $file = 'C:/xampp/htdocs/CoolDocs/storage/app/books/4/book/6eoQXeIx3t39mhNSigD8VF7MzCXXAefzZ1bamC3E.pdf';
        $file = '/webapps/apps/puente-digital/backend/storage/app/books/73/book/1657040558.pdf';
        
        if(!file_exists($file)){
            responseJson(['message'=> 'Libro no encontrado'], 404);
            exit;
        }
        return $file;

    }
}