<?php

	require_once "defaultincludes.inc";
	print_header(0, 0, 0, 0, "");

	if ( count($_POST) > 0 ) :

		sql_command("update plan_penalizacion set penalizado=0 where penalizado_resuelto = 0");

		if ( isset($_POST['pen']) ) :
		  foreach($_POST['pen'] as $key=>$value) :
		    sql_command("update plan_penalizacion set penalizado=1 where id=".$value);
		  endforeach;
		endif;

		if ( isset($_POST['res']) ) :
		  foreach($_POST['res'] as $key=>$value) :
		    sql_command("update plan_penalizacion set penalizado_resuelto=1 where id=".$value);
		  endforeach;
		endif;

	endif;

	# analizar estado de registros
	$conexion=mysql_connect($db_host,$db_login,$db_password) or die("Problemas en la conexion");
	mysql_select_db($db_database,$conexion) or die("Problemas en la selección de la base de datos");


	$a = mysql_query("select *, plan_penalizacion.id as ident from plan_penalizacion inner join plan_room on plan_room.id=plan_penalizacion.room_id  and (penalizado_resuelto = 0 || penalizado_resuelto is NULL)  order by fecha_cancelado desc");
	$c = mysql_num_rows($a);

	

function hourdiff($hour_1 , $hour_2 , $formated=false){
    
    $h1_explode = explode(":" , $hour_1);
    $h2_explode = explode(":" , $hour_2);

    $h1_explode[0] = (int) $h1_explode[0];
    $h1_explode[1] = (int) $h1_explode[1];
    $h2_explode[0] = (int) $h2_explode[0];
    $h2_explode[1] = (int) $h2_explode[1];
    

    $h1_to_minutes = ($h1_explode[0] * 60) + $h1_explode[1];
    $h2_to_minutes = ($h2_explode[0] * 60) + $h2_explode[1];

    
    if($h1_to_minutes > $h2_to_minutes){
    $subtraction = $h1_to_minutes - $h2_to_minutes;
    }
    else
    {
    $subtraction = $h2_to_minutes - $h1_to_minutes;
    }

    $result = $subtraction / 60;

    if(is_float($result) && $formated){
    
    $result = (string) $result;
      
    $result_explode = explode(".",$result);

    return $result_explode[0].":".(($result_explode[1]*60)/10);
    }
    else
    {
    return $result;
    }
}


?>
<br />
<form action="penalizaciones.php" method="post">
      <fieldset style="margin-top:20px;">

      <legend style="margin-bottom:20px;">Penalizaciones</legend>
		<input type="submit" value="Guardar datos" name="update" /><br /><br />
		<h3>Opciones</h3>
		<strong>Penalizado:</strong> Si esta marcada esta opción se aplicará la penalización a la reserva.<br>
		<strong>Resolver:</strong> Si esta marcada esta opción no se volverá a mostrar en el listado
		<br /><br />
		<table id="pending_list" class="admin_table" style="visibility: visible;">
		    <thead>
		        <tr>
		            <th class="header_start_time">Evento</th>
		            <th class="header_create">Unidad</th>
		            <th class="header_area">Duraci&oacute;n (horas)</th>
		            <th class="header_room">Fecha de Reservaci&oacute;n</th>
		            <th class="header_name">Fecha Cancelaci&oacute;n</th>
                    <th class="header_name">Penalizado</th>
                    <th class="header_name">Resolver</th>
		        </tr>
		    </thead>
		    <tbody>
		    	<?php //for ($i=0; $i <= $c-1 ; $i++) : ?>
		    	<?php //$row = sql_row_keyed($a, $i); ?>
		    	<?php while ($row = mysql_fetch_array($a)) : ?>
		    	<?php
		    		$mi = (int)utf8_strftime("%m",$row['start_time']);
		    		$di = (int)utf8_strftime("%d",$row['start_time']);
		    		$dc = (int)utf8_strftime("%d",strtotime($row['fecha_cancelado']));
		    		$mc = (int)utf8_strftime("%m",strtotime($row['fecha_cancelado']));
		    		$res = ($di - $dc) < 0 ? (-1)*($di - $dc) : ($di - $dc);
		    	?>
		    	<tr>
		            <td><?php echo $row['name']; ?></td>
		            <td><?php echo $row['room_name']; ?></td>
		            <td>
		            <?php

						$from_time = strtotime($row['end_time']);
						$to_time = strtotime($row['start_time']);
						echo $duracion=hourdiff(date('H:i',$row['start_time']),date('H:i',$row['end_time']),true);
		            ?>
		            </td>
		            <td><?php echo utf8_strftime("%d %b %Y",$row['start_time']); ?></td>
		            <td><?php echo utf8_strftime("%d %b %Y",strtotime($row['fecha_cancelado'])); ?></td>
                    <td><input type="checkbox" name="pen[]" value="<?php echo $row['ident']; ?>" <?php echo $row['penalizado']==1?'checked="checked"':''; ?> /></td>
		        	<td><input type="checkbox" name="res[]" value="<?php echo $row['ident']; ?>" <?php echo $row['penalizado_resuelto']==1?'checked="checked"':''; ?> /></td>
		        </tr>
		        
		        <?php endwhile; ?>
		    </tbody>
		</table>

		</fieldset>
</form>