<?php
$server = "190.153.188.6";
$user 	= "crmweb";
$pwd 	= "z3r0x23.,2013";
$bd     = "crmweb_webclass";
$cn = mysql_connect($server, $user, $pwd) or die("Error de conexion!");
mysql_select_db($bd, $cn);
mysql_query("SET CHARACTER SET utf8");
mysql_query("SET NAMES utf8");

$year = date("dmY");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>:: IDDE General ::</title>
<link rel="stylesheet" href="css/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="css/style.css" media="screen" />
  <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
  <script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
  <script src="js/cargador.js"></script>
  <script type="text/javascript">
  $(function($){
    $.datepicker.regional['es'] = {
        closeText: 'Cerrar',
        prevText: '<Ant',
        nextText: 'Sig>',
        currentText: 'Hoy',
        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
        dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
        dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
        weekHeader: 'Sm',
        dateFormat: 'dd/mm/yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['es']);
});
  $(function() {
    $( "#desde" ).datepicker();
	$( "#hasta" ).datepicker();
  });
  </script>
	<script type="text/javascript" src="js/jquery.mousewheel.pack.js?v=3.1.3"></script>
	<script type="text/javascript" src="source/jquery.fancybox.js?v=2.1.5"></script>
	<link rel="stylesheet" type="text/css" href="source/jquery.fancybox.css?v=2.1.5" media="screen" />
	<link rel="stylesheet" type="text/css" href="source/helpers/jquery.fancybox-buttons.css?v=1.0.5" />
	<script type="text/javascript" src="source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
	<link rel="stylesheet" type="text/css" href="source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" />
	<script type="text/javascript" src="source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
	<script type="text/javascript" src="source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>
    <script src="js/sorttable.js"></script>
	<style type="text/css">
		.fancybox-custom .fancybox-skin {
			box-shadow: 0 0 50px #222;
		}
	</style>
</head>

<body>
<br />
<strong class="titulos">Indicadores IDDE General</strong>
<table width="100%" border="0">
  <tr>
    <td colspan="2"><strong>Establecimiento :</strong><select name="idaccountx" id="idaccountx" onchange="CargarData();"></select></td>
    <td colspan="3" rowspan="4"><table width="100%" border="1">
      <tr>
        <td width="34%"><strong>Total Sostenedores :</strong></td>
        <td width="66%"><div id="total_sostenedores"></div></td>
        </tr>
      <tr>
        <td><strong>Total  UTP :</strong></td>
        <td><div id="total_utp"></div></td>
        </tr>
      <tr>
        <td><strong>Total Profesores :</strong></td>
        <td><div id="total_profesores"></div></td>
        </tr>
      <tr>
        <td><strong>Total Apoderados :</strong></td>
        <td><div id="total_apoderados"></div></td>
        </tr>
      <tr>
        <td  ><strong>Total Alumnos :</strong></td>
        <td><div id="total_alumnos"></div></td>
        </tr>
    </table></td>
  </tr>
  
  <tr>
    <td width="24%"><div id="title-comparar" style="display: block;"><strong>Comparar Establecimiento :</strong></div></td>
    <td width="25%"><div id="title-content-comparar" style="display: block;">Si
      <input type="radio" name="radio" id="radio1" value="1" />
      No
    <input type="radio" name="radio" id="radio2" value="2" /></div></td>
  </tr>
  <tr>
    <td colspan="2"><div id="school2" style="display:none;">Establecimiento :<select name="select2" id="idaccounty"></select></div></td>
 
  </tr>
  
 
  <tr>
    <td height="22"><div id="title-intervalo" style="display: block;"><strong>Indique intervalo de tiempo : </strong></div></td>
    <td><div id="title-content" style="display: block;">Diario <input type="radio" name="radio1" id="rdiario" value="3" />&nbsp;</div> 
    </td>
 
  </tr>
 
  <tr>
 
    <td><div id="intervalo" style="display:none;"> <input type="text" id="desde" placeholder="Desde" value="09/04/2014"/>&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" id="hasta" placeholder="Hasta" value="<?php echo date('d/m/Y');?>"/></div>
    <div id="mensual" style="display:none;"><select name="Mensual" id="select_mensual">
      <option value="0">- Seleccione Mes -</option>
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
    </select></div>
    <div id="anual" style="display:none;"><select name="Anual" id="select_anual">
      <option value="0">- Seleccione Año -</option>
      <option value="2014">2014</option>
      <option value="2015">2015</option>
      <option value="2016">2016</option>
      <option value="2017">2017</option>
      <option value="2018">2018</option>
      <option value="2019">2019</option>
      <option value="2020">2020</option>
    </select></div></td>
 
  </tr>
 
</table>
<div align="center" style="display:none;" id="div_graficar">
      <input type="submit" name="button" id="button" value="Generar Grafico" onclick="graficar();" />
</div>
    <div align="center" style="display:none;" id="div_listar">
      <input type="submit" name="button" id="button" value="Generar Lista" onclick="listar();" />
    </div>
<div id="lista" style="width:100%;"></div>
</body>
</html>
<?php 
mysql_close();
?>