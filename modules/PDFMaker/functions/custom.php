<?php

function neto_total($id) {
global $adb;
		
$query="SELECT quantity , listprice, quantity*listprice AS neto ,vtiger_inventoryproductrel.discount_amount AS desc1 , quantity * listprice * vtiger_inventoryproductrel.discount_percent/100 AS desc2  FROM vtiger_quotes LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_quotes.quoteid LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventoryproductrel.productid WHERE vtiger_quotes.quoteid = ?";
$result = $adb->pquery($query, array($id));
$num_rows = $adb->num_rows($result);


$total = 0;


for ( $i=0 ; $i  <  $num_rows ;  $i++ ) {

$total  = $adb->query_result($result,$i,2) + $total  ;

}
$total = str_replace(' ', '',$total);
return number_format($total ,0, '', '.') ;
}




function descuentototal($id) {
global $adb;

$query="SELECT quantity , listprice, quantity*listprice AS neto ,vtiger_inventoryproductrel.discount_amount AS desc1 , quantity * listprice * vtiger_inventoryproductrel.discount_percent/100 AS desc2  FROM vtiger_quotes LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_quotes.quoteid LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventoryproductrel.productid WHERE vtiger_quotes.quoteid = ?";
$result = $adb->pquery($query, array($id));
$num_rows = $adb->num_rows($result);


$descuento1 = 0;
$descuento2 = 0;

for ( $i=0 ; $i  <  $num_rows ;  $i++ ) {

$descuento1 = $adb->query_result($result,$i,3) + $descuento1 ;
$descuento2 = $adb->query_result($result,$i,4) + $descuento2 ;

}

$descuento1 = str_replace(' ', '',$descuento1);
$descuento2 = str_replace(' ', '',$descuento2);

return number_format($descuento1 + $descuento2,0, '', '.') ;

}


function arreglo () {
 
 $datos = array ();
 
 
 $datos[0] = 1;
 $datos[1] = 2;
 $datos[2] = 3;
 $datos[3] = 4;
 $datos[4] = 5;
 
 return $datos ;
 
 }
 
 
function getAllCalendarEvents ($accountId)
{
	global $adb;

	$query="SELECT vtiger_activity.activityid, vtiger_activity.subject,
				vtiger_activity.status, vtiger_activity.eventstatus,
				vtiger_activity.activitytype, vtiger_activity.date_start, vtiger_activity.due_date,
				vtiger_activity.time_start, vtiger_activity.time_end,
				vtiger_crmentity.modifiedtime, vtiger_crmentity.createdtime,
				vtiger_crmentity.description, CASE WHEN (vtiger_users.user_name NOT LIKE '') 
				THEN 
				CONCAT(vtiger_users.first_name,' ',vtiger_users.last_name) ELSE vtiger_groups.groupname END AS user_name
				FROM vtiger_activity INNER JOIN vtiger_seactivityrel
					ON vtiger_seactivityrel.activityid = vtiger_activity.activityid INNER JOIN vtiger_crmentity
					ON vtiger_crmentity.crmid = vtiger_activity.activityid LEFT JOIN vtiger_groups
					ON vtiger_groups.groupid = vtiger_crmentity.smownerid LEFT JOIN vtiger_users
					ON vtiger_users.id=vtiger_crmentity.smownerid
				WHERE (vtiger_activity.activitytype != 'Emails')
				AND vtiger_seactivityrel.crmid = ?
                            AND vtiger_crmentity.deleted = 0 AND vtiger_activity.activitytype IN ('Capacitacion','Cierre de Capacitacion') 
				ORDER BY vtiger_activity.date_start ASC";
			
	
	$result = $adb->pquery($query, array($accountId));
	$num_rows = $adb->num_rows($result);
	
	echo "ID" . $accountId;
	
	$html ='
		<table width="100%" border="1" style="font-family: Arial; font-size: 11px;">
			<tr>
			<th>Tema</th>
			<th>Descripci&oacute;n</th>
			<th>Fecha</th>
			<th>Hora de Inicio</th>
			<th>Hora de Fin</th>
			<th>Asignado a</th>
			</tr>';
			
	for ( $i=0 ; $i  <  $num_rows ;  $i++ ) 
	{

		$tipo =  $adb->query_result($result,$i,4);
		if ( empty($tipo) ) $tipo = "&nbsp;";
			$asunto = $adb->query_result($result,$i,1);
		if ( empty($asunto) ) $asunto = "&nbsp;";
		
		$fecha_inicio = date("d/m/Y", strtotime($adb->query_result($result,$i,5))); 
		if ( empty($fecha_inicio) ) $fecha_inicio = "&nbsp;";
		
		$start_time = date("H:i", strtotime($adb->query_result($result,$i,7))); 
		if ( empty($start_time) ) $start_time = "&nbsp;";

		$end_time = date("H:i", strtotime($adb->query_result($result,$i,8))); 
		if ( empty($end_time) ) $end_time = "&nbsp;";
		
		$estado = $adb->query_result($result,$i,2);
		if ( empty($estado) ) $estado = "&nbsp;";
			$asignado_a = $adb->query_result($result,$i,12); 
		if ( empty($asignado_a) ) $asignado_a = "&nbsp;";
		
		
		$html .= "<tr>";
		$html .= "<td style='padding: 0px 2px 0px 2px;'>" . str_replace("on", "&oacute;n", $tipo) . "</td>";	
		$html .= "<td style='padding: 0px 2px 0px 2px;'>" . $asunto . "</td>";
		$html .= "<td style='padding: 0px 2px 0px 2px;'>" . $fecha_inicio . "</td>";
		$html .= "<td style='padding: 0px 2px 0px 2px; text-align: center;'>" . $start_time . "</td>";
		$html .= "<td style='padding: 0px 2px 0px 2px; text-align: center;'>" . $end_time . "</td>";
		$html .= "<td style='padding: 0px 2px 0px 2px;'>" . $asignado_a . "</td>";	
		$html .= "</tr>";		   

         }
	$html .= "</table>";
	return $html;	
}


