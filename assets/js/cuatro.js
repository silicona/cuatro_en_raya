

var Cuatro = {

	rellenar_tablero: function(arr_tokens){

		$(".token").removeClass("H").removeClass("M");

		for(i = 0; i < arr_tokens.length; i++){

			if(typeof arr_tokens[i] != "object"){

				$("#" + i).addClass(arr_tokens[i]);
			}
		}

		$('#btn_iniciar').html("Reiniciar");
	},

	jugar_automatico: function(){

		var ajax = $.ajax({
			type: "POST",
			url: "php/api.php",
			data: {
				accion: "juego_automatico"
			}
		});

		ajax.done(function(json){

			if(json != ''){

				var datos = $.parseJSON(json); 

				if(datos != null){

					Cuatro.rellenar_tablero(datos.tablero);

					$('#resp')
						.html($('<p>', {html: datos.mensaje.join('<br>')}))
						.show()
					;
				}
			}
		});
	},

}

$(function(){

	$("#btn_iniciar").on("click", Cuatro.jugar_automatico);
});
