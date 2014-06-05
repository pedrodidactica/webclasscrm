<?php 
$server = "190.82.89.44";
$user 	= "root";
$pwd 	= "m3g4m4nx23.,2013";
$bd     = "crmwebclass";
$cn = mysql_connect($server, $user, $pwd) or die("Error de conexion!");
mysql_select_db($bd, $cn);
mysql_query("SET CHARACTER SET utf8");
mysql_query("SET NAMES utf8");

$caso = $_REQUEST['case'];
$idaccount = $_REQUEST['idaccount'];
$colegio2 = $_REQUEST['colegio2'];
$fecha = date('dmY');

$sql = "SELECT max(historyid) as idmax FROM vtiger_historyIDDE";
$query = mysql_query($sql);
$row = mysql_fetch_array($query);

$actual = $row['idmax'];
switch($caso){
	case 1: 
		
		$sqlAccount ="select aco.accountid as accountid, aco.accountname as accountname 
		from vtiger_crmentity crm 
		join vtiger_account aco ON aco.accountid = crm.crmid where crm.deleted !=1 and aco.parentid !=1;";
		$queryAccount = mysql_query($sqlAccount);
		$rowAccount = mysql_fetch_array($queryAccount);
		
		echo '<option value="0">- Seleccione Establecimiento -</option>';
		echo '<option value="1">- Todos -</option>';
		do{
			echo '<option value="'.$rowAccount['accountid'].'">'.$rowAccount['accountname'].'</option>';
		}while($rowAccount = mysql_fetch_array($queryAccount));
	
	break;
	
	case 2: 
		
		$sqlAccount ="select aco.accountid as accountid, aco.accountname as accountname 
		from vtiger_crmentity crm 
		join vtiger_account aco ON aco.accountid = crm.crmid where crm.deleted !=1 and aco.parentid !=1 and aco.accountid != '".$colegio2."';";
		$queryAccount = mysql_query($sqlAccount);
		$rowAccount = mysql_fetch_array($queryAccount);
		
		echo '<option value="0">- Seleccione Establecimiento -</option>';
		do{
			echo '<option value="'.$rowAccount['accountid'].'">'.$rowAccount['accountname'].'</option>';
		}while($rowAccount = mysql_fetch_array($queryAccount));
	
	break;
	
	case 3: // total apoderados
		
		$sqlX ="select tapoderados from vtiger_historyIDDE_Accounts where accountid = '".$idaccount."' and historydateid = '".$actual."'";
		$queryX = mysql_query($sqlX);
		$rowX = mysql_fetch_array($queryX);
		
			
		$totalApoderados = $rowX['tapoderados'];
			
					
		echo $totalApoderados;
		
	break;
	
	case 4: // total alumnos
		
		$sqlX ="select talumnos from vtiger_historyIDDE_Accounts where accountid = '".$idaccount."' and historydateid = '".$actual."'";
		$queryX = mysql_query($sqlX);
		$rowX = mysql_fetch_array($queryX);
		
			
		$totalAlumnos = $rowX['talumnos'];
			
					
		echo $totalAlumnos;
		
	break;
	
	case 5: //total sostenedores
		
		$sqlX ="select tsostenedores from vtiger_historyIDDE_Accounts where accountid = '".$idaccount."' and historydateid = '".$actual."'";
		$queryX = mysql_query($sqlX);
		$rowX = mysql_fetch_array($queryX);
		
			
		$totalSostenedores = $rowX['tsostenedores'];
			
					
		echo $totalSostenedores;
		
	break;
	
	case 6: // total profesores
		
		$sqlX ="select tprofesores from vtiger_historyIDDE_Accounts where accountid = '".$idaccount."' and historydateid = '".$actual."'";
		$queryX = mysql_query($sqlX);
		$rowX = mysql_fetch_array($queryX);
		
			
		$totalProfesores = $rowX['tprofesores'];
			
					
		echo $totalProfesores;
		
	break;
	
	case 7: //total UTP
		
		$sqlX ="select tutp from vtiger_historyIDDE_Accounts where accountid = '".$idaccount."' and historydateid = '".$actual."'";
		$queryX = mysql_query($sqlX);
		$rowX = mysql_fetch_array($queryX);
		
			
		$totalUTP = $rowX['tutp'];
			
					
		echo $totalUTP;
		
	break;
}
?>