function fecha_dia($fecha) {


$fecha= new DateTime ($fecha);

return $fecha->format('d');

}


function fecha_mes($fecha) {


$fecha= new DateTime ($fecha);


$mes = array("01" => "Enero","02" =>"Febrero","03" =>"Marzo","04" =>"Abril","05" =>"Mayo","06" =>"Junio","07" =>"Julio","08" =>"Agosto","09" =>"Septiembre","10" =>"Octubre","11" =>"Noviembre","12" =>"Diciembre");

return $mes[$fecha->format('m')];

}


function fecha_ano($fecha) {

$fecha= new DateTime ($fecha);
$fecha = substr($fecha->format('Y'), 2,2);
return $fecha;

}

function getLargeObjetive($target){
   
    $defincion = array ("1.Comprender el uso y aplicación de WebClass LMS en su entorno.",
                "2. Crear, editar y resolver problemas de nivel usuario relacionados con claves, actualización de caché entre otros, errores y preguntas frecuentes",
                "3. Crear correos y grupos de destinatarios, enviar mensajes a grupos e individuos, utilizar chat en alguna actividad cotidiana, así como foros y otras herramientas de comunicaciones.",
                "4. Coordinar y aprobar currículum cargado en la plataforma WebClass LMS de cada colegio",
                "5. Comprender los distintos tipos de recursos y sus usos, pudiendo identificar en internet, calidades y correctas aplicaciones.",
                "6. Seleccionar, descargar y subir recursos digitales a la plataforma WebClass LMS",
                "7. Aplicar en actividades pedagógicas recursos de introducción, ejercitación o evaluación.",
                "8. Comprender la nuevas bases curriculares y su organización y enfoque de competencias \"saber hacer\".",
                "9. Crear una unidad didáctica entendiendo el rol de la secuenciación didáctica, el espiral de aprendizaje y la taxonomía.",
                "10. Crear un clase dentro de una unidad, agregar recursos digitales y crear una tarea que deberá ser contestada por el alumno y revisada por el profesor.",
                "11. Comprender el proceso de evaluación dentro del proceso pedagógico y de aprendizaje, entender el rol de las taxonomías en las distintas áreas del conocimiento.",
                "12. Crear preguntas de distintos tipos aplicando las taxonomías y criterios de evaluación.",
                "13. Comprender el rol de la evaluación diagnostica, formativa y sumativa, así como la interpretación de la información entregada por el informe general de aprendizaje WebClass LMS.",
                "14. Crear y aplicar una evaluación a un curso real.");
   
    $output = '';
    $temp = explode(",", $target);
    if(count($temp) > 0){
        foreach ($temp as $item){
            $index = ((int) ($item) - 1);
            $output .= htmlentities($defincion[$index]) . "<br /><br />";
        }
    }
	
	 
	
	
   
    return $output;
}

function formato_cifra($cantidad) {


$cantidad = str_replace(' ', '',$cantidad);

return number_format($cantidad,0, '', '.');

}


function salto_linea($cantidad) {

$text = "The quick brown fox jumped over the lazy dog.";
$newtext = wordwrap($text, 20, "<br />\n");

return $newtext;

}




