<?php
$server = "190.82.89.44";
$user 	= "root";
$pwd 	= "m3g4m4nx23.,2013";
$bd     = "webclass_crm";
$cn = mysql_connect($server, $user, $pwd) or die("Error de conexion!");
mysql_select_db($bd, $cn);
mysql_query("SET CHARACTER SET utf8");
mysql_query("SET NAMES utf8");
$idaccount = $_GET['id'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Capacitaciones</title>
<link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
</head>
<style>
.datagrid table { border-collapse: collapse; text-align: left; } .datagrid {font: normal 12px/150% Verdana, Arial, Helvetica, sans-serif; background: #fff; overflow: hidden; border: 1px solid #006699; -webkit-border-radius: 7px; -moz-border-radius: 7px; border-radius: 7px; }.datagrid table td, .datagrid table th { padding: 6px 20px; }.datagrid table thead th {background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #006699), color-stop(1, #00557F) );background:-moz-linear-gradient( center top, #006699 5%, #00557F 100% );filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#006699', endColorstr='#00557F');background-color:#006699; color:#FFFFFF; font-size: 10px; font-weight: bold; border-left: 1px solid #0070A8; } .datagrid table thead th:first-child { border: none; }.datagrid table tbody td { color: #00496B; border-left: 1px solid #E1EEF4;font-size: 11px;font-weight: normal; }.datagrid table tbody .alt td { background: #E1EEF4; color: #00496B; }.datagrid table tbody td:first-child { border-left: none; }.datagrid table tbody tr:last-child td { border-bottom: none; }.datagrid table tfoot td div { border-top: 1px solid #006699;background: #E1EEF4;} .datagrid table tfoot td { padding: 0; font-size: 11px } .datagrid table tfoot td div{ padding: 2px; }.datagrid table tfoot td ul { margin: 0; padding:0; list-style: none; text-align: right; }.datagrid table tfoot  li { display: inline; }.datagrid table tfoot li a { text-decoration: none; display: inline-block;  padding: 2px 8px; margin: 1px;color: #FFFFFF;border: 1px solid #006699;-webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px; background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #006699), color-stop(1, #00557F) );background:-moz-linear-gradient( center top, #006699 5%, #00557F 100% );filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#006699', endColorstr='#00557F');background-color:#006699; }.datagrid table tfoot ul.active, .datagrid table tfoot ul a:hover { text-decoration: none;border-color: #006699; color: #FFFFFF; background: none; background-color:#00557F;}div.dhtmlx_window_active, div.dhx_modal_cover_dv { position: fixed !important; }
</style>

<body>
<div class="datagrid">
<table border="0" cellspacing="1" cellpadding="3">
  <thead>
  	<tr>
    	<th width="8%" align="center">Nombre de Sesion</th>
        <th width="8%" align="center">Asignado a</th>
        <th width="8%" align="center">Hora de Finalización</th>
        <th width="23%" align="center">Actitudes Observadas</th>
        <th width="26%" align="center">Asistencia y Horarios</th>
        <th width="16%" align="center">Observaciones</th>
        <th width="11%" align="center">Fecha de Creación</th>
    </tr>
   </thead>
   <tbody>
    <?php
		$sql = "SELECT cap.capacitacionid as capid, cap.cf_964 as nombre_sesion, concat(users.first_name,' ',users.last_name) as asignado_a, cap.cf_950 as hora_fin, cap.cf_961 as fecha_sesion, cap.cf_959 as observaciones, cap.cf_968 as actitudes_obs,
cap.cf_945 as asis_horari, crm.createdtime as fecha_creacion  FROM crmwebclass.vtiger_capacitacion cap
join crmwebclass.vtiger_crmentity crm ON crm.crmid = cap.capacitacionid 
join crmwebclass.vtiger_users users on users.id = crm.smownerid
 where crm.deleted !=1 and cap.linkto ='".$idaccount."'";
		$query = mysql_query($sql);
		$row = mysql_fetch_array($query);
		$cont =0;
		do{
			$cont = $cont+1;
			
			for ($i=0; $i < $cont; ++$i) {
				if ((($i % 2) == 0)||($cont == 1)) {
					$clase = 'class="alt"';
				}else{
					$clase = '';
				}
			}
	?>
    <tr <?php echo $clase; ?> >
      <td><a href="http://190.82.89.44/webclasscrm/index.php?module=Capacitacion&amp;parenttab=Tools&amp;action=DetailView&amp;record=<?php echo $row['capid']; ?>" title="Capacitacion" target="_blank"><?php echo $row['nombre_sesion']; ?></a></td>
      <td><?php echo $row['asignado_a']; ?></td>
      <td align="center"><?php echo $row['hora_fin']; ?></td>
      <td><?php echo $row['actitudes_obs']; ?></td>
      <td><?php echo $row['asis_horari']; ?></td>
      <td><?php echo $row['observaciones']; ?></td>
      <td align="center"><?php echo $row['fecha_creacion']; ?></td>
    </tr>
    <?php
    }while($row = mysql_fetch_array($query));
	mysql_close();
	?>
  </tbody>
</table>
</div>
</body>
</html>