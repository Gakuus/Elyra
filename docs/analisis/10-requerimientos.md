# 10 - Relevamiento y Análisis — Especificación de Requerimientos de Software

## Requerimientos Funcionales — Módulo de Documentación para Pacientes

| ID | Descripción | Prioridad |
|---|---|---|
| RF-01 | El sistema debe permitir al personal administrativo iniciar sesión con sus credenciales del sistema centralizado del hospital. | Alta |
| RF-02 | El sistema debe permitir al administrativo cargar documentos informativos en formato PDF. | Alta |
| RF-03 | El sistema debe permitir al administrativo editar el título y la descripción de un documento ya cargado. | Media |
| RF-04 | El sistema debe permitir al administrativo eliminar documentos del sistema. | Media |
| RF-05 | El sistema debe permitir al administrativo categorizar los documentos por tipo o área médica (cardiología, nefrología, etc.). | Alta |
| RF-06 | El sistema debe generar automáticamente un código QR único para cada documento cargado. | Alta |
| RF-07 | El sistema debe mostrar el código QR asociado a cada documento para su impresión o visualización. | Alta |
| RF-08 | El sistema debe permitir al paciente acceder al contenido del documento escaneando el código QR con su dispositivo móvil, sin necesidad de autenticación. | Alta |
| RF-09 | El sistema debe mostrar el documento en una vista optimizada para dispositivos móviles al ser accedido mediante QR. | Alta |
| RF-10 | El sistema debe permitir al administrativo crear y publicar encuestas de satisfacción con preguntas de distintos tipos (opción múltiple, escala, texto libre). | Media |
| RF-11 | El paciente debe poder completar y enviar una encuesta de satisfacción desde su dispositivo móvil. | Alta |
| RF-12 | El sistema debe almacenar las respuestas de las encuestas en la base de datos para su posterior análisis. | Alta |
| RF-13 | El sistema debe permitir al administrativo visualizar los resultados y estadísticas básicas de las encuestas respondidas. | Media |

**Documentos a incluir:**
- Indicaciones de interrupción voluntaria del embarazo
- Prostatectomía radical (indicaciones e información para el paciente)
- Preparación para estudios imagenológicos
- Estudios diagnósticos con pertecneciato
- Centellograma de perfusión miocárdica
- Indicaciones ecocardiograma con dobutamina
- Indicaciones para pacientes en tratamiento con warfarina
- Indicaciones ecocardiograma transesofágico
- Indicaciones para ingreso a centro de nefrología y trasplante
- Plan de alta enfermería, Nefrología
- Indicaciones de enfermería para usuarios trasplantados
- Prevención de infecciones
- Encuesta de satisfacción del usuario trasplantado
- Pauta para pacientes ostomizados

## Requerimientos Funcionales — Módulo de Trazabilidad de Ambulancias

| ID | Descripción | Prioridad |
|---|---|---|
| RF-14 | El sistema debe permitir al administrativo registrar una nueva solicitud de traslado en ambulancia. | Alta |
| RF-15 | El registro de traslado debe incluir: conductor responsable, copiloto/acompañante, paciente o elemento a trasladar, punto de origen, destino, hora de salida y hora estimada de llegada. | Alta |
| RF-16 | El sistema debe permitir registrar el tipo de elemento trasladado (paciente biológico, equipamiento médico, insumos). | Alta |
| RF-17 | El sistema debe permitir al administrativo consultar y visualizar la lista de traslados registrados con su estado actual. | Alta |
| RF-18 | El sistema debe contemplar los siguientes estados para cada traslado: Pendiente, En curso, Completado, Cancelado. | Alta |
| RF-19 | El sistema debe permitir al administrativo actualizar el estado del traslado durante su ciclo operativo (salida → en ruta → llegada a destino → retorno → finalizado). | Alta |
| RF-20 | El sistema debe permitir registrar y gestionar rutas dentro del circuito nacional asociadas a los traslados. | Media |
| RF-21 | El sistema debe permitir al administrativo visualizar el historial de traslados realizados con posibilidad de filtrar por fecha, conductor o estado. | Media |

## Requerimientos No Funcionales

| ID | Descripción |
|---|---|
| RNF-01 | **Arquitectura**: El sistema debe desarrollarse siguiendo una arquitectura hexagonal (puertos y adaptadores). |
| RNF-02 | **Base de datos**: El sistema debe utilizar MySQL como motor de base de datos relacional. |
| RNF-03 | **Frontend**: La interfaz debe ser responsive, desarrollada con HTML5, CSS3, JavaScript ES6+ y Bootstrap 5. |
| RNF-04 | **Backend**: El sistema debe desarrollarse en PHP >= 8.1. |
| RNF-05 | **Autenticación**: El sistema debe integrarse con el sistema de autenticación existente del hospital (usuario y contraseña). |
| RNF-06 | **Alojamiento**: El sistema debe alojarse en los servidores del DTI del Hospital de Clínicas (piso 6). |
| RNF-07 | **Seguridad**: El sistema debe implementar medidas contra SQL injection, XSS y CSRF. |
| RNF-08 | **Disponibilidad**: El sistema debe estar disponible durante el horario operativo del hospital. |
| RNF-09 | **Rendimiento**: La generación del código QR y la visualización de documentos debe realizarse en menos de 3 segundos. |
| RNF-10 | **Mantenibilidad**: El código debe seguir el estándar PSR-4 para autoloading y buenas prácticas de PHP. |

## Restricciones

| ID | Descripción |
|---|---|
| RES-01 | No se pueden modificar ni alterar los sistemas preexistentes del hospital más allá de agregar accesos directos en el panel principal. |
| RES-02 | El sistema debe funcionar en los navegadores modernos (Chrome, Firefox, Edge, Safari) en sus versiones actuales. |
| RES-03 | La información debe almacenarse exclusivamente en los servidores del hospital, sin depender de servicios externos en la nube. |
