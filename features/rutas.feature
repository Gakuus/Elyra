# language: es
# HU-15: Gestión de rutas
Característica: Gestión de rutas de ambulancia
  Como funcionario del hospital
  Quiero administrar las rutas disponibles
  Para asignarlas a los traslados

  Antecedentes:
    Dado estoy autenticado como "admin"

  Escenario: HU-15 Crear una nueva ruta
    Cuando voy a "/rutas/crear"
    Y relleno "nombre" con "Ruta Central"
    Y relleno "origen" con "Hospital"
    Y relleno "destino" con "Clínica Sur"
    Y presiono "Guardar Ruta"
    Entonces debería ver "ruta creada"

  Escenario: HU-15 Listar rutas existentes
    Cuando voy a "/rutas"
    Entonces debería ver "Rutas"
