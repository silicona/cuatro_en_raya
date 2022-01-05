

var Cuatro = {

	automatico: false,
	label_automatico: 'Juego automático',

	actualizar_tablero: function (json) {
		try {
			if (json != '') {

				var datos = $.parseJSON(json);

				if (datos != null) {

					Cuatro.rellenar_tablero(datos.tablero);

					var mensaje = $('<p>', { html: datos.mensaje.join('<br>') })

					if (Cuatro.automatico) {

						$('#btn_iniciar').html("Reiniciar automático");
						$('#resp').html(mensaje)

						datos.token !== false ? $('#' + datos.token).addClass('animado') : $('.token').removeClass('animado');

					} else {

						$('#btn_iniciar').html(Cuatro.label_automatico);
						$('#resp').append(mensaje);

						if (datos.fin_partida) {
							Cuatro.anular_columnas();

							if (datos.linea && datos.linea.length > 0) {
								for (var i in datos.linea) {
									$('#' + datos.linea[i]).addClass('animado');
								}
							}
						}
					}

					$('#resp')
						.scrollTop($('#resp').prop('scrollHeight'))
						.show();
				}
			}
		} catch (error) {
			$('#resp').html($('<p>', { html: 'Error interno: ' + error }));
		}
	},

	anular_columnas: function () {
		$('.columna')
			.removeClass('activa')
			.off('click', Cuatro.echar_ficha);
	},

	echar_ficha: function (event) {

		var tablero = []
		$('.token').map(function (i, item) {
			var match = /(M|H)/.exec(item.className)

			tablero[item.id] = match ? match[1] : null
		})

		var ajax = $.ajax({
			type: "POST",
			url: "php/api.php",
			data: {
				accion: "echar_ficha",
				tablero: tablero,
				columna: event.currentTarget.getAttribute('data-id')
			}
		});

		ajax.done(Cuatro.actualizar_tablero)
	},

	jugar_automatico: function () {

		Cuatro.automatico = true;
		$('#resp').html('');
		Cuatro.anular_columnas();

		var ajax = $.ajax({
			type: "POST",
			url: "php/api.php",
			data: {
				accion: "juego_automatico"
			}
		});

		ajax.done(Cuatro.actualizar_tablero)
	},

	jugar_partida: function () {

		Cuatro.automatico = false;
		$('#resp').html('');
		$('.columna')
			.addClass('activa')
			.on('click', Cuatro.echar_ficha)

		var ajax = $.ajax({
			type: "POST",
			url: "php/api.php",
			data: {
				accion: "jugar_partida"
			}
		});

		ajax.done(Cuatro.actualizar_tablero)
	},

	rellenar_tablero: function (arr_tokens) {

		$(".token").removeClass("H M animado");

		for (i = 0; i < arr_tokens.length; i++) {

			if (typeof arr_tokens[i] != "object") {

				$("#" + i).addClass(arr_tokens[i]);
			}
		}
	},
}

$(function () {
	$("#btn_iniciar").on("click", Cuatro.jugar_automatico);
	$("#btn_iniciar_partida").on("click", Cuatro.jugar_partida);
});
