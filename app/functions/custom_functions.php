<?php
// Project functions 
/**
 * @param $text
 */
function bcrypt_pass($text)
{
    $options = [
        'cost' => 11
      ];
    return  password_hash($text, PASSWORD_BCRYPT, $options);
}

function responseJson($data, $code = 200)
{
  http_response_code($code);
  echo json_encode($data);
}


function validateDateWithFormat($date, $format = 'Y-m-d')
{
   $d = DateTime::createFromFormat($format, $date);
   $errors = DateTime::getLastErrors();
 
   return $d && empty($errors['warning_count']) && $d->format($format) == $date;
}
