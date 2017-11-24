<?php

class Capacidad {
  public $host;
  public $database;
  public $user;
  public $pass;
  public $salas;

  public function __construct($db_host, $db_database, $db_login, $db_password){
    $this->host = $db_host;
    $this->database = $db_database;
    $this->user = $db_login;
    $this->pass = $db_password;
    $this->salas = $this->cargarArray();
  }

  function cargarArray() {

    $db = new PDO('mysql:host=' . $this->host . ';dbname='. $this->database .';charset=utf8mb4;port:3306', $this->user, $this->pass);

    $query = $db->query('SELECT id, capacity FROM plan_room');

    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    return $result;
  }

  function getCapacidad($room){
    foreach ($this->salas as $value) {
      if ($room == $value['id'])
        {
          return $value['capacity'];
        }
      }
      return -1;
    }
}

 ?>
