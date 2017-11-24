<?php

require_once "defaultincludes.inc";
require_once "classes/FacturacionMensual.class.php";


$conexion = mysql_connect($db_host,$db_login,$db_password) or die("Problemas en la conexion");
mysql_select_db($db_database,$conexion) or die("Problemas en la selección de la base de datos");


function getRequestType()
{
	
	if ($_REQUEST['csv'] == 0 && strpos($_GET['a'],'mensual')>0)
		return MENSUAL_WEB;
	if ($_REQUEST['csv'] != 0 && strpos($_GET['a'],'mensual')>0)
		return MENSUAL_EXCEL;
	if ($_REQUEST['csv2'] == 0 && strpos($_GET['a'],'anual')>0)
		return ANUAL_WEB;
	if ($_REQUEST['csv2'] != 0 && strpos($_GET['a'],'anual')>0)
		return ANUAL_EXCEL;

}

/* Facturacion */



$area_id = 14; // Solo para Bs As - Nueva Sede Aulas

$nombre_fichero = 'facturacion';


$csv=$_REQUEST['csv'];
$csv2=$_REQUEST['csv2'];
$fecha_inicio = strtotime($_REQUEST['year'].'-'.$_REQUEST['month'].'-1');
$fecha_final = strtotime($_REQUEST['year'].'-'.$_REQUEST['month'].'-'.date("t", $fecha_inicio));




if ((getRequestType() == MENSUAL_WEB) || (getRequestType() == ANUAL_WEB) ) :
	print_header(0, 0, 0, 0, ""); ?>
	<style type="text/css">

	.month-list{display: inline; cursor: pointer}
	.month-list li{list-style: none; display: inline-block; padding: 10px 20px; background-color: #4b667b; font-weight: bold; color: white}
	.hidden{display: none}

	</style>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript">

	$(function(){
		
		$('.month-box').filter('[data-month="1"]').show();
		$('.month-list li').click(function(){	
			$('.month-box').filter('[data-month]').hide();
			var month = $(this).data('month');

			$('.month-box').filter('[data-month="'+month+'"]').show();
		});

		
		
	})

	</script>
	
<?php

endif;

//Esto es importante para que nadie lo pueda ver si no es administrador

$user = getUserName();
$nivel=authGetUserLevel($user);
$sal = array();
$out = array();
if ($nivel<=2)
{
	echo '<span style="font-size:16px;"><br>No tiene permisos para ver esta pagina<br><br> </span>';
	exit;
}

// Si es Excel tengo que agregar las cabeceras sino da error al descargar
$facturacion = new FacturacionMensual($_REQUEST['month'], $_REQUEST['year'], $area_id, $conexion);
$facturacion->addCecoNoContable('feriado');
$facturacion->addCecoNoContable('cai');
$facturacion->addCecoNoContable('mantenimiento');
$facturacion->generateData();


if ((getRequestType() == MENSUAL_EXCEL) || (getRequestType() == ANUAL_EXCEL)) : 

	if (getRequestType() == MENSUAL_EXCEL)
		$filename = $facturacion->getMesString().$_GET['year'];
	if (getRequestType() == ANUAL_EXCEL)
		$filename = "Facturacion".$_GET['year'];
	$filename=$filename.'.xls'; 
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$filename.'"');
	header('Cache-Control: max-age=0');
endif;

/* ANUAL EXCEL */

if (getRequestType() == ANUAL_EXCEL) : 

	require_once "excel/PHPExcel/IOFactory.php";
	
	$objTpl = new PHPExcel();
	
	$sheet = 0;
	for ($mes = 1; $mes <= 12; $mes++) :
		$facturacion = new FacturacionMensual($mes, $_REQUEST['year'], $area_id, $conexion);
		$facturacion->addCecoNoContable('feriado');
		$facturacion->addCecoNoContable('cai');
		$facturacion->addCecoNoContable('mantenimiento');
		$facturacion->generateData();
		if ($sheet > 0)
		{
			$objTpl->createSheet();
			$objTpl->setActiveSheetIndex($sheet)->setTitle($facturacion->getMesString());
			$objTpl = $facturacion->generateExcel($objTpl,$typel);
		}
		else
		{
			$objTpl->setActiveSheetIndex(0)->setTitle($facturacion->getMesString());
			$objTpl = $facturacion->generateExcel($objTpl,$typel);
		}
		$sheet++;
		
	endfor;
		
	$objWriter = PHPExcel_IOFactory::createWriter($objTpl , 'Excel5');  
	$objWriter->save('php://output'); 
	exit; 
endif;



if (getRequestType() == MENSUAL_EXCEL) : 

	require_once "excel/PHPExcel/IOFactory.php";
	
	$objTpl = new PHPExcel();

	

	$objTpl = $facturacion->generateExcel($objTpl,$typel);
	$objWriter = PHPExcel_IOFactory::createWriter($objTpl, 'Excel5');  
	$objWriter->save('php://output'); 
	exit; 

endif; 


/* MENSUAL WEB */

//echo $facturacion->getTotalHorasOciosasAula(104);


if (getRequestType() == MENSUAL_WEB) : ?>

<pre>
<?php 

//echo $facturacion->getTotalHorasOcupadasAula(104);

//print_r($facturacion->getTotalHorasOciosasAula(104)); 

echo "<span style='font-size:14px'>Horas CAI: ".$facturacion->getHorasByCeco('cai')."</span>";

?>
</pre>

<h2>Facturaci&oacute;n <?php echo $facturacion->getMesString()." ".$_GET['year']; ?> </h2>
<?php $facturacion->printTableMensual( $typel ); ?>

<?php endif; 

/* ANUAL WEB */

if (getRequestType() == ANUAL_WEB) : ?>


<h2>Facturaci&oacute;n <?php echo $_GET['year']; ?> </h2>
<ul class="month-list">
		<li data-month="1">Enero</li>
		<li data-month="2">Febrero</li>
		<li data-month="3">Marzo</li>
		<li data-month="4">Abril</li>
		<li data-month="5">Mayo</li>
		<li data-month="6">Junio</li>
		<li data-month="7">Julio</li>
		<li data-month="8">Agosto</li>
		<li data-month="9">Septiembre</li>
		<li data-month="10">Octubre</li>
		<li data-month="11">Noviembre</li>
		<li data-month="12">Diciembre</li>
	</ul>
<?php

for ($i = 1; $i <= 12 ; $i++) : ?>

	<div class="month-box hidden"  data-month="<?php echo $i; ?>">
		<?php $facturacion = new FacturacionMensual($i, $_REQUEST['year'], $area_id, $conexion);
		$facturacion->addCecoNoContable('feriado');
		$facturacion->addCecoNoContable('cai');
		$facturacion->addCecoNoContable('mantenimiento');
		$facturacion->generateData();
		 $facturacion->printTableMensual($typel ); ?>
	</div>

<?php endfor;

endif; 





?>