# 4 - Marco Contextual

## La Institución

El **Hospital de Clínicas** es un centro hospitalario de referencia que brinda servicios de salud a la comunidad. Su Departamento Técnico de Informática (DTI), ubicado en el piso 6 de las instalaciones, es el responsable de la infraestructura tecnológica y el alojamiento de los sistemas institucionales.

## Situación Actual

### Gestión de documentación para pacientes

Actualmente, el hospital imprime y entrega en formato físico diversos documentos informativos a los pacientes. Esto incluye indicaciones médicas, preparación para estudios, guías de tratamiento y encuestas de satisfacción, entre otros. Este proceso implica:

- Un costo operativo recurrente en insumos de impresión y papel.
- La logística de distribución y reposición de documentos en las distintas áreas del hospital.
- La imposibilidad de actualizar la información de forma ágil una vez impresa.

### Gestión de traslados en ambulancia

El registro y seguimiento de los traslados en ambulancia se realiza sin un sistema digital centralizado. Las ambulancias parten desde el hospital hacia destinos dentro del circuito nacional, transportando pacientes, equipamiento médico o insumos. La falta de un sistema de información dificulta:

- El control en tiempo real del estado de cada traslado.
- La trazabilidad histórica de las operaciones realizadas.
- La generación de reportes y estadísticas sobre el servicio de transporte.

## Usuarios del Sistema

| Tipo de Usuario | Rol | Módulo |
|---|---|---|
| Administrativo | Gestiona documentos, da de alta traslados, realiza seguimiento | Ambos módulos |
| Paciente | Accede a documentos escaneando código QR, completa encuestas | Documentación |
| Conductor | Asignado a traslados (datos registrados en el sistema) | Ambulancias |

## Sistema Existente

El hospital cuenta con un sistema centralizado que dispone de un panel principal de acceso para los usuarios. Los nuevos módulos deberán integrarse a este panel como accesos directos, reutilizando las credenciales de autenticación existentes.
