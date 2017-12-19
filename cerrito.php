<?php
// $Id: day.php 1281 2009-12-09 22:37:17Z jberanek $

require_once "defaultincludes.inc";

// $sql = "SELECT from_unixtime($tbl_entry.start_time), from_unixtime($tbl_entry.end_time), $tbl_entry.room_id, $tbl_room.room_name,
//         $tbl_entry.name
//         FROM $tbl_entry
//         JOIN $tbl_room on room_id=$tbl_room.id
//         WHERE $tbl_entry.start_time >= unix_timestamp('2017-12-19 0:00') AND $tbl_entry.start_time <= unix_timestamp('2017-12-19 23:59') AND $tbl_room.area_id = 14";

$sql = "SELECT $tbl_entry.start_time, $tbl_entry.end_time, $tbl_entry.room_id, $tbl_room.room_name,
        $tbl_entry.name
        FROM $tbl_entry
        JOIN $tbl_room on room_id=$tbl_room.id
        WHERE $tbl_entry.start_time >= unix_timestamp('2017-11-16 0:00') AND $tbl_entry.start_time <= unix_timestamp('2017-11-16 23:59') AND $tbl_room.area_id = 14
        ORDER BY name";


try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_database", $db_login, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $result = $conn->query($sql);
}
catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Sede Bs As</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css?family=Cabin" rel="stylesheet">
    <script type="text/javascript">
    window.onload = function() {
      var tabla = document.getElementsByTagName('tbody');
      var largo = window.innerHeight;
      var filaTitulo = document.getElementsByTagName('thead')[0].offsetHeight;
      var filaAltura = document.getElementsByTagName('tr')[1].offsetHeight;
      tanda = Math.floor((largo - filaTitulo) / filaAltura)
      cursos = (document.getElementsByTagName('tr')) ;
      var cursosCant = (document.getElementsByTagName('tr').length) - 1 ;
      pasadas = Math.ceil(cursosCant/tanda);
      var arrCursos = [];
      console.log("Titulo: " + filaTitulo);
      console.log("cada Fila: " + filaAltura);
      console.log("Largo Pagina: " + largo);
      console.log("Entran: " + tanda);
      console.log("Cursos: " + cursosCant);
      console.log("Pasadas: " + pasadas);
      cont = 1;
      console.log('Pasada '+ cont);
      cambio();
      window.setInterval(cambio, 10000);
      // cambio();
    }

    function cambio(){
      var desaparecer = [];
      var aparecer = [];
      for (var i = 1; i < cursos.length; i++) {
        if ((i >= tanda*cont - (tanda-1)) && (i <= tanda*cont)) {
          console.log(i);
          aparecer.push(cursos[i]);
          // cursos[i].style.display = '';
          // var x = $(cursos[i]).show('slow');
        } else {
          console.log(i + " X");
          desaparecer.push(cursos[i]);
          // cursos[i].style.display = 'none';
          // var x = $(cursos[i]).hide('slow');
        }
      }
      for (var i = 0; i < desaparecer.length; i++) {
        var x = $(desaparecer[i]).hide('slow');
      }
      setTimeout(function(){
        for (var i = 0; i < aparecer.length; i++) {
          var x = $(aparecer[i]).show('slow');
        }
      }, 1000);

      cont = cont + 1;
      console.log("--> " + cont + " --> " + pasadas);
      if (cont > pasadas) {
        cont = 1;
        }
      }

    </script>
    <style media="screen">
    body{
      font-family: 'Cabin', sans-serif;
      font-size: 40px;
      background-color: rgba(8,45,88,1);
    }
    table{
      font-size: 40px;
      color: white;
    }
    th{
      font-size: 50px;
    }
    </style>
  </head>
  <body style="">
    <table class="table">
      <thead>
        <th>Curso</th>
        <th>Inicio</th>
        <th>Fin</th>
        <th>Aula</th>
      </thead>
      <tbody>
        <?php foreach ($result as $value) {
          echo "<tr>";
          // echo "<td>" . date('H:i', $value['start_time']) . " a " . date('H:i', $value['end_time']) . "</td>";
          echo "<td>" . $value['name'] . "</td>";
          echo "<td>" . date('H:i', $value['start_time']) . "</td>";
          echo "<td>" . date('H:i', $value['end_time']) . "</td>";
          echo "<td>" . $value['room_name'] . "</td>";
          echo "</tr>";
        } ?>
        </tbody>
    </table>

  </body>
</html>
