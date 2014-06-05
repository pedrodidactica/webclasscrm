<?php 
$server = "190.82.89.44";
$user 	= "root";
$pwd 	= "m3g4m4nx23.,2013";
$bd     = "crmwebclass";
$cn = mysql_connect($server, $user, $pwd) or die("Error de conexion!");
mysql_select_db($bd, $cn);
mysql_query("SET CHARACTER SET utf8");
mysql_query("SET NAMES utf8");
$idaccount = $_GET['idaccount'];
$idaccount2 = $_GET['idaccount2'];
$desde = str_replace("/", "", $_GET['desde']);
$hasta = str_replace("/", "", $_GET['hasta']);

if($desde[0]==0){
	$desdeX = substr($desde, 1);
	$desdeY = $desdeX;
}else{
	$desdeX = $desde;
	$desdeY = $desdeX;
}
if($hasta[0]==0){
	$hastaX = substr($hasta, 1);
	$hastaY = $hastaX;
}else{
	$hastaX = $hasta;
	$hastaY = $hastaX;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>:: Indicador de uso general ::</title>
</head>
<?php 
if (($idaccount2=='')||($idaccount2==0)){
?>
<script src="js/jsapi"></script>
      <script type="text/javascript">
	  google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Año', 'Uso', 'Sin Acceso', 'Sin Uso', 'Uso Bajo', 'Uso Medio', 'Uso Alto'],
		<?php 
			$sqlF ="select aco.accountname as cliente, hist.historydate, histacc.* from vtiger_historyIDDE hist 
			join vtiger_historyIDDE_Accounts histacc on histacc.historydateid = hist.historyid 
			join vtiger_account aco ON aco.accountid = histacc.accountid 
			where histacc.accountid = '".$idaccount."' and hist.historydate between '".$desdeX."' and '".$hastaX."';";
			$queryF = mysql_query($sqlF);
			$rowF = mysql_fetch_array($queryF);
			$totalReg = mysql_num_rows($queryF);
			$cliente = $rowF['cliente'];
			do{
		?>
		
			['<?php echo $rowF['historydate'];?>',<?php echo $rowF['fuso']?>,<?php echo $rowF['psinacceso']?>,<?php echo $rowF['psinuso']?>,<?php echo $rowF['pbajo']?>,<?php echo $rowF['pmedio']?>,<?php echo $rowF['palto']?>],
		
		<?php 
			}while($rowF = mysql_fetch_array($queryF));
		?>	
        ]);
        var options = {
          title: '<?php echo $cliente;?>',
		  legend: { position: 'top', maxLines: 3 }
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('chart_divX'));
        chart.draw(data, options);
	  }
        </script>
<body>
<div id="chart_divX" style="width: 900px; height: 450px;"></div>
</body>
<?php 
}else if($idaccount2!=0){
?>
<script src="js/jsapi"></script>
      <script type="text/javascript">
	  google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChartX);
	  google.setOnLoadCallback(drawChartY);
      function drawChartX() {
        var dataX = google.visualization.arrayToDataTable([
          ['Año', 'Uso', 'Sin Acceso', 'Sin Uso', 'Uso Bajo', 'Uso Medio', 'Uso Alto'],
		<?php 
			$sqlF ="select aco.accountname as cliente, hist.historydate, histacc.* from vtiger_historyIDDE hist 
			join vtiger_historyIDDE_Accounts histacc on histacc.historydateid = hist.historyid 
			join vtiger_account aco ON aco.accountid = histacc.accountid 
			where histacc.accountid = '".$idaccount."' and hist.historydate between '".$desdeX."' and '".$hastaX."';";
			$queryF = mysql_query($sqlF);
			$rowF = mysql_fetch_array($queryF);
			$totalReg = mysql_num_rows($queryF);
			$cliente = $rowF['cliente'];
			do{
		?>
		
			['<?php echo $rowF['historydate'];?>',<?php echo $rowF['fuso']?>,<?php echo $rowF['psinacceso']?>,<?php echo $rowF['psinuso']?>,<?php echo $rowF['pbajo']?>,<?php echo $rowF['pmedio']?>,<?php echo $rowF['palto']?>],
		
		<?php 
			}while($rowF = mysql_fetch_array($queryF));
		?>	
        ]);
        var options = {
          title: '<?php echo $cliente;?>',
		  legend: { position: 'top', maxLines: 3 }
        };
        var chartX = new google.visualization.ColumnChart(document.getElementById('chart_divX'));
        chartX.draw(dataX, options);
	  }
	  
	  function drawChartY() {
        var dataY = google.visualization.arrayToDataTable([
          ['Año', 'Uso', 'Sin Acceso', 'Sin Uso', 'Uso Bajo', 'Uso Medio', 'Uso Alto'],
		<?php 
			$sqlF ="select aco.accountname as cliente, hist.historydate, histacc.* from vtiger_historyIDDE hist 
			join vtiger_historyIDDE_Accounts histacc on histacc.historydateid = hist.historyid 
			join vtiger_account aco ON aco.accountid = histacc.accountid 
			where histacc.accountid = '".$idaccount2."' and hist.historydate between '".$desdeY."' and '".$hastaY."';";
			$queryF = mysql_query($sqlF);
			$rowF = mysql_fetch_array($queryF);
			$totalReg = mysql_num_rows($queryF);
			$cliente = $rowF['cliente'];
			do{
		?>
		
			['<?php echo $rowF['historydate'];?>',<?php echo $rowF['fuso']?>,<?php echo $rowF['psinacceso']?>,<?php echo $rowF['psinuso']?>,<?php echo $rowF['pbajo']?>,<?php echo $rowF['pmedio']?>,<?php echo $rowF['palto']?>],
		
		<?php 
			}while($rowF = mysql_fetch_array($queryF));
		?>	
        ]);
        var options = {
          title: '<?php echo $cliente;?>',
		  legend: { position: 'top', maxLines: 3 }
        };
        var chartY = new google.visualization.ColumnChart(document.getElementById('chart_divY'));
        chartY.draw(dataY, options);
	  }
        </script>
<body>
<div id="chart_divX" style="width: 900px; height: 450px;"></div>
<div id="chart_divY" style="width: 900px; height: 450px;"></div>
</body>
<?php 
}
?>
</html>