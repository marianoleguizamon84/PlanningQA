<?php
require_once "defaultincludes.inc";
print_header(0, 0, 0, 0, "");
echo "<br>";

  if ($_POST['accion']!='')
  {
  	if (trim($_POST['p1'])=='')
  		$_POST['p1']=0;
  	if (trim($_POST['p2'])=='')
  		$_POST['p2']=0;
    if (trim($_POST['p3'])=='')
      $_POST['p2']=0;
  	
  	sql_command("delete from plan_configuration");
  	sql_command("insert into plan_configuration values('{$_POST['p1']}','{$_POST['p2']}','{$_POST['p3']}');");	
  }

  $a=sql_query("select * from plan_configuration");  

  $row = sql_row_keyed($a, 0);
?>


<form class="form_general" method="post" action="configuracion.php">
      <fieldset>
      <legend>Costos por metro cuadrado</legend>
      
        <div id="div_report_start">
	      <label for="areamatch">Costo Enero - Marzo:</label>
	      <input type="text" name="p1" value="<?php echo $row['costo_ene_mar']; ?>">
        </div>

        <div id="div_report_start">
	      <label for="areamatch">Costo Abril - Septiembre:</label>
	      <input type="text" name="p2" value="<?php echo $row['costo_abr_sep']; ?>">
        </div>

        <div id="div_report_start">
        <label for="areamatch">Costo Octubre - Diciembre:</label>
        <input type="text" name="p3" value="<?php echo $row['costo_oct_dic']; ?>">
        </div>
      
        <div id="report_submit">
          <input class="submit" name="accion" type="submit" value="Guardar configuraci&oacute;n">
        </div>
      
      </fieldset>
    </form>

<?php 
  #mysql_close($a);
?>