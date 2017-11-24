<?php


class FacturacionMensual {

	private $conexion;
	private $area_id; // El edificio
	private $mes;
	private $anio;
	private $fecha_inicio; 
	private $fecha_final;
	private $dias_mes; // Los dias que hay en el mes
	private $costos_aulas;
	private $array_datos_eventos;
	private $cecos_no_contables; // estos centros de costos no se suman como ocupacion sino como horas ociosas
	private $query;


	public function __construct($mes, $anio, $area_id, $conexion)  
	{
		$this->mes = $mes;
		$this->anio = $anio;
		$this->area_id = $area_id;
		$this->conexion = $conexion;
		$this->fecha_inicio = strtotime($this->anio.'-'.$this->mes.'-1 00:00:00 P');
		$this->fecha_final = strtotime($this->anio.'-'.$this->mes.'-'.date("t", $this->fecha_inicio).' 23:59:00');
		$this->dias_mes = date("t", $this->fecha_inicio);
		$this->query ="SELECT plan_room.id as room_id, plan_entry.id as entry_id, plan_room.*, plan_area.*, plan_entry.*, null as penalizado, null as fecha_cancelado, null as penalizado_resuelto FROM plan_entry 
							  inner join plan_room on plan_room.id=plan_entry.room_id 
							  inner join plan_area on plan_area.id=plan_room.area_id 
							  WHERE start_time >='$this->fecha_inicio' AND end_time <='$this->fecha_final' AND
							  area_id = '$area_id' 
					    UNION
					    	  SELECT plan_room.id as room_id, plan_penalizacion.id as entry_id, plan_room.*, plan_area.*, plan_penalizacion.* FROM plan_penalizacion
							  inner join plan_room on plan_room.id=plan_penalizacion.room_id 
							  inner join plan_area on plan_area.id=plan_room.area_id 
							  WHERE start_time >='$this->fecha_inicio' AND end_time <='$this->fecha_final' AND
							  area_id = '$area_id' AND
							  plan_penalizacion.penalizado = 1 
							  order by start_time";
		
		$this->cecos_no_contables = array();
		$this->generateData();

		

	}

	
	public function addCecoNoContable($ceco)
	{
		array_push($this->cecos_no_contables, $ceco);
		
	}

	public function generateData()
	{
		$this->costos_aulas = $this->getArrayCostos();
		$this->array_datos_eventos = $this->getArrayHorasOciosasAula();
	}

	private function getArrayCostos()
	{
		/* Array de costos

		0: Enero - Marzo
		1: Abril - Septiembre
		2: Octubre - diciembre

		*/

		$registros = mysql_query("SELECT * FROM plan_configuration ",$this->conexion) or die("Problemas en el select:".mysql_error()); 
		$reg = mysql_fetch_array($registros);
		$costos = array();
		for ($i = 1; $i < 13; $i++) :
			if ($i >=0 && $i <= 3) // Seria Enero- Marzo
				$costos[$i] = $reg['costo_ene_mar'];
			if ($i >=4 && $i <= 9) // Seria Abril- Septiembre
				$costos[$i] = $reg['costo_abr_sep'];
			if ($i >=10 && $i <= 12) // Seria Abril- Septiembre
				$costos[$i] = $reg['costo_oct_dic'];
		endfor;
		return $costos;
	}

	public function getHorasByCeco($ceco)
	{
		$query = "SELECT *, null as penalizado, null as fecha_penalizacion, null as penalizado_resuelto FROM plan_entry 
				  WHERE start_time >='$this->fecha_inicio' AND end_time <='$this->fecha_final' AND
				  ceco = '$ceco'
				  UNION
				  SELECT * FROM plan_penalizacion 
				  WHERE start_time >='$this->fecha_inicio' AND end_time <='$this->fecha_final' AND
				  penalizado = 1 AND
				  ceco = '$ceco'
				  order by start_time";
		$registros = mysql_query($query,$this->conexion) or die("Problemas en el select:".mysql_error()); 
		$horas = 0;
		while ($reg = mysql_fetch_array($registros))
			$horas += $this->horasEntre($reg['start_time'], $reg['end_time']);	
		
		return $horas;
	}


