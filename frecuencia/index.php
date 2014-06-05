<?php 
set_error_handler('ignore_divide_by_zero', E_WARNING);
$host = "190.153.216.245";
$user = "root";
$pass = "kr34v3nx23.,2013";

$dbname = "proyecto_webclass";
$connection = mysql_connect($host,$user,$pass) or die("Error de conexion!");;
mysql_select_db($dbname, $connection);
mysql_query("SET CHARACTER SET utf8");
mysql_query("SET NAMES utf8");

$total_users = 0;					
$totalG_SinAcceso = 0;
$totalG_SinUso = 0;
$totalG_Bajo = 0;
$totalG_Medio = 0;
$totalG_Alto = 0;
$totalP_SinAcceso = 0;
$totalP_SinUso = 0;
$totalP_Bajo = 0;
$totalP_Medio = 0;
$totalP_Alto = 0;
$porcentaje_SinAcceso = 0;
$porcentaje_SinUso = 0;
$porcentaje_Bajo = 0;
$porcentaje_Medio = 0;
$porcentaje_Alto = 0;
$cont1 = 0;
$cont = 0;

function ignore_divide_by_zero($errno, $errstring){
  return ($errstring == 'Division by zero');
}



function ExprecionTemporal($unixTime){

	$DifUnixTime = time() - $unixTime;
	$Agnios =  (int)($DifUnixTime / 31557600);
	$rAgnios =  (int)($DifUnixTime % 31557600);	
	$Meses =  (int)($rAgnios / 2678400);
	$rMeses =  (int)($rAgnios % 2678400);	
	$Dias =  (int)($rMeses / 86400);
	$rDias =  (int)($rMeses % 86400);
	$Horas =  (int)($rDias / 3600);
	$rHoras =  (int)($rDias % 3600);	
	$Minutos =  (int)($rHoras / 60);
	$rMinutos =  (int)($rHoras % 60);	
	if($Agnios==0){ $ano='';}else{$ano=$Agnios." año(s), ";}
	if($Meses==0){ $mes='';}else{$mes=$Meses." mes(es), ";}
	if($Dias==0){ $dia='';}else{$dia=$Dias." dia(s), ";}	
	if($Horas==0){ $hora='';}else{$hora=$Horas." hora(s), ";}
	if($Minutos<=1){ $minuto='1 minuto aprox.';}else{$minuto=$Minutos." minutos aprox.";}	
	$exprecion .= " Hace ".$ano.$mes.$dia.$hora.$minuto;
	return $exprecion;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body>
<div id="div-top">
	<div id="div-resultados"></div>
</div>
<div id="div-filter"></div>
<div id="div-header">
	<div id="contenido" class="datagrid">
   	  <table class="sortable" width="100%">
			<thead>
            	<tr>
                	<th width="36"><div align="center">N°</div></th>
           	        <th width="146"><div align="center">CRM | Webclass</div></th>
                    <th width="111"><div align="center">Cliente</div></th>
                    <!-- <th width="113"><div align="center">SAC Responsable</div></th> -->
                    <th width="81"><div align="center">Consultor</div></th>
					<th width="123"><div align="center">Frecuencia</div></th>
					<td width="76"><div align="center">N° de Usuarios</div></th>
                    <td width="86" class="sorttable_nosort"><div align="center">Sin Acceso</div> <div id="totalpsa" align="center"></div></td>
                    <td width="70" class="sorttable_nosort"><div align="center">Sin Uso</div> <div id="totalpsu" align="center"></div></td>
                    <td width="73" class="sorttable_nosort"><div align="center">Bajo</div> <div id="totalpb" align="center"></div></td>
                    <td width="69" class="sorttable_nosort"><div align="center">Medio</div> <div id="totalpm" align="center"></div></td>
                    <td width="77" class="sorttable_nosort"><div align="center">Alto</div> <div id="totalpa" align="center"></div></td>
                    
                    <th width="63"><div align="center">Estado</div></th>
                    <th width="76"><div align="center">Fecha Venc. Contrato</div></th>
                    <th width="100"><div align="center">Fecha Ultimo Contacto</div></th>
                    
                    <th width="73"><div align="center">N° Pruebas</div></th>
                </tr>
            </thead>
            <tbody>
            <?php 
				$sql = "select * from colegio where id > 1000 and visible != 0 and rbd != '' order by id desc;";
				$query = mysql_query($sql);
				$row = mysql_fetch_array($query);
				$idcolegio = 0;
				$Tusuarios = 0;
				do{
					$idcolegio = $row['id'];
					$sql1 = "select count(*) as total_usuarios from usuario where colegio = '".$idcolegio."' and (rol = 13 or rol = 12) and visible !=0";
					$query1 = mysql_query($sql1);
					$row1 = mysql_fetch_array($query1);
					$Tusuarios = $row1['total_usuarios'];
					
					$periodoDias = 60;
					$diasHabiles = 44;
					$hasta = time();
					$intervalo = $periodoDias * (3600*24);   
					$desde = $hasta - $intervalo;
					mysql_query("call sp_calculo_IDDE($idcolegio,$desde,$hasta)");
					
					$sql3 = "SELECT * FROM Datos_IDDE order by id desc;";
					$query3 = mysql_query($sql3);
					$row3 = mysql_fetch_array($query3);
					
					$total_Planificaciones =0;
					$total_Preguntas = 0;
					$total_Recursos = 0;
					$SinAcceso = 0;
					$SinUso = 0;
					$Bajo = 0;
					$Medio = 0;
					$Alto = 0;
					$folio = $row1['total_usuarios'];
					$sumUso = 0;
					$sumUsoFrec=0;
					
					$sqlE = "select * from test t 
						join test_alumno ta ON ta.test = t.id 
						where (t.colegio = '".$idcolegio."' or t.colegio = 14)
						and ta.t_inicio !=0 
						and t.visible!=0
						and ta.realizado=1
						group by ta.test";
						$queryE = mysql_query($sqlE);
						$rowE = mysql_num_rows($queryE);
					
						$total_evaluaciones = $rowE;
					do{
						
						
						 if($row3['inicio'] != "" ){
							$ultAcceso = ExprecionTemporal($row3['inicio']);
						}else{
							$ultAcceso = "";
						}
	
						$d = $desde;
						$DiasUso = 0;

						for($i=1; $i<=$periodoDias; $i++){
							$h = $d + (3600*24);
											
						$sql5 = "SELECT COUNT(usuario) AS total FROM log 
						WHERE usuario = '".$row3['id']."' AND inicio >= '".$d."' AND inicio <= '".$h."'";
						$query5 = mysql_query($sql5);
						$row5 = mysql_fetch_array($query5);
				
						if($row5['total'] > 0){
							$DiasUso++;
						}
						$d = $h;
						}
	
						$sql6 = "SELECT MIN(inicio) AS primerlog FROM log WHERE usuario = '".$row3['id']."'";
						$query6 = mysql_query($sql6);
						$row6 = mysql_fetch_array($query6);
	
						$primerlog = $row6['primerlog'];
						$permanencia = $hasta - $primerlog;
	
	if($permanencia > $intervalo){ $tiempo = $diasHabiles; } else { $tiempo = ($permanencia/86400); if($tiempo > 0 &&  $tiempo < 1){ $tiempo = 1; } $tiempo = (int)$tiempo; }
	
	$frecuencia = round(($DiasUso / $tiempo),4);
	$frecuenciaPorcentual = $frecuencia*100;
	if($frecuenciaPorcentual >= 100){ $frecuenciaPorcentual = 100; }
	$sumUso = $sumUso + $DiasUso;
	$sumUsoFrec = $sumUsoFrec + $frecuencia; 	
	
/******************************************************************************************/
						$sin_acceso = 0;			
						if($primerlog){
							if($frecuencia > 0.6667)
							//if($DiasUso > 40)  // Alto
							{
								$imagen = '<img src="img/alto.png" width="25" height="25" />';
								$texto = '<span class="alto" style="color:green;">Alto</span>';
							}else if($frecuencia > 0.1333 && $frecuencia <= 0.6667){
							//}else if($DiasUso > 8 && $DiasUso <= 40){   // Medio
								$imagen = '<img src="img/medio.png" width="25" height="25" />';
								$texto = '<span class="medio" style="color:orange;">Medio</span>';
							}else if($frecuencia >= 0.0167 && $frecuencia <= 0.1333){
							//}else if($DiasUso >= 1 && $DiasUso <= 8){   // Bajo
								$imagen = '<img src="img/bajo.png" width="25" height="25" />';
								$texto = '<span class="bajo" style="color:red;">Bajo</span>';
							}else{ // Sin Uso
								$imagen = '<img src="img/sin_uso.png" width="25" height="25" />';
								$texto = '<span class="nulo" style="color:#000;">Sin Uso</span>';
							}
							}else{  // Sin Acceso
								$imagen = '<img src="img/sin_acceso.png" width="25" height="25" />';
								$texto = '<span class="vacio" style="color:#000;">Sin Acceso</span>';
								$sin_acceso ++;
							}		
						$total_Planificaciones = $total_Planificaciones + $row3['planificacion'];
						$total_Preguntas = $total_Preguntas + $row3['pregunta'];
						$total_Recursos = $total_Recursos + $row3['recursos'];
	
						if(($texto=='<span class="vacio" style="color:#000;">Sin Acceso</span>')&&($row3['id']!='')){
							$SinAcceso = $SinAcceso+1;	
						}	
						if($texto=='<span class="nulo" style="color:#000;">Sin Uso</span>'){
							$SinUso = $SinUso+1;
						} 
						if($texto=='<span class="bajo" style="color:red;">Bajo</span>'){
							$Bajo = $Bajo+1;	
						}
						if($texto=='<span class="medio" style="color:orange;">Medio</span>'){
							$Medio = $Medio+1;	
						}
						if($texto=='<span class="alto" style="color:green;">Alto</span>'){
							$Alto = $Alto+1;
						}
					}while($row3 = mysql_fetch_array($query3));
					
					//$frec_new = round(($tfrec / $row1['total_usuarios']),2);
	
					if(($SinAcceso=='')){
						$porcentaje_SinAcceso = 0;
					}else{
						$porcentaje_SinAcceso = round((($SinAcceso*100)/$row1['total_usuarios']),2);
					}
					if($SinUso==''){
						$porcentaje_SinUso = 0;
					}else{
						$porcentaje_SinUso = round((($SinUso*100)/$row1['total_usuarios']),2);
					}
					if($Bajo==''){
						$porcentaje_Bajo = 0;
					}else{
					$porcentaje_Bajo = round((($Bajo*100)/$row1['total_usuarios']),2);
					}
					if($Medio==''){
						$porcentaje_Medio = 0;
					}else{
						$porcentaje_Medio = round((($Medio*100)/$row1['total_usuarios']),2);
					}
					if($Alto==''){
						$porcentaje_Alto = 0;
					}else{
						$porcentaje_Alto = round(($Alto*100)/$row1['total_usuarios'],2);
					}
					
					
										
					$sqlC = "select * from crm_principal.vtiger_account a 
					join crm_principal.vtiger_accountscf b ON b.accountid = a.accountid
					join crm_principal.vtiger_crmentity c ON c.crmid = b.accountid 
					where c.deleted!=1 and b.cf_640 = '".$row['rbd']."' group by a.accountid order by a.accountid desc;";
					$queryC = mysql_query($sqlC);
					$rowC = mysql_fetch_array($queryC);
					if(($rowC!=NULL)||($rowC!='')){
					do{
						$sqlComm = "select * from crm_principal.vtiger_modcomments vtmodcom 
						join crm_principal.vtiger_modtracker_basic vtmodtrackbasic ON vtmodtrackbasic.crmid = vtmodcom.modcommentsid
						join crm_principal.vtiger_users vtusers ON vtusers.id = vtmodtrackbasic.whodid
						where vtmodcom.related_to = '".$rowC['accountid']."' order by vtmodcom.modcommentsid desc;";
						$queryComm = mysql_query($sqlComm);
						$rowComm = mysql_fetch_array($queryComm);
						
						if(($rowComm['changedon']==NULL)||($rowComm['changedon']=='')){
							$fecha = '';	
						}else{
							$fecha = date('d/m/Y',strtotime($rowComm['changedon']));
						}
						
						
						$cont = $cont +1;
						$cont1 = $cont1+1;
						$clase = '';
				
						for ($i=0; $i < $cont; ++$i) {
							if ((($i % 2) == 0)||($cont == 1)) {
								$clase = 'class="alt"';
							}else{
								$clase = '';
							}
						}

			?>
            	<tr <?php echo $clase; ?>>
                	<td width="36"><div align="center"><?php echo $cont;?></div></td>
                	<td width="146"><div align="center"><?php echo $rowC['accountid'].' | '.$row['id'];?></div></td>
                    <td width="111"><div align="left"><a href="../modules/Accounts/frecuencia/detalle_clientes.php?id=<?php echo $idcolegio;?>"><?php echo $rowC['accountname'];?></a></div></td>
                   <!-- <td width="113"><div align="center">&nbsp;</div></td> -->
                    <td width="81"><div align="center"><?php echo $rowC['cf_918'];?></div></td>
					<td width="123"><div align="center">
                      <?php 
					  $promedioUso = round(($sumUso/$folio),0);
					  $promedioUsoFrec = round(($sumUsoFrec/$folio),2);
						$frecuenciaPorcentual = $promedioUsoFrec*100;
						if($frecuenciaPorcentual >= 100){ $frecuenciaPorcentual = 100; }					  
					  
					echo $frecuenciaPorcentual." %";
					 
				//echo '<img src="images/sin_acceso.png" width="25" height="25" />
				//<span class="vacio" style="color:#000;">Sin Acceso</span>';
					
				?>
                    </div></td>
					<td width="76"><div align="center"><?php echo $row1['total_usuarios'];?></div></td>
                    <td width="86"><div align="center"><?php 
					if($SinAcceso>0){
					echo '<font color="#FF0000">'.$SinAcceso.'</font>';
					}else{echo $SinAcceso;}
					?> (<?php 
					if($porcentaje_SinAcceso>0){
					echo '<font color="#FF0000">'.$porcentaje_SinAcceso.'%</font>';
					}else{echo $porcentaje_SinAcceso.'%';}
					?>)</div></td>
                    <td width="70"><div align="center"><?php echo $SinUso;?> (<?php echo $porcentaje_SinUso.'%';?>)</div></td>
                    <td width="73"><div align="center"><?php echo $Bajo;?> (<?php echo $porcentaje_Bajo.'%';?>)</div></td>
                    <td width="69"><div align="center"><?php echo $Medio;?> (<?php echo $porcentaje_Medio.'%';?>)</div></td>
                    <td width="77"><div align="center"><?php echo $Alto;?>  (<?php echo $porcentaje_Alto.'%';?>)</div></td>
                    <td width="63"><div align="center"><?php echo $rowC['cf_985']?></div></td>
                    <td width="76"><div align="center"><?php echo date('d/m/Y', strtotime($rowC['cf_983']));?></div></td>
                    <td width="100"><div align="center"><a class="fancybox fancybox.iframe" href="../modules/Accounts/frecuencia/detalle_comentario.php?idrelated_to=<?php echo $rowC['accountid'];?>"><?php echo $fecha;?></a></div></td>
                    <td width="73"><div align="center"><?php 
					if($total_evaluaciones==0){
						echo $total_evaluaciones;
					}elseif($total_evaluaciones>0){
						echo '<a class="fancybox fancybox.iframe" href="detalle_evaluaciones.php?id='.$idcolegio.'">'.$total_evaluaciones.'</a>';		
					}
					
					
					?></div></td>
                </tr>
               <?php 
			   	  mysql_query("UPDATE crm_principal.vtiger_accountscf SET cf_1037 = '$frecuenciaPorcentual', cf_1038 = '$porcentaje_SinAcceso', cf_1045 = '$Tusuarios', cf_1042 = '$porcentaje_Alto', cf_1041 = '$porcentaje_Medio', cf_1040 = '$porcentaje_Bajo', cf_1039 = '$porcentaje_SinUso', cf_1048 = '$total_evaluaciones', cf_1043 = '$total_Planificaciones', cf_1046 = '$total_Recursos', cf_1049 = '$idcolegio' WHERE accountid = '".$rowC['accountid']."';"); 
					}while($rowC = mysql_fetch_array($queryC));
				}								
						
						//$cont = $cont +1;
						$totalG_SinAcceso = $totalG_SinAcceso + $SinAcceso;
						$totalG_SinUso = $totalG_SinUso + $SinUso;
						$totalG_Bajo = $totalG_Bajo + $Bajo;
						$totalG_Medio = $totalG_Medio + $Medio;
						$totalG_Alto = $totalG_Alto + $Alto;

			 			$total_users = $total_users + $row1['total_usuarios'];

					}while($row = mysql_fetch_array($query));	

						$totalP_SinAcceso =  round((($totalG_SinAcceso*100)/$total_users),2);
						$totalP_SinUso = round((($totalG_SinUso*100)/$total_users),2);
						$totalP_Bajo = round((($totalG_Bajo*100)/$total_users),2);
						$totalP_Medio = round((($totalG_Medio*100)/$total_users),2);
						$totalP_Alto = round((($totalG_Alto*100)/$total_users),2);
			   ?>
               </tbody>
	  </table>
      <div>
      <table width="100%" class="fonts">
      	<tr>
        	<th width="378" bgcolor="#006699"><strong></strong><div align="center"><strong>TOTAL GENERAL</strong></div></th>
   	      <!-- <th width="10%" bgcolor="#BFBFBF"><div align="center"></div></th> -->
	   	    <th width="101" bgcolor="#006699"><div align="center"><strong><?php echo 'Sin Acceso<p>&nbsp;</p>'; echo $totalG_SinAcceso.'<p>&nbsp;</p>';
				if($totalP_SinAcceso>0){
					echo '<font color="#FF0000">'.$totalP_SinAcceso.'%</font><p>&nbsp;</p>';
				}else{echo '<font color="#FFFFFF">'.$totalP_SinAcceso.'%</font><p>&nbsp;</p>';}
			?></strong></div></th>
   	    	<th width="67" bgcolor="#006699"><div align="center"><strong><?php echo 'Sin Uso<p>&nbsp;</p>'; echo $totalG_SinUso.'<p>&nbsp;</p>';
				if($totalP_SinUso>0){
					echo '<font color="#FF0000">'.$totalP_SinUso.'%</font><p>&nbsp;</p>';
				}else{echo '<font color="#FFFFFF">'.$totalP_SinUso.'%</font><p>&nbsp;</p>';}
			?></strong></div></th>
   	    	<th width="69" bgcolor="#006699"><div align="center"><strong><?php echo 'Bajo<p>&nbsp;</p>'; echo $totalG_Bajo.'<p>&nbsp;</p>';
				if($totalP_Bajo>0){
					echo '<font color="#FF0000">'.$totalP_Bajo.'%</font><p>&nbsp;</p>';
				}else{echo '<font color="#FFFFFF">'.$totalP_Bajo.'%</font><p>&nbsp;</p>';}	
			?></strong></div></th>
   	    <th width="76" bgcolor="#006699"><div align="center"><strong><?php echo 'Medio<p>&nbsp;</p>'; echo $totalG_Medio.'<p>&nbsp;</p>';
				if($totalP_Medio>0){
					echo '<font color="#FF0000">'.$totalP_Medio.'%</font><p>&nbsp;</p>';
				}else{echo '<font color="#FFFFFF">'.$totalP_Medio.'%</font><p>&nbsp;</p>';}
			?></strong></div></th>
   	    <th width="62" bgcolor="#006699"><div align="center"><strong><?php echo 'Alto<p>&nbsp;</p>'; echo $totalG_Alto.'<p>&nbsp;</p>';
				if($totalP_Alto>0){
					echo '<font color="#FF0000">'.$totalP_Alto.'%</font><p>&nbsp;</p>';
				}else{echo '<font color="#FFFFFF">'.$totalP_Alto.'%</font><p>&nbsp;</p>';}
			?></strong></div></th>
   	    <th width="97" bgcolor="#006699"><div align="center"><strong><?php echo 'Usuarios<br>'; echo $total_users;?></strong></div></th>
   	    <th width="272" bgcolor="#006699"><div align="center"></div></th>
       	  <th width="162" bgcolor="#006699"><div align="center"></div></th>
       	  <th width="162" bgcolor="#006699">&nbsp;</th>
   	    </tr>
  	  </table>
  	</div>
  </div>
</div>

<input name="totalC" id="totalC" type="hidden" value="<?php echo $cont1;?>" />
<input name="totalU" id="totalU" type="hidden" value="<?php echo $total_users;?>" />
<input name="totalSA" id="totalSA" type="hidden" value="<?php echo $totalG_SinAcceso;?>" />
<input name="totalSU" id="totalSU" type="hidden" value="<?php echo $totalG_SinUso;?>" />
<input name="totalB" id="totalB" type="hidden" value="<?php echo $totalG_Bajo;?>" />
<input name="totalM" id="totalM" type="hidden" value="<?php echo $totalG_Medio;?>" />
<input name="totalA" id="totalA" type="hidden" value="<?php echo $totalG_Alto;?>" />
<input name="psa" id="psa" type="hidden" value="<?php echo $totalP_SinAcceso;?>" />
<input name="psu" id="psu" type="hidden" value="<?php echo $totalP_SinUso;?>" />
<input name="pb" id="pb" type="hidden" value="<?php echo $totalP_Bajo;?>" />
<input name="pm" id="pm" type="hidden" value="<?php echo $totalP_Medio;?>" />
<input name="pa" id="pa" type="hidden" value="<?php echo $totalP_Alto;?>" />
</body>
</html>