# 12 - Historias de Usuario

## Módulo de Documentación para Pacientes

### HU-01: Inicio de sesión administrativo

**Como** administrativo del hospital,
**quiero** iniciar sesión con mis credenciales del sistema centralizado,
**para** acceder a las funcionalidades de gestión del sistema.

**Criterios de aceptación:**
- El sistema debe validar las credenciales contra el sistema centralizado del hospital.
- Si las credenciales son inválidas, debe mostrar un mensaje de error claro.
- Si las credenciales son válidas, debe redirigir al panel principal del módulo de documentación.
- Debe cerrar la sesión automáticamente tras un periodo de inactividad.

**Prioridad:** Alta | **RF:** RF-01 | **CU:** CU-01

---

### HU-02: Carga de documentos informativos

**Como** administrativo del hospital,
**quiero** cargar documentos informativos en formato PDF al sistema,
**para** ponerlos a disposición de los pacientes mediante código QR.

**Criterios de aceptación:**
- El sistema debe aceptar archivos en formato PDF.
- Debe permitir asignar un título, una descripción y una categoría al documento.
- El tamaño máximo del archivo debe estar claramente especificado y validado.
- Al cargarse exitosamente, debe generar automáticamente un código QR único para el documento.
- Debe mostrar una confirmación visual de que la carga fue exitosa.

**Prioridad:** Alta | **RF:** RF-02, RF-06 | **CU:** CU-02

---

### HU-03: Edición de documento

**Como** administrativo del hospital,
**quiero** editar el título y la descripción de un documento ya cargado,
**para** corregir o actualizar la información sin tener que volver a subirlo.

**Criterios de aceptación:**
- Debe permitir modificar el título, la descripción y la categoría.
- Los cambios deben persistir en la base de datos.
- El código QR existente debe seguir siendo válido tras la edición.

**Prioridad:** Media | **RF:** RF-03 | **CU:** CU-03

---

### HU-04: Eliminación de documentos

**Como** administrativo del hospital,
**quiero** eliminar documentos del sistema,
**para** mantener actualizado y depurado el repositorio de materiales informativos.

**Criterios de aceptación:**
- Debe solicitar confirmación antes de eliminar.
- Al eliminar un documento, el código QR asociado debe dejar de funcionar.
- Debe mostrar un mensaje de éxito tras la eliminación.

**Prioridad:** Media | **RF:** RF-04 | **CU:** CU-04

---

### HU-05: Categorización de documentos

**Como** administrativo del hospital,
**quiero** categorizar los documentos por tipo o área médica,
**para** que los pacientes encuentren fácilmente la información relevante para su caso.

**Criterios de aceptación:**
- Las categorías deben incluir áreas como cardiología, nefrología, etc.
- Un documento debe pertenecer al menos a una categoría.
- Debe ser posible filtrar documentos por categoría en el listado.

**Prioridad:** Alta | **RF:** RF-05 | **CU:** CU-05

---

### HU-06: Visualización e impresión de código QR

**Como** administrativo del hospital,
**quiero** visualizar e imprimir el código QR asociado a cada documento,
**para** entregarlo físicamente al paciente junto con su documentación médica.

**Criterios de aceptación:**
- El código QR debe generarse automáticamente al cargar el documento.
- Debe mostrarse en un tamaño adecuado para impresión.
- Debe incluir el título del documento junto al QR.
- El QR debe poder descargarse como imagen.

**Prioridad:** Alta | **RF:** RF-06, RF-07 | **CU:** CU-06

---

### HU-07: Acceso a documento mediante QR

**Como** paciente del hospital,
**quiero** escanear un código QR con mi dispositivo móvil para ver el documento,
**para** acceder a la información médica sin necesidad de instalar una aplicación ni autenticarme.

**Criterios de aceptación:**
- El acceso mediante QR no debe requerir autenticación.
- La vista del documento debe cargarse en menos de 3 segundos.
- Debe mostrar el documento optimizado para pantallas móviles.
- Debe funcionar correctamente en los navegadores modernos de dispositivos móviles.

**Prioridad:** Alta | **RF:** RF-08, RF-09 | **CU:** CU-10

---

### HU-08: Creación de encuestas de satisfacción

**Como** administrativo del hospital,
**quiero** crear y publicar encuestas de satisfacción con distintos tipos de preguntas,
**para** recopilar la opinión de los pacientes sobre la atención recibida.

**Criterios de aceptación:**
- Debe permitir crear preguntas de opción múltiple, escala de valoración y texto libre.
- Debe permitir publicar o despublicar la encuesta.
- Una encuesta publicada debe ser accesible mediante un enlace o código QR.

**Prioridad:** Media | **RF:** RF-10 | **CU:** CU-08

---

### HU-09: Respuesta a encuesta por parte del paciente

