<?php
// $Id: day.php 1281 2009-12-09 22:37:17Z jberanek $

require_once "defaultincludes.inc";

if ($_POST['fecha']) {
  $dia = substr($_POST['fecha'], 8, 2);
  $mes = substr($_POST['fecha'], 5, 2);
  $anio = substr($_POST['fecha'], 0, 4);
} else {
  $dia = date("d");
  $mes = date("m");
  $anio = date("Y");
}

$sql = "SELECT $tbl_entry.start_time, $tbl_entry.end_time, $tbl_entry.room_id, $tbl_room.room_name,
        $tbl_entry.name
        FROM $tbl_entry
        JOIN $tbl_room on room_id=$tbl_room.id
        WHERE $tbl_entry.start_time >= unix_timestamp('$anio-$mes-$dia 0:00') AND $tbl_entry.start_time <= unix_timestamp('$anio-$mes-$dia 23:59') AND $tbl_room.area_id = 14
        ORDER BY start_time, name";


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
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="shortcut icon" href="https://d30y9cdsu7xlg0.cloudfront.net/png/123953-200.png">
    <script type="text/javascript">
    window.onload = function() {
      encabezado();
      calculos();
      cambioAltura(filaAltura);
      tituloFijo();
      tamanoPantalla();
    }

    window.onresize = function(event){
      resetear();
      encabezado();
      calculos();
      cambioAltura(filaAltura);
      tituloFijo();
      tamanoPantalla();
    }

    function encabezado(){
      var tablaTitulo = document.getElementsByClassName('tablaTitulo');
      var encabezadoTitulo = document.getElementsByClassName('encabezadoTitulo');
      for (var i = 0; i < tablaTitulo.length; i++) {
        encabezadoTitulo[i].style.width = tablaTitulo[i].offsetWidth + "px";
        encabezadoTitulo[i].style.height = tablaTitulo[i].offsetHeight + "px";
      }
    }

    function calculos(){
      var largo = window.innerHeight;
      var filaTitulo = document.getElementsByTagName('thead')[0].offsetHeight;
      filaAltura = medio();
      tanda = Math.floor((largo - filaTitulo) / filaAltura)
      cursos = (document.getElementsByTagName('tr')) ;
      var cursosCant = cursos.length - 2 ;
      pasadas = Math.ceil(cursosCant/tanda);
      cont = 0;
    }

    function cambio(){
      var desaparecer = [];
      var aparecer = [];
      cont++;
      if (cont > pasadas) {
        cont = 1;
      }
      for (var i = 1; i < cursos.length; i++) {
        if ((i >= tanda*cont - (tanda-1)) && (i <= tanda*cont)) {
          aparecer.push(cursos[i]);
        } else {
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
        for (var i = 1; i < cursos.length - 1; i++) {
          cursos[i].style.height = max + "px";
        }
      }

    function calendar(){
        var fecha = document.getElementsByTagName('input')[0].value;
        document.getElementById('fecha').submit();
      }

    function tituloFijo(){
      var alto = document.getElementsByTagName('tr')[0].offsetHeight;
      var titulo = $("#encabezado");
      titulo.hide();
      $(window).scroll(function(){
      if($(window).scrollTop() >= alto ){
        titulo.show();
      } else {
        titulo.hide();
      }
      });
    }

    function tamanoPantalla(){
      if ($(window).width() > 500) {
        cambio();
        intervalo = window.setInterval(cambio, 10000);
      }
      if ($(window).width() < 500) {
        var x = $('input').outerHeight();
        $('.table').css('margin-bottom', x + 'px');
      }
    }

    function resetear(){
      $('tr').show();
      if (typeof intervalo != 'undefined') {
        clearInterval(intervalo);
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
    .botonera{
      display: none;
      position: fixed;
      bottom: 0;
      align-content: space-between;
      width: 100%;
    }
    .encabezado{
      /* display: none; */
      width: 100%;
      position: fixed;
      top: 0;
      background-color: rgba(8,45,88,1);
      border-bottom: solid 1px white;
    }
    .encabezado th{
      padding: 8px;
      border-bottom: 2px;
    }
    .flechas{
      background-color: Transparent;
      color: white;
      border-radius: 5px;
      width: 25%;
      box-sizing: border-box;
    }
    input{
      -webkit-appearance: none;
      box-sizing: border-box;
      text-align: center;
      text-align-last: center;
      /* text-align: -webkit-center; */
      width: 100%;
      border-radius: 5px;
      font-size: 25px;
      background-color: rgba(8,45,88,1);
      color: white;
      /* width: 50%; */
      /* -moz-box-sizing:    border-box; */
      /* -webkit-box-sizing: border-box; */

    }
    form{
      width: 100%;
    }
    @media (max-width: 500px) {
      body{
        font-size: 15px;
      }
      .botonera {
        display: flex;
      }
      table {
        font-size: 17px;
        color: white;
      }
      th {
        font-size: 25px;
      }
    }
    </style>
  </head>
  <body>
    <table class="table">
      <thead>
        <th class="tablaTitulo">Curso</th>
        <th class="tablaTitulo">Horario</th>
        <!-- <th class="tablaTitulo">Fin</th> -->
        <th class="tablaTitulo">Aula</th>
      </thead>
    <tbody>
        <?php foreach ($result as $value) {
          echo "<tr>";
          echo "<td>" . $value['name'] . "</td>";
          echo "<td>" . date('H:i', $value['start_time']) . " a " .  date('H:i', $value['end_time']) . "</td>";
          // echo "<td>" . date('H:i', $value['end_time']) . "</td>";
          echo "<td>" . $value['room_name'] . "</td>";
          echo "</tr>";
        } ?>
        </tbody>
    </table>
    <div class="encabezado" id="encabezado">
      <table>
        <thead>
          <th class="encabezadoTitulo">Curso</th>
          <th class="encabezadoTitulo">Horario</th>
          <!-- <th class="encabezadoTitulo">Fin</th> -->
          <th class="encabezadoTitulo">Aula</th>
        </thead>
      </table>
    </div>
    <div class="botonera" id="botonera">
      <!-- <button type="button" name="button" class="flechas" onclick="atras()"><span class="glyphicon glyphicon-chevron-left"></span></button> -->
      <form class="" method="post" id="fecha">
        <input type="date" name="fecha" value="<?php echo $anio."-".$mes."-".$dia ?>" onchange="calendar()">
      </form>
      <!-- <button type="button" name="button" class="flechas" onclick="adelante()"><span class="glyphicon glyphicon-chevron-right"></span></button> -->
    </div>

  </body>
</html>
