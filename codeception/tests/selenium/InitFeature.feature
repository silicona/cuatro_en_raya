# language: es

Característica: InitFeature
  Para iniciar bien la suite de Gerkhin

  Escenario: Llegamos al Cuatro en raya
    Dado que voy a "index"
    Cuando voy a la pagina
    Y veo el titulo
    Y pulso boton con id btn_iniciar
    Entonces veo partida automática
