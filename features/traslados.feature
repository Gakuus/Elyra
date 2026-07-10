# language: es
# HU-12 a HU-14 y HU-16: Trazabilidad de traslados
Característica: Gestión de traslados en ambulancia
  Como funcionario del hospital
  Quiero registrar y dar seguimiento a los traslados
  Para tener trazabilidad de cada operación

  Antecedentes:
    Dado estoy autenticado como "admin"

  Escenario: HU-12 Registrar un nuevo traslado
    Cuando voy a "/traslados/nuevo"
    Y relleno "origen" con "Emergencias"
    Y relleno "destino" con "Cirugía"
    Y selecciono "1" como "conductor"
    Y presiono "Registrar Traslado"
    Entonces debería ver "traslado registrado"

  Escenario: HU-13 Avanzar estado del traslado
    Dado voy a "/traslados/ver?id=1"
    Cuando presiono "Iniciar Traslado"
    Entonces debería ver "en_curso"
    Y la URL debería contener "traslados"

  Escenario: HU-13 Cancelar un traslado
    Dado voy a "/traslados/ver?id=1"
    Cuando presiono "Cancelar Traslado"
    Y relleno "motivo" con "Falta de personal"
    Entonces debería ver "cancelado"

  Escenario: HU-14 Consultar traslados activos
    Cuando voy a "/traslados"
    Entonces debería ver "Traslados"
    Y debería ver un campo "estado"

  Escenario: HU-16 Ver historial de traslados
    Cuando voy a "/traslados/historial"
    Entonces debería ver "Historial"
