<?php

namespace Classes;

class Controller
{
  protected $_method;
  protected $_request;


  public function __construct()
  {
    $this->_method = $this->get_request_method();
    switch ($this->get_request_method()) {
      case "POST":
        $result = file_get_contents("php://input");
        if ($result === FALSE) {
          $this->_request = $this->cleanInputs($_POST);
        } else {
          $this->_request = json_decode($result, true);
          if ($this->_request === NULL) {
            $this->_request = $this->cleanInputs($_POST);
          } else {
            $this->_request = $this->cleanInputs($this->_request);
          }
        }
        break;
      case "GET":
        $this->_request = $this->cleanInputs($_GET);
        break;
      case "DELETE":
        $this->_request = $this->cleanInputs($_GET);
        break;
      case "PUT":
        parse_str(file_get_contents("php://input"), $this->_request);
        $this->_request = $this->cleanInputs($this->_request);
        break;
      case "OPTIONS":
        $result = file_get_contents("php://input");
        if ($result === FALSE) {
          $this->_request = $this->cleanInputs($_POST);
        } else {
          $this->_request = json_decode($result, true);
          if ($this->_request === NULL) {
            $this->_request = $this->cleanInputs($_POST);
          } else {
            $this->_request = $this->cleanInputs($this->_request);
          }
        }
        break;
      default:
        return responseJson([] , 406);
    }
  }
  public function get_request_method()
  {
    return $_SERVER['REQUEST_METHOD'];
  }
  private function cleanInputs($data)
  {
    $clean_input = array();
    if (is_array($data)) {
      foreach ($data as $k => $v) {
        $clean_input[$k] = $this->cleanInputs($v);
      }
    } else {
      $data = strip_tags($data);
      $clean_input = trim($data);
    }
    return $clean_input;
  }
}
