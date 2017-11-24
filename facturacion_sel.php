<?php
require_once "defaultincludes.inc";
print_header(0, 0, 0, 0, "");
echo "<br>";
?>
<html>
<head>
<title>Planning Aulas</title>
</head>
<body>
<form action="facturacion.php" method="get"> 
<br> 
<h3>Facturaci&oacute;n</h3><br />
<div style="width:100%" />
	Año: 
	<select name="year">
	<option value="2015">2015</option> 
	<option value="2016" selected="selected">2016</option> 
	<option value="2017">2017</option> 
	<option value="2018">2018</option> 
	<option value="2019">2019</option> 
	<option value="2020">2020</option> 
	<option value="2021">2021</option> 
	<option value="2022">2022</option> 
	<option value="2023">2023</option> 
	<option value="2024">2024</option> 
	<option value="2025">2025</option> 

	
	</select> 
	Mes: 
	<select name="month"> 
	<option value="1">Enero</option> 
	<option value="2">Febrero</option> 
	<option value="3">Marzo</option> 
	<option value="4">Abril</option> 
	<option value="5">Mayo</option> 
	<option value="6">Junio</option> 
	<option value="7">Julio</option> 
	<option value="8">Agosto</option> 
	<option value="9">Septiembre</option> 
	<option value="10">Octubre</option> 
	<option value="11">Noviembre</option> 
	<option value="12">Diciembre</option> 
	</select> 
	Formato:
	<select name="csv"> 
	<option value="0">Web</option> 
	<option value="1">Excel</option> 
	</select> 
	Edificio:
	<select> 
	<option>Bs. As. - Nueva Sede Aulas</option> 
	</select> 

	<input type="submit" name="a" value="Obtener facturacion mensual"> 
</div>
<br />
<div style="width:100%;" />
	Año: 
	<select name="year2"> 
	<option value="2016" selected="selected">2016</option> 
	<option value="2017">2017</option> 
	<option value="2018">2018</option> 
	<option value="2019">2019</option> 
	<option value="2020">2020</option> 
	<option value="2021">2021</option> 
	<option value="2022">2022</option> 
	<option value="2023">2023</option> 
	<option value="2024">2024</option> 
	<option value="2025">2025</option> 
	
	</select> 
	Formato:
	<select name="csv2"> 
	<option value="0">Web</option> 
	<!--<option value="1">Excel</option> -->
	</select> 
	Edificio:
	<select> 
	<option>Bs. As. - Nueva Sede Aulas</option> 
	</select>
	<input type="submit" name="a" value="Obtener facturacion anual"> 
</div>

</form>  

</body>
</html>