	public function getArrayHorasOciosasAula() 
	{
		$horas_ociosas = array();
		$array_aulas = array();
		 
		$registros = mysql_query($this->query,$this->conexion) or die("Problemas en el select:".mysql_error());

		while ($reg = mysql_fetch_array($registros)) :

			$room_id = $reg['room_id'];
			$date = date("Y-m-d",$reg['start_time']); 
			$dia_semana = date("w", strtotime($fecha));
			

			if (!$horas_ociosas[$room_id][$date])
				if ($dia_semana != 6){ // No es sabado
					if (!in_array(strtolower($reg['ceco']), $this->cecos_no_contables)) // Si el ceco no debe contabilizarse, entonces quedan como ociosas
						$horas_ociosas[$room_id][$date] = array('ociosas' => 14 - $this->horasEntre($reg['start_time'], $reg['end_time']), 'ocupadas' => $this->horasEntre($reg['start_time'], $reg['end_time']) );
					$horas_ociosas[$room_id][$date]['eventos'] = array($reg['entry_id'] => $this->horasEntre($reg['start_time'], $reg['end_time']));
				}
				else{
					if (!in_array(strtolower($reg['ceco']), $this->cecos_no_contables))
						$horas_ociosas[$room_id][$date] = array('ociosas' => 6 - $this->horasEntre($reg['start_time'], $reg['end_time']), 'ocupadas' => $this->horasEntre($reg['start_time'], $reg['end_time']));
					$horas_ociosas[$room_id][$date]['eventos'] = array($reg['entry_id'] => $this->horasEntre($reg['start_time'], $reg['end_time']));
					// Me puede dar negativo porque el sabado cuento solo de 8 a 14 pero puede estar ocupado todo el dia.
					if ($horas_ociosas[$date] < 0)
						$horas_ociosas[$date] = 0; // No hay horas ociosas
				}
			

			else {
				$count = $horas_ociosas[$room_id][$date]['ociosas'];
				$count_ocupadas = $horas_ociosas[$room_id][$date]['ocupadas'];
				if ($dia_semana != 6){ // No es sabado
					if (!in_array(strtolower($reg['ceco']), $this->cecos_no_contables)){
						$horas_ociosas[$room_id][$date]['ociosas'] = $count - $this->horasEntre($reg['start_time'], $reg['end_time']) ;
						$horas_ociosas[$room_id][$date]['ocupadas'] = $count_ocupadas + $this->horasEntre($reg['start_time'], $reg['end_time']);
					}
					$horas_ociosas[$room_id][$date]['eventos'][$reg['entry_id']] = $this->horasEntre($reg['start_time'], $reg['end_time']);
				}
				else{
					if (!in_array(strtolower($reg['ceco']), $this->cecos_no_contables)){
						$horas_ociosas[$room_id][$date]['ociosas'] = $count - $this->horasEntre($reg['start_time'], $reg['end_time']);
						$horas_ociosas[$room_id][$date]['ocupadas'] = $count_ocupadas + $this->horasEntre($reg['start_time'], $reg['end_time']);
					}
					$horas_ociosas[$room_id][$date]['eventos'][$reg['entry_id']] = $this->horasEntre($reg['start_time'], $reg['end_time']);
					// Me puede dar negativo porque el sabado cuento solo de 8 a 14 pero puede estar ocupado todo el dia.
					if ($horas_ociosas[$room_id][$date]['ociosas'] < 0)
						$horas_ociosas[$room_id][$date]['ociosas'] = 0; // No hay horas ociosas
				}
			}

			// Guardo el id del aula si no esta

			if (!in_array($reg['room_id'],$array_aulas))
				$array_aulas[] = $reg['room_id'];

		endwhile;

		// El paso siguiente seria ver los dias del mes que no fueron contabilizados ya puede pasar que un dia este ocioso completo

		// Veo cuantos dias tiene este mes

		
		foreach ($array_aulas as $aula_id) 
			
			for ($i = 1; $i <= $this->dias_mes; $i++) :

				$fecha_actual = date("Y",$this->fecha_inicio)."-".date("m",$this->fecha_inicio)."-".str_pad ( $i , 2, 0, STR_PAD_LEFT);
				
				$dia_semana = date("w", strtotime($fecha_actual));
				
				if ($dia_semana != 0) // Osea que no es Domingo
				{

					if (!$horas_ociosas[$aula_id][$fecha_actual]) // es un dia libre 
						if ($dia_semana != 6) // No es sabado
							$horas_ociosas[$aula_id][$fecha_actual]['ociosas'] = 14;
						else
							$horas_ociosas[$aula_id][$fecha_actual]['ociosas'] = 6;

				}

			endfor;
		/*echo "<pre>";	
	    print_r($horas_ociosas);
	    echo "</pre>";*/
		return $horas_ociosas;

	} 

