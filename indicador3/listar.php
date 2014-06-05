<?php 
$server = "190.82.89.44";
$user 	= "root";
$pwd 	= "m3g4m4nx23.,2013";
$bd     = "crmwebclass";
$cn = mysql_connect($server, $user, $pwd) or die("Error de conexion!");
mysql_select_db($bd, $cn);
mysql_query("SET CHARACTER SET utf8");
mysql_query("SET NAMES utf8");

$caso = $_POST['caso'];

switch($caso){
	case 1: 
		
		$sql ='SELECT max(historyid) as idmax FROM crmwebclass.vtiger_historyIDDE;';
		$query = mysql_query($sql);
		$row = mysql_fetch_array($query);
		
		$lastid = $row['idmax'];
		
		echo '<table width="100%">
			<thead>
            	<tr>
                	<th width="36"><div align="center">N°</div></th>
           	        <th width="146"><div align="center">ID Webclass</div></th>
                    <th width="111"><div align="center">Establecimiento</div></th>
                    <th width="81"><div align="center">Contacto</div></th>
					<th width="76"><div align="center">N° de Usuarios</div></th>
					<th width="63"><div align="center">N° de Apoderados</div></th>
                    <th width="76"><div align="center">N° de Alumnos</div></th>
                    <th width="100"><div align="center">N° de Profesores</div></th>
                    <th width="73"><div align="center">N° de Sostenedores</div></th>
					<th width="73"><div align="center">N° de UTP</div></th>
					<th width="123" class="sorttable_numeric"><div align="center">Frecuencia</div></th>
                    <th width="86" class="sorttable_numeric"><div align="center">Sin Acceso</div></th>
                    <th width="70" class="sorttable_numeric"><div align="center">Sin Uso</div></th>
                    <th width="73" class="sorttable_numeric"><div align="center">Bajo</div></th>
                    <th width="69" class="sorttable_numeric"><div align="center">Medio</div></th>
                    <th width="77" class="sorttable_numeric"><div align="center">Alto</div></th>
					<th width="86" class="sorttable_numeric"><div align="center">N° de Planificaciones</div></th>
                    <th width="70" class="sorttable_numeric"><div align="center">N° de Evaluaciones</div></th>
                    <th width="73" class="sorttable_numeric"><div align="center">N° de Planes Lectores</div></th>
                    <th width="69" class="sorttable_numeric"><div align="center">N° de Recursos</div></th>
                    <th width="77" class="sorttable_numeric"><div align="center">N° de Actividades</div></th>
					<th width="77" class="sorttable_numeric"><div align="center">Comentarios</div></th>
                </tr>
            </thead>
            <tbody>';
			
			$sql = 'select acc.accountid as idaccount, acc.accountname as establecimiento, acc.phone as telefono, acc.email1 as contacto1, acc.email2 as contacto2, 
			hia.colegioid as codigo_webclass, hia.fuso as uso, hia.psinacceso as sinacceso, hia.palto as alto, hia.pmedio as medio, hia.pbajo as bajo, 
			hia.psinuso as sinuso, hia.tusuarios as tusuarios, hia.tplanificaciones as tplanificaciones, hia.tevaluaciones as tevaluaciones, 
			hia.tplanlector as tplanlector, hia.trecursos as trecursos, hia.tactividades as tactividades, hia.tapoderados as tapoderados, 
			hia.talumnos as talumnos, hia.tprofesores as tprofesores, hia.tsostenedores as tsostenedores, hia.tutp as tutp 
			from vtiger_account acc 
			join vtiger_crmentity crm on crm.crmid = acc.accountid 
			join vtiger_historyIDDE_Accounts hia on hia.accountid = acc.accountid 
			join vtiger_historyIDDE hi on hi.historyid = hia.historydateid 
			where crm.deleted !=1 and hi.historyid = '.$lastid.' and acc.parentid !=1 order by hia.colegioid desc ';
			$query = mysql_query($sql);
			$row = mysql_fetch_array($query);
			$cont = 0;
			do{
				$cont = $cont+1;
				if($row['contacto1']==''){
					$contacto = $row['contacto2'];
				}else{
					$contacto = $row['contacto1'];	
				}
				$idwebclass = $row['codigo_webclass'];
				$establecimiento = $row['establecimiento'];
				$tusuarios = $row['tusuarios'];
				$tapoderados = $row['tapoderados'];
				$talumnos = $row['talumnos'];
				$tprofesores = $row['tprofesores'];
				$tsostenedores = $row['tsostenedores'];
				$tutp = $row['tutp'];
				$uso = $row['uso'];
				$sinacceso = $row['sinacceso'];
				$alto = $row['alto'];
				$medio = $row['medio'];
				$bajo = $row['bajo'];
				$sinuso = $row['sinuso'];
				$tplanificaciones = $row['tplanificaciones'];
				$tevaluaciones = $row['tevaluaciones'];
				$tplanlector = $row['tplanlector'];
				$trecursos = $row['trecursos'];
				$tactividades = $row['tactividades'];
			echo '<tr>
                	<td width="36"><div align="center">'.$cont.'</div></td>
                	<td width="146"><div align="center">'.$idwebclass.'</div></td>
                    <td width="111"><div align="center">'.$establecimiento.'</div></td>
                    <td width="81"><div align="center">'.$contacto.'</div></td>
					<td width="123"><div align="center">'.$tusuarios.'</div></td>
					<td width="76"><div align="center">'.$tapoderados.'</div></td>
                    <td width="86"><div align="center">'.$talumnos.'</div></td>
                    <td width="70"><div align="center">'.$tprofesores.'</div></td>
					<td width="73"><div align="center">'.$tsostenedores.'</div></td>
                    <td width="73"><div align="center">'.$tutp.'</div></td>
                    <td width="69"><div align="center">'.$uso.'</div></td>
                    <td width="77"><div align="center">'.$sinacceso.'</div></td>
                    <td width="63"><div align="center">'.$alto.'</div></td>
                    <td width="76"><div align="center">'.$medio.'</div></td>
                    <td width="100"><div align="center">'.$bajo.'</div></td>
                    <td width="73"><div align="center">'.$sinuso.'</div></td>
					<td width="77"><div align="center">'.$tplanificaciones.'</div></td>
                    <td width="63"><div align="center">'.$tevaluaciones.'</div></td>
                    <td width="76"><div align="center">'.$tplanlector.'</div></td>
                    <td width="100"><div align="center">'.$trecursos.'</div></td>
                    <td width="73"><div align="center">'.$tactividades.'</div></td>
					<td width="73"><div align="center"><a class="fancybox fancybox.iframe" href="comentarios.php?idrelated_to='.$row['idaccount'].'">Comentarios</a></div></td>
                </tr>';
				
			}while($row = mysql_fetch_array($query));
		echo '</tbody></table>';
	break;
}
?>