<!DOCTYPE html>
<html>
<head>
	<title>Cuatro en raya</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" type="text/javascript"></script>
    <script src="assets/js/cuatro.js" type="text/javascript"></script>

    <link rel="stylesheet" href="assets/css/cuatro.css" type="text/css"></style>
</head>
<body>

	<main>
		<header>
			<h1>Cuatro en raya</h1>
		</header>

		<div id="contenedor">

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
			</div>

			<div id="controles">
				<div class="botones">
					<button id="btn_iniciar">Juego automático</button>
					<!-- <br><br> -->
					&emsp;
					<button id="btn_iniciar_partida">Iniciar partida</button>
				</div>

				<div class="resp" id="resp"></div>
			</div>
		</div>

		<footer>
			<p>Diviértase</p>
		</footer>
	</main>

</body>
</html>