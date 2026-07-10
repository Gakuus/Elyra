# language: es
# HU-08 a HU-10: Gestión de encuestas de satisfacción
Característica: Gestión de encuestas
  Como funcionario del hospital
  Quiero crear encuestas y ver sus resultados
  Para medir la satisfacción de los pacientes

  Antecedentes:
    Dado estoy autenticado como "admin"

  Escenario: HU-08 Crear una encuesta con preguntas
    Cuando voy a "/encuestas/crear"
    Entonces debería ver "Nueva encuesta"

  Escenario: HU-10 Ver resultados de una encuesta
    Cuando voy a "/encuestas/resultados?id=3"
    Entonces debería ver "Satisfacción general"
