<!DOCTYPE html>
<html>

<head>
	<title>Cuatro en raya</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" type="text/javascript"></script>
	<script src="assets/js/cuatro.js" type="text/javascript"></script>

	<link rel="stylesheet" href="assets/css/cuatro.css" type="text/css">
	</style>
</head>

<body>

	<main>
		<header>
			<h1>Cuatro en raya</h1>
		</header>

		<div id="contenedor">

			<h2 id="rotulo">Choose your game</h2>

			<div id="tablero">
				<div id="col1" data-id="1" class="columna">
					<div class="celda">
						<div class="token" id="3"></div>
					</div>
					<div class="celda">
						<div class="token" id="2"></div>
					</div>
					<div class="celda">
						<div class="token" id="1"></div>
					</div>
					<div class="celda">
						<div class="token" id="0"></div>
					</div>
					<div class="id_col">1</div>
				</div>
				<div id="col2" data-id="2" class="columna">
					<div class="celda">
						<div class="token" id="7"></div>
					</div>
					<div class="celda">
						<div class="token" id="6"></div>
					</div>
					<div class="celda">
						<div class="token" id="5"></div>
					</div>
					<div class="celda">
						<div class="token" id="4"></div>
					</div>
					<div class="id_col">2</div>
				</div>
				<div id="col3" data-id="3" class="columna">
					<div class="celda">
						<div class="token" id="11"></div>
					</div>
					<div class="celda">
						<div class="token" id="10"></div>
					</div>
					<div class="celda">
						<div class="token" id="9"></div>
					</div>
					<div class="celda">
						<div class="token" id="8"></div>
					</div>
					<div class="id_col">3</div>
				</div>
				<div id="col4" data-id="4" class="columna">
					<div class="celda">
						<div class="token" id="15"></div>
					</div>
					<div class="celda">
						<div class="token" id="14"></div>
					</div>
					<div class="celda">
						<div class="token" id="13"></div>
					</div>
					<div class="celda">
						<div class="token" id="12"></div>
					</div>
					<div class="id_col">4</div>
				</div>
				<div class="div_presentacion">
					<div class="div_btns_presentacion">
						<button id="btn_atras">Atrás</button>
						&emsp;
						<span id="presentacion_actual">0</span> /	<span id="presentacion_total">0</span>
						&emsp;
						<button id="btn_adelante">Adelante</button>
						&emsp;
						<button id="btn_cerrar_presentacion">Cerrar</button>
					</div>
				</div>
			</div>

			<div id="controles">
				<div class="botones">
					<!-- <button id="btn_iniciar">Juego automático</button>
					<br><br> -->
					<button id="btn_solitario">Juego solitario</button>
					<br><br>
					<button id="btn_ver_memoria">Ver memoria</button>
					<br><br>
					<button id="btn_iniciar_partida">Iniciar partida</button>
					<div class="div_dificultad">
						Dificultad:
						<select id="dificultad">
							<option value="0">Borracho</option>
							<option value="1">Fiestero</option>
							<option value="2">Resacoso</option>
							<option value="3">Sobrio</option>
						</select>
						<button id="btn_dificultad">Elegir</button>
					</div>
				</div>

				<div class="resp" id="resp"></div>
			</div>

			<div id="amigas">
				<h3>Amigas de Bender</h3>
				<p>¿Estás despierto, mamífero? Espabila, estas son mis cinco mejores amigas.</p>
				<div class="div-amigas-list">
					<div id="div-tops-table"></div>
					<div class="div-amigas-botones">
						<button id="btn-amigas-actualizar">Actualizar</button>
						<br>&emsp;<br>
						<button id="btn-amigas-records" data-show='on'>Ver records</button>
					</div>
				</div>
				<div id="div-amigas-records" style="display: none"></div>
				<div id="amigas-error"></div>
			</div>

			<div id="comunidad">
				<form id="form-union" class="form-union">
					<input type="text" id="union-nombre" placeholder="Escribe tu nombre" name="union-nombre">
					<select id="union-color" name="union-color">
						<option class="union-color-item" value="">Random color</option>
						<option class="union-color-item" value="#007AFF" style="background-color: #007AFF;">Azul</option>
						<option class="union-color-item" value="#FF7000" style="background-color: #FF7000;">Naranja</option>
						<option class="union-color-item" value="#45A713" style="background-color: #45A713;">Verde</option>
						<option class="union-color-item" value="#A7A713" style="background-color: #A7A713;">Musgo</option>
						<option class="union-color-item" value="#15E25F" style="background-color: #15E25F;">Pistacho</option>
						<option class="union-color-item" value="#CFC700" style="background-color: #CFC700;">Oro</option>
						<option class="union-color-item" value="#CF1100" style="background-color: #CF1100;">Sangre</option>
						<option class="union-color-item" value="#FF0000" style="background-color: #FF0000;">Rojo</option>
						<option class="union-color-item" value="#CF00BE" style="background-color: #CF00BE;">Morado</option>
					</select>
					<button id="btn_union">Únete a la comunidad</button>
				</form>
				<div id="chat-union">
					<div class="logged-union">Logged as <strong id="logged-name"></strong>, <span id="logged-color"></span></div>
					<div id="chat-wrapper" class="chat-wrapper">
						<div id="message-box"></div>
						<div class="user-panel">
							<!-- <input type="text" name="name" id="name" placeholder="Your Name" maxlength="15" /> -->
							<input type="text" name="message" id="message" placeholder="Type your message here..." maxlength="100" />
							<button id="send-message">Send</button>
						</div>
					</div>
					<div class="listado">
						<div id="users">
							<div>Usuarios</div>
							<ul id="users-list"></ul>
						</div>
					</div>
				</div>
			</div>
		</div>

		<footer>
			<p>Thanks to Ganesha, we have PHP. Enjoy yourself.</p>
		</footer>
	</main>

</body>

</html>