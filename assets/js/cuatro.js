

var Cuatro = {

	host: 'localhost',
	port: 8081,
	//port: 9000,
	automatico: false,
	label_automatico: 'Juego automático',
	union: false,
	requests: [],
	niveles: ['Borracho', 'Fiestero', 'Resacoso', 'Sobrio'],
	temp_file: 'def',
	memoria: [],

	actualizar_comunidad_users: function (type, user) {
		if (type == 'add') {
			var boton = '<span class="users-botones"><button class="ask-play" data-id="' + user.id + '">?</button></span>';
			$('#users-list').append('<li class="users-list-item" id="user-' + user.id + '">' + user.name + boton + '</li>');

			$('.ask-play').on('click', Cuatro.new_play_send);

		} else if (type == 'delete') $('#user-' + user.id).remove();
		else if (type == 'accept') {
			delete (Cuatro.requests[Cuatro.requests.indexOf(parseInt(user.id))]);
			$('#resp').hide().html('');
			Cuatro.actualizar_rotulo('Playing vs. Human');
			$('#user-' + user.id + ' span.users-botones').html('Playing...');
		}
		else if (type == 'decline' || type == 'end') {
			delete (Cuatro.requests[Cuatro.requests.indexOf(parseInt(user.id))]);
			var boton = '<button class="ask-play" data-id="' + user.id + '">?</button>';
			$('#user-' + user.id + ' span.users-botones')
				.empty()
				.append(boton);
			$('#user-' + user.id + ' span.users-botones button').on('click', Cuatro.new_play_send);
		}

		return true;
	},

	actualizar_rotulo: function (mensaje) {

		var matches;
		if (matches = mensaje.match(/Playing vs\. (.*)$/)) {
			mensaje = mensaje.replace(matches[1], '<span id="op">' + matches[1] + '</span>');
		}

		$('#rotulo').html(mensaje);
	},

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

						Cuatro.temp_file = datos.temp_file;
						//Cuatro.habilitar_columnas();

						if (datos.fin_partida) {
							Cuatro.habilitar_columnas(false);

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

	actualizar_tablero_socket: function (datos) {

		Cuatro.rellenar_tablero(datos.tablero);

		//var mensaje = $('<p>', { html: datos.mensaje.join('<br>') })

		$('#btn_iniciar').html(Cuatro.label_automatico);
		$('#resp').append($('<p>', { html: datos.mensaje.join('<br>') }));

		if (datos.turno) Cuatro.habilitar_columnas_socket();

		if (datos.fin_partida) {
			Cuatro.habilitar_columnas_socket(false);

			if (datos.linea && datos.linea.length > 0) {
				for (var i in datos.linea) {
					$('#' + datos.linea[i]).addClass('animado');
				}
			}
		}

		$('#resp')
			.scrollTop($('#resp').prop('scrollHeight'))
			.show();
	},

	crear_comunidad_users: function (users) {

		var arr = [];
		for (var i in users) {
			if (users[i].id == Cuatro.union) continue;

			var boton = '<span class="users-botones"><button class="ask-play" data-id="' + users[i].id + '">?</button></span>';
			arr.push('<li class="users-list-item" id="user-' + users[i].id + '">' + users[i].name + boton + '</li>')
		}

		$('#users-list').html(arr.join(''));
		$('.ask-play').on('click', Cuatro.new_play_send);
	},

	echar_ficha: function (event) {

		var tablero = []
		$('.token').map(function (i, item) {
			var match = /(M|H)/.exec(item.className)

			tablero[item.id] = match ? match[1] : null
		})
		var nombre = $('#logged-name').html() || '';
		var ajax = $.ajax({
			type: "POST",
			url: "php/api.php",
			data: {
				accion: "echar_ficha",
				tablero: tablero,
				columna: event.currentTarget.getAttribute('data-id'),
				nombre: nombre,
				dificultad: $('#dificultad').val(),
				temp_file: Cuatro.temp_file
			}
		});

		ajax.done(Cuatro.actualizar_tablero)
	},

	getBenderFriends: function () {
		$('#amigas-error').empty();

		var ajax = $.ajax({
			type: "POST",
			url: "php/api.php",
			data: {
				accion: "get_bender_friends"
			}
		});

		ajax.done(function (json) {
			try {
				if (json != '') {
					var datos = $.parseJSON(json);
					var tops = [];
					var keys = Object.keys(datos.tops);
					while (tops.length < 6) {
						if (keys.length) {
							var key = keys.pop();
							var arr_mayor = datos.tops[key].sort(function (a, b) {
								var orden = b.victorias - a.victorias;
								if(orden === 0) orden = a.nombre.toLowerCase() > b.nombre.toLowerCase();
								return orden;
							});
							while (tops.length < 6) {
								if (arr_mayor.length) tops.push(arr_mayor.shift());
								else break;
							}
						} else break;
					}

					var tops_ord = ['<table id="tops-table"><tr><th class="th-amiga">Amiga</th><th>Puntos</th><th>Victorias</th></tr>']
					for (var i in tops) {
						var top = '<td>' + tops[i].nombre + '</td>' +
							'<td class="text-center">' + tops[i].puntos + '</td>' +
							'<td class="text-center">' + tops[i].victorias + '</td>';
						tops_ord.push('<tr>' + top + '</tr>');
					}
					tops_ord.push('</table>');
					$('#div-tops-table').html(tops_ord.join(''));

					var records = [
						'<table id="amigas-table">',
						'<tr><th rowspan=2 class="th-amiga">Amiga</th>',
						'<th colspan=4 style="text-align: center;">Bender</th>',
						'</tr><tr>'
					];
					records = records.concat(Cuatro.niveles.map(nivel => '<th class="th-nivel">' + nivel + '</th>'), '</tr>');

					var records_ord = datos.records.sort(function (a, b) {
						return a.nombre.toLowerCase() > b.nombre.toLowerCase();
					});

					for (var i in records_ord) {
						var str_rec = '<td class="td-amiga">' + records_ord[i].nombre + '</td>';
						var record = records_ord[i].nums;
						for (var j in record) {
							str_rec += '<td class="td-record">' +
								'<span class="fondo-verde">' + record[j].v + '</span>' +
								'<span class="fondo-rojo">' + record[j].d + '</span>' +
								'<span class="fondo-oro">' + record[j].e + '</span>' +
								'</td>';
						}
						records.push('<tr>' + str_rec + '</tr>')
					}
					records.push('</table>');

					$('#div-amigas-records').html(records.join(''));
				}
			} catch (error) {
				$('#amigas-error').html($('<p>', { html: 'Parece que no tengo amigas debido a: ' + error }));
			}
		});
	},

	habilitarAmigasRecords: function (event) {
		var boton = $(event.currentTarget);
		var show = boton.attr('data-show');

		if (show == 'on') {
			$('#div-amigas-records').show();
			boton
				.attr('data-show', 'off')
				.html('Ocultar records');
		} else {
			$('#div-amigas-records').hide();
			boton
				.attr('data-show', 'on')
				.html('Ver records');
		}
	},

	habilitar_columnas: function (active = true) {
		$('.columna').off('click', Cuatro.send_move);

		if (active) {
			$('.columna')
				.addClass('activa')
				.on('click', Cuatro.echar_ficha);
		} else {
			$('.columna')
				.removeClass('activa')
				.off('click', Cuatro.echar_ficha);
		}
	},

	habilitar_columnas_socket: function (active = true) {
		$('.columna').off('click', Cuatro.echar_ficha);
		if (active) {
			$('.columna')
				.addClass('activa')
				.on('click', Cuatro.send_move);
		} else {
			$('.columna')
				.removeClass('activa')
				.off('click', Cuatro.send_move);
		}
	},

	habilitar_comunidad: function (show = true, data = []) {
		if (show) {
			$('#union-nombre').val('');
			$('#union-color').val('');
			$('#form-union').hide();
			$('#chat-union').show();
			$('#logged-name').html(data[0].name);
			$('#logged-color').css('background-color', data[0].color);
			Cuatro.union = data[0].id;
			Cuatro.crear_comunidad_users(data[1]);

			//Message send button
			$('#send-message').click(function () {
				Cuatro.send_message();
			});

			//User hits enter key 
			$("#message").on("keydown", function (event) {
				if (event.which == 13) {
					Cuatro.send_message();
				}
			});

		} else {
			$('#form-union').show();
			$('#chat-union').hide();
			$('.user-message').html('');
			$('#logged-name').html('');
			$('#logged-color').css('background-color: #FFFFFF');
			Cuatro.union = false;
			//$('#resp').html('Error al conectarse con la comunidad de Cuatro En Raya')
		}
	},

	iniciar_socket: function (name, color) {

		var wsUri = "ws://" + Cuatro.host + ":" + Cuatro.port + "/php/server.php";
		// var wsUri = "ws://" + Cuatro.host + ":" + Cuatro.port + "/demo/server.php";
		websocket = new WebSocket(wsUri);	//create a new WebSocket object.
		var msgBox = $('#message-box');

		websocket.onopen = function (ev) { // connection is open 
			msgBox.append('<div class="system_msg" style="color:#bbbbbb">Welcome to Cuatro Community!</div>');
			//var msg = { type: 'system', message: 'set user data', name: name, color: color };
			//websocket.send(JSON.stringify(msg));
			Cuatro.send_websocket('system', 'set user data', 0, { name: name, color: color });
		}

		websocket.onmessage = function (ev) {	// Message received from server

			var response = JSON.parse(ev.data); //PHP sends Json data

			var res_type = response.type; //message type

			switch (res_type) {
				case 'play':
					if (response.message == 'play request') Cuatro.request_store(response.id_op);
					else if (response.message == 'play accept') Cuatro.actualizar_comunidad_users('accept', { id: response.id_op });
					else if (response.message == 'play decline') Cuatro.request_delete(response.id_op);
					else if (response.message == 'move') Cuatro.actualizar_tablero_socket(response);
					else if (response.message == 'end') Cuatro.actualizar_comunidad_users('end', { id: response.id_op });
					break;
				case 'usermsg':
					msgBox.append('<div><span class="user_name" style="color:' + response.color + '">' + response.name + '</span> : <span class="user_message">' + response.message + '</span></div>');
					break;
				case 'system':
					if (response.message == 'socket connected') Cuatro.habilitar_comunidad(true, [response.user, response.users]);
					else if (/\sconnected$/.test(response.message)) {
						if (response.user.id != Cuatro.union) Cuatro.actualizar_comunidad_users('add', response.user);

					} else if (/\sdisconnected$/.test(response.message)) Cuatro.actualizar_comunidad_users('delete', response.user);
					else msgBox.append('<div style="color:#bbbbbb">' + response.message + '</div>');
					break;
				default:
			}
			msgBox[0].scrollTop = msgBox[0].scrollHeight; //scroll message 
		};

		websocket.onerror = function (ev) {
			$('#resp').html('Error: ' + ev.data + '<br>').show();
		};
		websocket.onclose = function (ev) {
			$('#resp').append('Closing: ' + Cuatro.readWebsocketCode(ev.code) + '<br>').show();
			Cuatro.habilitar_comunidad(false);
		};

		return true;
	},

	jugar_automatico: function () {

		Cuatro.automatico = true;
		$('#resp').html('');
		Cuatro.habilitar_columnas(false);

		var ajax = $.ajax({
			type: "POST",
			url: "php/api.php",
			data: {
				accion: "juego_automatico"
			}
		});

		ajax.done(Cuatro.actualizar_tablero);

		Cuatro.actualizar_rotulo('Automatic play');
	},

	jugar_solitario: function () {

		Cuatro.automatico = true;
		$('#resp').html('');
		Cuatro.habilitar_columnas(false);
		Cuatro.habilitar_columnas_socket(false);

		Cuatro.ocultar_presentacion();

		var ajax = $.ajax({
			type: "POST",
			url: "php/api.php",
			data: {
				accion: "juego_solitario",
				num_rounds: 100
			}
		});

		ajax.done(Cuatro.actualizar_tablero);

		Cuatro.actualizar_rotulo('Lonely play');
	},

	jugar_partida: function () {

		Cuatro.automatico = false;
		var dif = $('#dificultad').val();
		$('.div_dificultad').hide(300);
		$('.columna')
			.addClass('activa')
			.on('click', Cuatro.echar_ficha)

		var ajax = $.ajax({
			type: "POST",
			url: "php/api.php",
			data: {
				accion: "jugar_partida",
				dificultad: dif
			}
		});

		ajax.done(Cuatro.actualizar_tablero);

		Cuatro.actualizar_rotulo('Playing vs. Bender (' + Cuatro.niveles[dif] + ')');
	},

	mostrar_dificultad: function () {
		$('.div_dificultad').show()
	},
	
	mostrar_presentacion: function () {
		$('.div_presentacion').show();
		$('#presentacion_actual').html(0);
		$('#presentacion_total').html(0);

		$('#resp').html('').hide();
	},

	mostrar_presentacion_adelante: function () {
		num = $('#presentacion_actual').html();

		if(num < Cuatro.memoria.length) {
			num++;
			Cuatro.rellenar_tablero(Cuatro.memoria[num-1]);
			$('#presentacion_actual').html(num);
		}
	},

	mostrar_presentacion_atras: function () {
		num = $('#presentacion_actual').html();

		if(num > 1) {
			num--;
			Cuatro.rellenar_tablero(Cuatro.memoria[num-1]);
			$('#presentacion_actual').html(num);
		}
	},

	ocultar_presentacion: function () {
		$('.div_presentacion').hide();
		$('#presentacion_actual').html(0);
		$('#presentacion_total').html(0);

		$(".token").removeClass("H M animado");
	},

	new_play_accept: function (event) {
		var boton = $(event.currentTarget);
		var id_op = boton.attr('data-id');

		delete (Cuatro.requests[Cuatro.requests.indexOf(parseInt(id_op))]);
		//$('#resp').hide().html('');
		Cuatro.send_websocket('play', 'accept play', id_op);

		Cuatro.actualizar_comunidad_users('accept', { id: id_op });
		return true;
	},

	new_play_decline: function (event) {
		var boton = $(event.currentTarget);
		var id_op = boton.attr('data-id');

		Cuatro.send_websocket('play', 'decline play', id_op);

		Cuatro.actualizar_comunidad_users('decline', { id: id_op });
		return true;
	},

	new_play_send: function (event) {
		var boton = $(event.currentTarget);
		var id_op = boton.attr('data-id');

		Cuatro.send_websocket('play', 'new play', id_op);

		boton
			.removeClass()
			.off('click', Cuatro.new_play_send);
		return true;
	},

	readWebsocketCode: function (code) {
		if (code == 1000)
			reason = "Normal closure, meaning that the purpose for which the connection was established has been fulfilled.";
		else if (code == 1001)
			reason = "An endpoint is \"going away\", such as a server going down or a browser having navigated away from a page.";
		else if (code == 1002)
			reason = "An endpoint is terminating the connection due to a protocol error";
		else if (code == 1003)
			reason = "An endpoint is terminating the connection because it has received a type of data it cannot accept (e.g., an endpoint that understands only text data MAY send this if it receives a binary message).";
		else if (code == 1004)
			reason = "Reserved. The specific meaning might be defined in the future.";
		else if (code == 1005)
			reason = "No status code was actually present.";
		else if (code == 1006)
			reason = "Comunity Server is offline.<br>Anyway, websocket was closed abnormally, e.g., without sending or receiving a Close control frame";
		// reason = "The connection was closed abnormally, e.g., without sending or receiving a Close control frame";
		else if (code == 1007)
			reason = "An endpoint is terminating the connection because it has received data within a message that was not consistent with the type of the message (e.g., non-UTF-8 [https://www.rfc-editor.org/rfc/rfc3629] data within a text message).";
		else if (code == 1008)
			reason = "An endpoint is terminating the connection because it has received a message that \"violates its policy\". This reason is given either if there is no other sutible reason, or if there is a need to hide specific details about the policy.";
		else if (code == 1009)
			reason = "An endpoint is terminating the connection because it has received a message that is too big for it to process.";
		else if (code == 1010) // Note that this status code is not used by the server, because it can fail the WebSocket handshake instead.
			reason = "An endpoint (client) is terminating the connection because it has expected the server to negotiate one or more extension, but the server didn't return them in the response message of the WebSocket handshake. <br /> Specifically, the extensions that are needed are: " + event.reason;
		else if (code == 1011)
			reason = "A server is terminating the connection because it encountered an unexpected condition that prevented it from fulfilling the request.";
		else if (code == 1015)
			reason = "The connection was closed due to a failure to perform a TLS handshake (e.g., the server certificate can't be verified).";
		else
			reason = "Unknown reason";

		return reason;
	},

	rellenar_tablero: function (arr_tokens = []) {

		$(".token").removeClass("H M animado");

		for (i = 0; i < arr_tokens.length; i++) {

			if (typeof arr_tokens[i] != "object") {

				$("#" + i).addClass(arr_tokens[i]);
			}
		}
	},

	request_delete: function (id_op) {
		var botones = $('#user-' + id_op + ' span.users-botones');
		//var boton = $('<button>', { class: "ask-play", 'data-id': id_op, html: '?' });

		botones
			.empty()
			//.append(boton);
			.append('<button class="ask-play" data-id="' + id_op + '">?</button>');

		$('#user-' + id_op + ' span.users-botones button').on('click', Cuatro.new_play_send);

		return true;
	},

	request_store: function (id_op) {

		if (Cuatro.requests.indexOf(id_op) == -1) {
			Cuatro.requests.push(id_op);
			var botones = $('#user-' + id_op + ' span.users-botones');
			botones
				.empty()
				.append('<button class="accept-play" data-id="' + id_op + '">V</button>')
				.append('<button class="decline-play" data-id="' + id_op + '">X</button>');

			$('.accept-play').on('click', this.new_play_accept);
			$('.decline-play').on('click', this.new_play_decline);
			return true;
		}
		return false;
	},

	send_move: function (event) {
		Cuatro.habilitar_columnas_socket(false);

		var tablero = [];
		$('.token').map(function (i, item) {
			var match = /(M|H)/.exec(item.className)

			tablero[item.id] = match ? match[1] : null
		})
		var columna = event.currentTarget.getAttribute('data-id');

		Cuatro.send_websocket('play', 'move', 0, { tablero: tablero, columna: columna });
	},

	send_message: function () {
		var message = $('#message');

		if (message.val() == "") {
			alert("Enter Some message Please!");
			return;
		}

		Cuatro.send_websocket('usermsg', message.val());
		message.val(''); //reset message input
		return true;
	},

	send_websocket: function (type = 'play', message = '', id_op = 0, others = {}) {
		var msg = {
			type: type,
			game: 'cuatro',
			message: message
		};
		if (id_op && parseInt(id_op) > 0) msg.id_op = parseInt(id_op);

		msg = Object.assign(msg, others);

		websocket.send(JSON.stringify(msg));
		return true;
	},

	unirse_comunidad: function (event) {
		event.preventDefault();
		var name = $('#union-nombre').val();
		var color = $('#union-color').val();
		var colors = ['#007AFF', '#FF7000', '#45A713', '#A7A713', '#15E25F', '#CFC700', '#CF1100', '#CF00BE', '#FF0000'];

		if (color == '') {
			var ind_color = Math.floor(Math.random() * colors.length);
			color = colors[ind_color];
		}

		Cuatro.iniciar_socket(name, color)
	},

	ver_memoria: function() {
		
		Cuatro.automatico = false;
		Cuatro.memoria = [];

		Cuatro.mostrar_presentacion();
		Cuatro.habilitar_columnas(false);
		Cuatro.habilitar_columnas_socket(false);

		$.ajax({
			type: "POST",
			url: "php/api.php",
			data: {
				accion: "ver_memoria",
			},
			success: function (resp) {

				if(resp && resp.length) {
					memoria = JSON.parse(resp);

					$('#presentacion_actual').html(1);
					$('#presentacion_total').html(memoria.length);

					Cuatro.rellenar_tablero(memoria[0]);
					Cuatro.memoria = memoria;
				}
			}
		});

		// ajax.done(Cuatro.actualizar_tablero);

		// Cuatro.actualizar_rotulo('Playing vs. Bender (' + Cuatro.niveles[dif] + ')');
	}

}

$(function () {
	$("#btn_iniciar").on("click", Cuatro.jugar_automatico);
	$("#btn_solitario").on("click", Cuatro.jugar_solitario);
	$("#btn_iniciar_partida").on("click", function () { 
		$('#resp')
		.hide()
		.html('');
		$('.div_dificultad').show();
		Cuatro.ocultar_presentacion();
	});

	$("#btn_ver_memoria").on("click", Cuatro.ver_memoria);
	$("#btn_adelante").on("click", Cuatro.mostrar_presentacion_adelante);
	$("#btn_atras").on("click", Cuatro.mostrar_presentacion_atras);
	$("#btn_cerrar_presentacion").on("click", Cuatro.ocultar_presentacion);

	$("#btn_dificultad").on("click", Cuatro.jugar_partida);
	$('#form-union').on('submit', Cuatro.unirse_comunidad);

	Cuatro.getBenderFriends();
	$('#btn-amigas-actualizar').on('click', Cuatro.getBenderFriends);
	$('#btn-amigas-records').on('click', Cuatro.habilitarAmigasRecords);
});
