# language: es
# HU-02 a HU-06 y HU-11: Gestión de documentos informativos
Característica: Gestión de documentos informativos
  Como funcionario del hospital
  Quiero subir, editar, eliminar y categorizar documentos PDF
  Para que los pacientes accedan a información digital

  Antecedentes:
    Dado estoy autenticado como "admin"

  Escenario: HU-02 Subir un documento PDF
    Cuando voy a "/documentos/subir"
    Y relleno "titulo" con "Indicaciones post-operatorias"
    Y selecciono "1" como "categoria"
    Y subo el archivo "documento.pdf" a "archivo"
    Y presiono "Subir documento"
    Entonces la URL debería contener "generales"

  Escenario: HU-03 Editar título de un documento
    Cuando voy a "/documentos/editar?id=5"
    Y relleno "titulo" con "Título actualizado"
    Y presiono "Guardar cambios"
    Entonces la URL debería contener "generales"

  Escenario: HU-04 Eliminar un documento
    Cuando voy a "/documentos/eliminar?id=5"
    Entonces la URL debería contener "generales"

  Escenario: HU-05 + HU-11 Listar documentos filtrados por categoría
    Cuando voy a "/documentos/generales?categoria=1"
    Entonces debería ver "Documentos Generales"
    Y debería ver un campo "categoria"

  Escenario: HU-06 Descargar QR de un documento
    Cuando voy a "/documentos/ver?id=5"
    Entonces debería ver "Descargar PDF"

  Escenario: HU-11 Listar documentos con paginación
    Cuando voy a "/documentos/generales"
    Entonces debería ver "Documentos Generales"
