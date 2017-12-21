<?php
// $Id: day.php 1281 2009-12-09 22:37:17Z jberanek $

require_once "defaultincludes.inc";

if ($_GET['anio'] AND $_GET['mes'] AND $_GET['dia']) {
  $dia = $_GET['dia'];
  $mes = $_GET['mes'];
  $anio = $_GET['anio'];
} else {
  $dia = date("d");
  $mes = date("m");
  $anio = date("Y");
}


// $sql = "SELECT from_unixtime($tbl_entry.start_time), from_unixtime($tbl_entry.end_time), $tbl_entry.room_id, $tbl_room.room_name,
//         $tbl_entry.name
//         FROM $tbl_entry
//         JOIN $tbl_room on room_id=$tbl_room.id
//         WHERE $tbl_entry.start_time >= unix_timestamp('2017-12-19 0:00') AND $tbl_entry.start_time <= unix_timestamp('2017-12-19 23:59') AND $tbl_room.area_id = 14";

$sql = "SELECT $tbl_entry.start_time, $tbl_entry.end_time, $tbl_entry.room_id, $tbl_room.room_name,
        $tbl_entry.name
        FROM $tbl_entry
        JOIN $tbl_room on room_id=$tbl_room.id
        -- WHERE $tbl_entry.start_time >= unix_timestamp('2017-11-16 0:00') AND $tbl_entry.start_time <= unix_timestamp('2017-11-16 23:59') AND $tbl_room.area_id = 14
        WHERE $tbl_entry.start_time >= unix_timestamp('$anio-$mes-$dia 0:00') AND $tbl_entry.start_time <= unix_timestamp('$anio-$mes-$dia 23:59') AND $tbl_room.area_id = 14
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
      calculos();
      cambioAltura(filaAltura);
      cambio();
      window.setInterval(cambio, 10000);
    }

    // window.onresize = function(event){
    //   calculos();
    //   cambioAltura(filaAltura);
    //   cambio();
    //   // window.setInterval(cambio, 10000);
    // }

    function calculos(){
      var largo = window.innerHeight;
      var filaTitulo = document.getElementsByTagName('thead')[0].offsetHeight;
      filaAltura = medio();
      tanda = Math.floor((largo - filaTitulo) / filaAltura)
      cursos = (document.getElementsByTagName('tr')) ;
      var cursosCant = (document.getElementsByTagName('tr').length) - 1 ;
      pasadas = Math.ceil(cursosCant/tanda);
      cont = 1;
      // console.log("Titulo: " + filaTitulo);
      // console.log("cada Fila: " + filaAltura);
      // console.log("Largo Pagina: " + largo);
      // console.log("Entran: " + tanda);
      // console.log("Cursos: " + cursosCant);
      // console.log("Pasadas: " + pasadas);
      // console.log('Pasada '+ cont);
      // console.log('Maxima altura de TD ' + filaAltura + 'px');
    }

    function cambio(){
      var desaparecer = [];
      var aparecer = [];
      for (var i = 1; i < cursos.length; i++) {
        if ((i >= tanda*cont - (tanda-1)) && (i <= tanda*cont)) {
          // console.log(i);
          aparecer.push(cursos[i]);
        } else {
          // console.log(i + " X");
          desaparecer.push(cursos[i]);
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
      // console.log("--> " + cont + " --> " + pasadas);
      if (cont > pasadas) {
        cont = 1;
        }
      }

      function medio(){
        var maxHeight = 0;
        var fila = document.getElementsByTagName('td');
        for (var i = 0; i < fila.length; i++) {
          fila[i].style.verticalAlign = "middle";
          if (fila[i].offsetHeight > maxHeight) {
            maxHeight = fila[i].offsetHeight;
          }
        }
        return maxHeight;
      }

      function cambioAltura(max){
        // var fila = document.getElementsByTagName('td');

        for (var i = 1; i < cursos.length; i++) {
          // fila[i].style.height = max + "px";
          cursos[i].style.height = max + "px";
        }
      }

      function calendar(){
        console.log(document.getElementsByTagName('input')[0].value);
        var fecha = document.getElementsByTagName('input')[0].value;
        var dia = fecha.substr(8,2);
        var mes = fecha.substr(5,2);
        var anio = fecha.substr(0,4);
        console.log(dia+mes+anio);
        window.location="./cerrito.php?anio="+anio+"&mes="+mes+"&dia="+dia;
      }
      function atras(){
        console.log('atras');
      }
      function adelante(){
        console.log('adelante');
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
    .botonera{
      display: flex;
      align-content: space-between;
    }
    .flechas{
      background-color: Transparent;
      color: white;
      border-radius: 5px;
      width: 25%;
      box-sizing: border-box;
    }
    input{
      text-align: center;
      width: 50%;
      border-radius: 5px;
      box-sizing: border-box;
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
          echo "<td>" . $value['name'] . "</td>";
          echo "<td>" . date('H:i', $value['start_time']) . "</td>";
          echo "<td>" . date('H:i', $value['end_time']) . "</td>";
          echo "<td>" . $value['room_name'] . "</td>";
          echo "</tr>";
        } ?>
        </tbody>
    </table>
    <div class="botonera">
      <button type="button" name="button" class="flechas" onclick="atras()"><span class="glyphicon glyphicon-chevron-left"></span></button>
      <input type="date" name="" value="<?php echo $anio."-".$mes."-".$dia ?>" onchange="calendar()">
      <button type="button" name="button" class="flechas" onclick="adelante()"><span class="glyphicon glyphicon-chevron-right"></span></button>
    </div>

  </body>
</html>
