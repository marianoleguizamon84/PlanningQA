<h2>UA Main Site DB test</h2>
<?php

$hostname = "10.70.200.55"; 
$username = "webplatform";
$password = "UA.garay125";
$dbname = "test";
$table = "testtable";

//connection to the database server
$dbhandle = mysql_connect($hostname, $username, $password) 
  or die("Unable to connect to DB server: <b>$hostname</b> ");
echo "Connected to DB server <b>$hostname</b><br>";
?>

<?php
//select a database to work with
$selected = mysql_select_db($dbname, $dbhandle) 
  or die("Could not select DB <b>$dbname</b>");
?>

<?php
//execute the SQL query and return records
$result = mysql_query("SELECT * FROM $table");
//fetch tha data from the database
while ($row = mysql_fetch_array($result)) {
   echo "ID: ".$row{'id'}." Description: ".$row{'description'}."<br>";
}
?>


<?php
//close the connection
mysql_close($dbhandle);
?>

<hr>
<?php phpinfo(); ?>