	// Retorna el costo real que debe pagar un evento por las horas de utilizacion, segun los metros del aula

	public function getCostoTotal($metros, $horas)
	{
		
		$total_horas =  (($metros * $this->costos_aulas[$this->mes]) / 14) * $horas;
		
		return $total_horas;

	}

	// Retorna informacion general del mes: horas ociosas y horas ocupadas por cada evento

	public function getArrayGeneralDatos()
	{
		return $this->array_datos_eventos;
	}


	// Retorna el total de horas ociosas de un aula	



	public function getTotalHorasOciosasAula($aula_id)
	{
		$horas_libres = 0;
		
		if ($array_aula = $this->array_datos_eventos[$aula_id]){ // el aula estuvo libre todo el mes
			foreach ($array_aula as $horas) {

				$horas_libres += $horas['ociosas'];
			}
		}

		return $horas_libres;

	}

	public function getTotalHorasOcupadasAula($aula_id)
	{
		$horas_ocupadas = 0;
		
		if ($array_aula = $this->array_datos_eventos[$aula_id]){ // el aula estuvo libre todo el mes
			foreach ($array_aula as $horas) 
				
				$horas_ocupadas += $horas['ocupadas'];
			
		}
		
		return $horas_ocupadas;

	}

	public function getCostoHO($aula_id, $horas, $metros){

		$total_ociosas = $this->getTotalHorasOciosasAula($aula_id); 
		$total_ocupadas = $this->getTotalHorasOcupadasAula($aula_id); 
		
		// 104: 380hs libres aula : A
		if ($total_ocupadas)
			$porcentaje = $horas/$total_ocupadas; // el porcentaje de ocupacion
		else
			$porcentaje = 0;

		// 8 / hs libres
		$costo_monetario_horas_ociosas = (($metros * $this->costos_aulas[$this->mes]) / 14) * $total_ociosas;
		
		return 	($porcentaje * $costo_monetario_horas_ociosas);

	}

	// Retorna el mes 

	public function getMesString()
	{
		
		switch ($this->mes) {
		    case 1: return "Enero"; break;	
		    case 2: return "Febrero"; break;	
		    case 3: return "Marzo"; break;	
		    case 4: return "Abril"; break;	
		    case 5: return "Mayo"; break;	
		    case 6: return "Junio"; break;	
		    case 7: return "Julio"; break;	
		    case 8: return "Agosto"; break;	
		    case 9: return "Septiembre"; break;	
		    case 10: return "Octubre"; break;	
		    case 11: return "Noviembre"; break;	
		    case 12: return "Diciembre"; break;	
	    }

		return NULL;	 
	}

	// Retorna las horas entre dos horarios timestamp

	public function horasEntre($fecha1, $fecha2) // 2 timestamps
	{

		$segundos = $fecha2-$fecha1;
		return $segundos/60/60;

	}

