<?php 
$server = "190.82.89.44";
$user	= "root";
$pwd    = "m3g4m4nx23.,2013";
$bd     = "crmwebclass";
$cn = mysql_connect($server, $user, $pwd) or die("Error de conexion!");
mysql_select_db($bd, $cn);
mysql_query("SET CHARACTER SET utf8");
mysql_query("SET NAMES utf8");

$id = $_GET['idrelated_to'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>:: FollowSac - 2014 ::</title>
<style type="text/css">
#body-div {
	position: absolute;
	left: 0px;
	top: 0px;
	width: 100%;
	height: 69px;
	z-index: 2;
}
</style>
</head>

<body>
<div id="body-div">
<div id="contenidos" class="datagrid">
  <table width="100%">
  	<thead>
    <tr>
      <th width="8%"><div align="center">ID</div></th>
      <th width="29%"><div align="center">USUARIO</div></th>
      <th width="46%"><div align="center">COMENTARIO</div></th>
      <th width="17%"><div align="center">FECHA</div></th>
    </tr>
    <thead>
    <tbody>
    <?php 
		$sql = "select * from vtiger_modcomments vtmodcom 
		join vtiger_modtracker_basic vtmodtrackbasic ON vtmodtrackbasic.crmid = vtmodcom.modcommentsid 
		join vtiger_users vtusers ON vtusers.id = vtmodtrackbasic.whodid 
		where vtmodcom.related_to = '".$id."' and vtmodtrackbasic.`status` != 0 order by vtmodcom.modcommentsid desc;";
		$query = mysql_query($sql);
		$row = mysql_fetch_array($query);
		do{
			if(($row['changedon']==NULL)||($row['changedon']=='')){
							$fecha = '';	
						}else{
							$fecha = date('d/m/Y H:i:s' ,strtotime($row['changedon']));
						}
	?>
    <tr>
      <td><div align="center"><?php echo $row['modcommentsid'];?></div></td>
      <td><div align="center"><?php echo $row['first_name'].' '.$row['last_name'];?></div></td>
      <td><div align="left"><?php echo $row['commentcontent'];?></div></td>
      <td><div align="center"><?php echo $fecha;?></div></td>
    </tr>
    <?php 
		}while($row = mysql_fetch_array($query));
	?>
    </tbody>
  </table>
</div>
</div>
</body>
</html>