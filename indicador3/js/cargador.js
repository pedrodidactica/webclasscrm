$(document).ready(function() {
	//var totalpa = document.getElementById('pa').value;
	$("#idaccountx").load('data/filtro.php?case=1');
	$('.fancybox').fancybox();
	
	
	});
function graficar(){
	var idaccount = document.getElementById('idaccountx').value;
	var desde = document.getElementById('desde').value;
	var hasta = document.getElementById('hasta').value;
	var idaccount2 = document.getElementById('idaccounty').value;
window.open(this.href="grafico.php?idaccount="+idaccount+"&idaccount2="+idaccount2+"&desde="+desde+"&hasta="+hasta+"", this.target, 'width=1000px,height=520px,left=150px,top=90px'); return false;

}
function listar(){
	
	$.ajax
	({
		type: "POST",
		async: true,
		url: "listar.php",
		data:"&caso=1",
		dataType: "html",
		beforeSend: function () {
        	$("#lista").html('<div align="center"><img src="images/loading-recursos.gif"/></div>');
		},
		success: function(respuesta){
			$('#lista').html(respuesta);
		}
	});	
}
$(function(){
   $("#radio1").click(function(){
	   var idcolegio2 = document.getElementById('idaccountx').value;
       $("#school2").css("display","");
	   
	   $("#idaccounty").load('data/filtro.php?case=2&colegio2='+idcolegio2);
   });
   $("#radio2").click(function(){
       $("#school2").css("display","none");
	    $("#idaccounty").val(0);
   });
   $("#rdiario").click(function(){
       $("#intervalo").css("display","");
	   $("#select_mensual").val('');
	   $("#select_anual").val('');
   });
   $("#rmensual").click(function(){
       $("#intervalo").css("display","none");
	   $("#desde").val('');
	   $("#hasta").val('');
	   $("#select_anual").val('');
   });
   $("#ranual").click(function(){
       $("#intervalo").css("display","none");
	   $("#desde").val('');
	   $("#hasta").val('');
	   $("#select_mensual").val('');
   });
});  
function CargarData(){
var idaccount = document.getElementById('idaccountx').value;
	if(idaccount==1){
		$("#div_listar").css("display","");
		$("#div_graficar").css("display","none");
	   	$("#select_mensual").css("display","none");
	   	$("#select_anual").css("display","none");
	   	$("#desde").css("display","none");
	   	$("#hasta").css("display","none");
	   	$("#title-intervalo").css("display","none");
	   	$("#title-content").css("display","none");
	   	$("#title-comparar").css("display","none");
	   	$("#title-content-comparar").css("display","none");
	   	$("#school2").css("display","none");
		$("#total_apoderados").css("display","none");
	   	$("#total_alumnos").css("display","none");
		$("#total_utp").css("display","none");
	   	$("#total_profesores").css("display","none");
		$("#total_sostenedores").css("display","none");
		$("#lista").css("display","");
	}else{
		
		$("#div_listar").css("display","none");
		$("#div_graficar").css("display","");
		$("#desde").css("display","");
	   	$("#hasta").css("display","");
	   	$("#title-intervalo").css("display","");
	   	$("#title-content").css("display","");
	  	$("#title-comparar").css("display","");
	   	$("#title-content-comparar").css("display","");
		$("#total_apoderados").css("display","");
	   	$("#total_alumnos").css("display","");
		$("#total_utp").css("display","");
	   	$("#total_profesores").css("display","");
		$("#total_sostenedores").css("display","");
		$("#lista").css("display","none");
		
		
	   	$("#total_apoderados").load('data/filtro.php?case=3&idaccount='+idaccount);
	   	$("#total_alumnos").load('data/filtro.php?case=4&idaccount='+idaccount);
		$("#total_utp").load('data/filtro.php?case=7&idaccount='+idaccount);
	   	$("#total_profesores").load('data/filtro.php?case=6&idaccount='+idaccount);
		$("#total_sostenedores").load('data/filtro.php?case=5&idaccount='+idaccount);
	   	
	   
		
	}
}