	public function getArrayResumen($registros,$typel)
	{
		
		$array_resumen = array();
		
		
		while ($reg = mysql_fetch_array($registros)) : 
			$horas = $this->horasEntre($reg['start_time'], $reg['end_time']);
			$costo_real = $this->getCostoTotal($reg['mts'], $horas);
			$costo_ho = $this->getCostoHO($reg['room_id'], $horas, $reg['mts']);
			$costo_total = $costo_real + $costo_ho;

			$unidad = $typel[$reg['type']];
			
			if (!$array_resumen[$unidad][$reg['ceco']])
				$array_resumen[$unidad][$reg['ceco']] = $costo_total;
			else
				$array_resumen[$unidad][$reg['ceco']] = $array_resumen[$unidad][$reg['ceco']] + $costo_total;

			if ($array_resumen[$unidad]['suma'])
				$array_resumen[$unidad]['suma'] += $costo_total;
			else
				$array_resumen[$unidad]['suma'] = $costo_total;

		endwhile;

		return $array_resumen;

	}

	public function excelHeader($objTpl)
	{
		$objTpl = $this->cellColor($objTpl, 'A1:I1', '355BB6');
		$objTpl->getActiveSheet()->getStyle("A1:I1")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
		
		$objTpl->getActiveSheet()->setCellValue('A1', "EVENTO");
		$objTpl->getActiveSheet()->setCellValue('B1', "UNIDAD");
		$objTpl->getActiveSheet()->setCellValue('C1', "SALA");
		$objTpl->getActiveSheet()->setCellValue('D1', "FECHA");
		$objTpl->getActiveSheet()->setCellValue('E1', "HORAS");
		$objTpl->getActiveSheet()->setCellValue('F1', "CECO");
		$objTpl->getActiveSheet()->setCellValue('G1', "COSTO REAL");
		$objTpl->getActiveSheet()->setCellValue('H1', "COSTO HO");
		$objTpl->getActiveSheet()->setCellValue('I1', "TOTAL");
		
		$objTpl->getActiveSheet()->setCellValue('K1', "Etiquetas de Fila");
		$objTpl->getActiveSheet()->getStyle("K1")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
		$objTpl = $this->cellColor($objTpl, 'K1', '355BB6' );
		$objTpl->getActiveSheet()->setCellValue('L1', "Suma de Precio");
		$objTpl->getActiveSheet()->getStyle("L1")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
		$objTpl = $this->cellColor($objTpl, 'L1', '355BB6' );
	

		$objTpl->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objTpl->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objTpl->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$objTpl->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$objTpl->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$objTpl->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
		$objTpl->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
		$objTpl->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
		$objTpl->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
		$objTpl->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
		$objTpl->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);