**Como** paciente del hospital,
**quiero** completar y enviar una encuesta de satisfacción desde mi dispositivo móvil,
**para** brindar mi opinión sobre la atención recibida de forma rápida y anónima.

**Criterios de aceptación:**
- La encuesta debe visualizarse correctamente en dispositivos móviles.
- El envío debe ser inmediato y mostrar una confirmación al paciente.
- No debe requerir autenticación ni datos personales.
- Las preguntas obligatorias deben estar marcadas y validadas.

**Prioridad:** Alta | **RF:** RF-11, RF-12 | **CU:** CU-11

---

### HU-10: Visualización de resultados de encuestas

**Como** administrativo del hospital,
**quiero** visualizar los resultados y estadísticas de las encuestas respondidas,
**para** analizar la satisfacción de los pacientes y detectar áreas de mejora.

**Criterios de aceptación:**
- Debe mostrar el número total de respuestas recibidas.
- Las preguntas de opción múltiple y escala deben mostrar gráficos (barras, torta).
- Las respuestas de texto libre deben listarse para su revisión.
- Debe permitir filtrar por rango de fechas.

**Prioridad:** Media | **RF:** RF-12, RF-13 | **CU:** CU-09

---

### HU-11: Listado y búsqueda de documentos

**Como** administrativo del hospital,
**quiero** visualizar el listado completo de documentos con opciones de búsqueda y filtro,
**para** encontrar rápidamente un documento específico entre todos los cargados.

**Criterios de aceptación:**
- El listado debe mostrar título, categoría, fecha de carga y estado del QR.
- Debe permitir filtrar por categoría y buscar por texto en el título o descripción.
- Debe mostrar un paginado si hay muchos resultados.

**Prioridad:** Alta | **RF:** RF-05 | **CU:** CU-07

---

## Módulo de Trazabilidad de Ambulancias

### HU-12: Registro de solicitud de traslado

**Como** administrativo del hospital,
**quiero** registrar una nueva solicitud de traslado en ambulancia,
**para** gestionar el transporte de pacientes, equipamiento o insumos.

**Criterios de aceptación:**
- El formulario debe incluir: conductor, copiloto/acompañante, paciente o elemento, origen, destino, hora de salida y hora estimada de llegada.
- Debe permitir seleccionar el tipo de elemento trasladado: paciente biológico, equipamiento médico o insumos.
- Al guardar, el traslado debe quedar con estado "Pendiente".

**Prioridad:** Alta | **RF:** RF-14, RF-15, RF-16 | **CU:** CU-12

---

### HU-13: Actualización de estado del traslado

**Como** administrativo del hospital,
**quiero** actualizar el estado del traslado durante su ciclo operativo,
**para** mantener informados a todos los involucrados sobre el progreso del viaje.

**Criterios de aceptación:**
- Los estados disponibles deben ser: Pendiente, En curso, Completado, Cancelado.
- El ciclo operativo debe permitir la transición: salida → en ruta → llegada a destino → retorno → finalizado.
- Cada cambio de estado debe registrar la fecha y hora exacta.
- No debe permitir transiciones de estado inválidas (ej. de Pendiente a Completado sin pasar por En curso).

**Prioridad:** Alta | **RF:** RF-18, RF-19 | **CU:** CU-13

---

### HU-14: Consulta de traslados activos

**Como** administrativo del hospital,
**quiero** consultar y visualizar la lista de traslados registrados con su estado actual,
**para** tener visibilidad en tiempo real de las operaciones activas.

**Criterios de aceptación:**
- La lista debe mostrar origen, destino, conductor, estado y hora de salida.
- Debe resaltar visualmente los traslados "En curso".
- Debe actualizarse la información al recargar la vista.

**Prioridad:** Alta | **RF:** RF-17 | **CU:** CU-14

---

### HU-15: Gestión de rutas

**Como** administrativo del hospital,
**quiero** registrar y gestionar rutas dentro del circuito nacional,
**para** asociarlas a los traslados y estandarizar los trayectos.

**Criterios de aceptación:**
- Debe permitir crear rutas con origen, destino y distancia estimada.
- Las rutas deben poder seleccionarse al registrar un traslado.
- Debe permitir editar y desactivar rutas existentes.

**Prioridad:** Media | **RF:** RF-20 | **CU:** CU-15

---

### HU-16: Historial de traslados

**Como** administrativo del hospital,
**quiero** visualizar el historial de traslados realizados con opciones de filtro,
**para** realizar análisis retrospectivos y emitir reportes.

**Criterios de aceptación:**
- Debe permitir filtrar por fecha, conductor y estado.
- El historial debe incluir todos los cambios de estado con sus marcas de tiempo.
- Debe mostrar el detalle completo de cada traslado.

**Prioridad:** Media | **RF:** RF-21 | **CU:** CU-16
