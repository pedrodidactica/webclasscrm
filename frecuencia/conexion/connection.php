<?php 
$server = "localhost";
$user	= "root";
$pwd    = "kr34v3nx23";
$bd     = "crm_principal";
$cn = mysql_connect($server, $user, $pwd) or die("Error de conexion!");
mysql_select_db($bd, $cn);
mysql_query("SET CHARACTER SET utf8");
mysql_query("SET NAMES utf8");
?>