		return $objTpl;
		
	}

	public function cellColor($objTpl,$cells,$color)
	{

		$objTpl->getActiveSheet()
		                ->getStyle($cells)
		                ->getBorders()
		                ->getAllBorders()
		                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
		                ->getColor()
		                ->setRGB('DDDDDD');	

	    $objTpl->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
	        'type' => PHPExcel_Style_Fill::FILL_SOLID,
	        'startcolor' => array(
	             'rgb' => $color
	        )
	    ));

	    return $objTpl;
	}

	public function getstyleArray()
	{
		return array(
		  'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_THIN
		    )
		  )
		);
	}

	public function generateExcel($objTpl,$typel)
	{
		$total_final = 0;
		$i = 0;
		$objTpl = $this->excelHeader($objTpl);
		
		$registros = mysql_query($this->query,$this->conexion) or die("Problemas en el select:".mysql_error());

		while ($reg = mysql_fetch_array($registros)) : 
			if (!in_array(strtolower($reg['ceco']), $this->cecos_no_contables)) :

				$horas = $this->horasEntre($reg['start_time'], $reg['end_time']);
				$costo_real = $this->getCostoTotal($reg['mts'], $horas);
				$costo_ho = $this->getCostoHO($reg['room_id'], $horas, $reg['mts']);
				$costo_total = $costo_real + $costo_ho;
				$total_final = $total_final + $costo_total; 

				$this->cellColor($objTpl,'A'.($i+2), $i % 2 ? 'D9E2F3' : 'FFFFFF' );
				$objTpl->getActiveSheet()->setCellValue('A'.($i+2), $reg['name']);
				$this->cellColor($objTpl,'B'.($i+2), $i % 2 ? 'D9E2F3' : 'FFFFFF' );
				$objTpl->getActiveSheet()->setCellValue('B'.($i+2), $typel[$reg['type']]);
				$this->cellColor($objTpl,'B'.($i+2), $i % 2 ? 'D9E2F3' : 'FFFFFF' );
				$objTpl->getActiveSheet()->setCellValue('C'.($i+2), $reg['room_name']);
				$this->cellColor($objTpl,'C'.($i+2), $i % 2 ? 'D9E2F3' : 'FFFFFF' );
				$objTpl->getActiveSheet()->setCellValue('D'.($i+2), date("d-m-Y",$reg['start_time']));
				$this->cellColor($objTpl,'D'.($i+2), $i % 2 ? 'D9E2F3' : 'FFFFFF' );
				$objTpl->getActiveSheet()->setCellValue('E'.($i+2), $horas);
				$this->cellColor($objTpl,'E'.($i+2), $i % 2 ? 'D9E2F3' : 'FFFFFF' );
				$objTpl->getActiveSheet()->setCellValue('F'.($i+2), ($reg['ceco']) ? $reg['ceco'] : "N/A");
				$this->cellColor($objTpl,'F'.($i+2), $i % 2 ? 'D9E2F3' : 'FFFFFF' );
				$objTpl->getActiveSheet()->setCellValue('G'.($i+2), '$'.number_format($costo_real, 2));
				$this->cellColor($objTpl,'G'.($i+2), $i % 2 ? 'D9E2F3' : 'FFFFFF' );
				$objTpl->getActiveSheet()->setCellValue('H'.($i+2), '$'.number_format($costo_ho, 2));
				$this->cellColor($objTpl,'H'.($i+2), $i % 2 ? 'D9E2F3' : 'FFFFFF' );
				$objTpl->getActiveSheet()->setCellValue('I'.($i+2), '$'.number_format($costo_total, 2));
				$this->cellColor($objTpl,'I'.($i+2), $i % 2 ? 'D9E2F3' : 'FFFFFF' );
				
				$objTpl->getActiveSheet()->getStyle('A'.($i+1).':I'.($i+2))->applyFromArray($this->getStyleArray());
				$i++;
			endif;
			

		endwhile;

		$horas_cai = $this->getHorasByCeco('cai');



		$objTpl->getActiveSheet()->setCellValue('A'.($i+2), 'TOTAL');
		$objTpl->getActiveSheet()->setCellValue('I'.($i+2), '$'.number_format($total_final, 2));
		$objTpl->getActiveSheet()->setCellValue('A'.($i+4), 'Horas CAI: '.$horas_cai);
	
		$i = 2;
		mysql_data_seek ( $registros , 0 );
		$array_resumen = $this->getArrayResumen($registros, $typel);
	    foreach ($array_resumen as $key => $value) : 
	    	$objTpl->getActiveSheet()->setCellValue('K'.($i), $key);
	    	$objTpl->getActiveSheet()->getStyle('K'.($i))->getFont()->setBold(true);
	    	$i++;
	    	foreach ($value as $ceco => $val) :

	    			if (($ceco != 'suma') && (!in_array(strtolower($ceco), $this->cecos_no_contables))) : 
	    				$objTpl->getActiveSheet()->setCellValue('K'.($i), $ceco);
	    				$objTpl->getActiveSheet()->setCellValue('L'.($i), "$".number_format($val,2));
	    				$objTpl->getActiveSheet()->getStyle('K'.($i))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	    				
	    			endif;
	    			if (!next($value)) : 
	    				$objTpl->getActiveSheet()->setCellValue('K'.($i), "TOTAL");
	    				$objTpl->getActiveSheet()->getStyle('K'.($i))->getFont()->setBold(true);
	    				$objTpl->getActiveSheet()->setCellValue('L'.($i), "$".number_format($value['suma'],2));
	    			
			    	endif; 
			    	$i++;

			    	
	    	 endforeach;

	    endforeach; 

	    return $objTpl;
	 
		
	}


	public function printTableMensual($typel)
	{
		?>

		<table id="pending_list" style="margin-top:20px; width:70%; float:left" class="admin_table">
				    <thead>
				        <tr>
				            <th class="header_start_time">Evento</th>  
				            <th class="header_name">Unidad</th>
				            <th class="header_area">Sala</th>
				            <th class="header_name">Fecha</th>
				            <th class="header_name">Horas</th>
				            <th class="header_name">CECO</th>
				            <th class="header_name">Costo Real</th>
				            <th class="header_name">Costo HO</th>
				            <th class="header_name">Total</th>
				        </tr>
				    </thead>
				    <tbody>
				    <?php
				   
				   $registros = mysql_query($this->query,$this->conexion) or die("Problemas en el select:".mysql_error());

					while ($reg = mysql_fetch_array($registros)) : 
						if (!in_array(strtolower($reg['ceco']), $this->cecos_no_contables)) :
						$horas = $this->horasEntre($reg['start_time'], $reg['end_time']);
						$costo_real = $this->getCostoTotal($reg['mts'], $horas);
						$costo_ho = $this->getCostoHO($reg['room_id'], $horas, $reg['mts']);
						$costo_total = $costo_real + $costo_ho;
						?>
						<tr>
					        <td class="header_start_time"><?php echo $reg['name']; ?></td>
					        <td class="header_name"><?php echo $typel[$reg['type']]; ?></td>
					        <td class="header_room"><?php echo $reg['room_name']; ?></td> 
					        <td class="header_name"><?php echo date("d-m-Y",$reg['start_time']); ?></td>
					        <td class="header_name"><?php echo $horas ?></td>
					        <td class="header_name"><?php echo ($reg['ceco']) ? $reg['ceco'] : "N/A"  ?></td>
					       
					        <td class="header_area">$ <?php echo number_format($costo_real, 2); ?></td>
					        <td class="header_area">$ <?php echo number_format($costo_ho, 2); ?></td>
					        <td class="header_area">$ <?php echo number_format($costo_total, 2); ?></td>
					     </tr>
					<?php
						endif;



					endwhile;
		?>
			</tbody>
		</table>
		<?php 
		
		mysql_data_seek ( $registros , 0 );
		$array_resumen = $this->getArrayResumen($registros, $typel);

		?>
		<table id="pending_list" style="margin-top:20px; margin-left:25px; width:27%; float:left" class="admin_table" >
			<thead>
		        <tr>
		            <th class="header_name">Etiquetas de fila</th>  
		            <th class="header_name">Suma de PRECIO</th>        
		        </tr>
		    </thead>
		    <tbody>
		    <?php 

		    foreach ($array_resumen as $key => $value) : ?>

		    	<tr>
		    		<td><strong><?php echo $key; ?></strong></td>
		    		<td>&nbsp;</td>
		    	<tr>
		    	<?php foreach ($value as $ceco => $val) :
		    			
		    			if ( ($ceco != 'suma') && (!in_array(strtolower($ceco), $this->cecos_no_contables))) : ?>
		    			<tr>
				    		<td style="text-align:right"><?php echo $ceco; ?></td>
				    		<td>$ <?php echo number_format($val,2); ?></td>
				    	<tr>
		    			<?php endif;
		    			if (!next($value)) : ?>
		    			<tr>
				    		<td style="text-align:right"><strong>TOTAL</strong></td>
				    		<td>$ <?php echo number_format($value['suma'],2); ?></td>
				    	<tr>
				    	<?php endif; ?>


		    		<?php endforeach;?>
		    <?php endforeach;?>
		    

		    </tbody>

		</table>
	<?php
	}

}

?>