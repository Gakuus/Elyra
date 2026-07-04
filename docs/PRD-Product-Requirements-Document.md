# Documento de Requisitos del Producto (PRD) — Elyra

| Campo | Detalle |
|-------|---------|
| **Producto** | Elyra — Sistema de Gestión Documental y Trazabilidad de Ambulancias |
| **Cliente** | Hospital de Clínicas — DTI (Piso 6) |
| **Versión** | 1.0 |
| **Fecha** | 2026 |
| **Autores** | Alan, Kevin, Tom |

---

## 1. Resumen Ejecutivo

Elyra es un sistema web modular desarrollado para el Hospital de Clínicas que resuelve tres problemas críticos: la gestión de identidad y acceso de usuarios, la gestión y acceso digital a documentación informativa para pacientes, y la trazabilidad de traslados en ambulancia a nivel nacional. El sistema se integra al panel centralizado existente del hospital y está desarrollado con PHP, MySQL, JavaScript y Bootstrap, siguiendo una arquitectura hexagonal.

---

## 2. Glosario de Términos

| Término | Definición |
|---------|------------|
| **Elyra** | Nombre del sistema web modular desarrollado para el Hospital de Clínicas |
| **DTI** | Departamento Técnico de Informática del hospital, encargado de la infraestructura tecnológica |
| **QR** | Código Quick Response: código de barras bidimensional que almacena información legible por dispositivos móviles |
| **MER** | Modelo Entidad Relación: representación gráfica de las entidades y relaciones de la base de datos |
| **MR** | Modelo Relacional: esquema detallado de tablas, campos, tipos y claves de la base de datos |
| **Arquitectura Hexagonal** | Patrón de arquitectura que aísla la lógica de negocio de los detalles técnicos externos mediante puertos y adaptadores |
| **Table-per-class** | Estrategia de herencia en BD donde cada clase concreta tiene su propia tabla |
| **LAMP** | Stack tecnológico: Linux, Apache, MySQL, PHP |
| **CRUD** | Create, Read, Update, Delete — operaciones básicas de persistencia |
| **FODA** | Fortalezas, Oportunidades, Debilidades y Amenazas — análisis estratégico |
| **Sprint** | Período de trabajo definido dentro de la metodología Scrum para completar tareas planificadas |
| **GPU** | Graphics Processing Unit — utilizada en renderizado 3D con Blender |
| **Token de acceso** | Identificador único generado para cada paciente, permite acceder a documentos sin autenticación |
| **PSR-4** | Estándar de PHP para autoloading de clases mediante namespaces |

---

## 3. Planteamiento del Problema

### 3.1 Gestión de Identidad y Acceso

El hospital cuenta con un sistema centralizado de autenticación, pero no existe un módulo digital propio del sistema Elyra que gestione los usuarios (funcionarios y pacientes), sus roles, permisos y credenciales de forma organizada. Cada tipo de usuario tiene necesidades distintas: los funcionarios requieren inicio de sesión con usuario y contraseña, mientras que los pacientes acceden mediante tokens QR sin necesidad de autenticación.

### 3.2 Gestión de Documentación

El Hospital de Clínicas imprime y distribuye físicamente documentos informativos para pacientes (indicaciones médicas, preparación para estudios, guías de tratamiento, encuestas), generando costos operativos recurrentes, imposibilidad de actualización en tiempo real y dependencia de logística de distribución.

### 3.3 Trazabilidad de Ambulancias

Los traslados en ambulancia se gestionan sin un sistema digital centralizado, lo que impide conocer en tiempo real el estado de cada operación, generar estadísticas históricas y mantener un control eficiente de los recursos de transporte.

---

## 4. Visión del Producto

Elyra será la plataforma digital de referencia para la gestión de identidad, la documentación informativa y la trazabilidad logística del Hospital de Clínicas, permitiendo reducir costos de impresión, mejorar la experiencia del paciente y optimizar la gestión del transporte en ambulancia mediante una solución web integrada, segura y fácil de usar.

---

## 5. Objetivos y Métricas

### 4.1 Objetivos

| ID | Objetivo | Módulo |
|----|----------|--------|
| OBJ-01 | Implementar un módulo de identidad que gestione usuarios, roles y autenticación | Identidad |
| OBJ-02 | Permitir el registro y administración de funcionarios con distintos roles | Identidad |
| OBJ-03 | Gestionar el acceso de pacientes mediante tokens QR | Identidad |
| OBJ-04 | Implementar un módulo de carga y administración de documentos informativos en formato PDF | Documentación |
| OBJ-05 | Generar códigos QR únicos para cada documento, permitiendo acceso móvil sin autenticación | Documentación |
| OBJ-06 | Implementar encuestas de satisfacción digitales con almacenamiento de respuestas | Documentación |
| OBJ-07 | Reducir los costos operativos de impresión del hospital | Documentación |
| OBJ-08 | Desarrollar un módulo de registro y seguimiento de traslados en ambulancia | Ambulancias |
| OBJ-09 | Implementar un sistema de cambios de estado para seguimiento en tiempo real | Ambulancias |
| OBJ-10 | Integrar los módulos al panel centralizado existente del hospital | Todos |
| OBJ-11 | Desarrollar con arquitectura hexagonal para garantizar mantenibilidad | Todos |

### 4.2 Métricas de Éxito

| Métrica | Objetivo | Cómo se mide |
|---------|----------|--------------|
| Reducción de documentos impresos | > 50% | Comparativa de consumo de papel antes/después |
| Tiempo de acceso a documento vía QR | < 3 segundos | Pruebas de rendimiento |
| Traslados registrados digitalmente | 100% | Cobertura del sistema |
| Disponibilidad del sistema | > 99% | Uptime del servidor |
| Usuarios registrados correctamente | 100% | Verificación de datos en BD |

---

## 6. Interesados (Stakeholders)

| Interesado | Rol | Interés |
|------------|-----|---------|
| DTI Hospital de Clínicas | Cliente / Propietario de infraestructura | Alojamiento, integración, seguridad |
| Personal Administrativo | Usuario principal del sistema | Gestión de documentos y traslados |
| Pacientes | Usuario final (módulo documentación) | Acceso a información médica |
| Docentes | Evaluadores del proyecto | Seguimiento académico |
| Equipo de desarrollo (Alan, Kevin, Tom) | Implementadores | Desarrollo y mantenimiento |

---

## 7. Personas de Usuario

### 6.1 Administrativo (Funcionario)

| Atributo | Descripción |
|----------|-------------|
| **Nombre** | María García |
| **Rol** | Administrativa del Hospital de Clínicas |
| **Uso** | Diario, múltiples veces al día |
| **Necesidades** | Iniciar sesión, subir documentos, gestionar traslados, ver estado de operaciones |
| **Competencia técnica** | Media — usuaria de sistemas informáticos administrativos |
| **Canal** | PC de escritorio en el hospital |

### 6.2 Paciente

| Atributo | Descripción |
|----------|-------------|
| **Nombre** | Juan Pérez |
| **Rol** | Paciente del hospital |
| **Uso** | Esporádico, cuando recibe un documento |
| **Necesidades** | Acceder a información médica desde su celular sin tener que crear una cuenta |
| **Competencia técnica** | Baja a media |
| **Canal** | Dispositivo móvil propio |

### 6.3 Super Admin

| Atributo | Descripción |
|----------|-------------|
| **Nombre** | Carlos Rodríguez |
| **Rol** | Encargado del DTI del hospital |
| **Uso** | Semanal, para administrar usuarios y configuraciones |
| **Necesidades** | Crear funcionarios, asignar roles, gestionar permisos |
| **Competencia técnica** | Alta |
| **Canal** | PC de escritorio |

---

## 8. Alcance

### 7.1 Dentro del Alcance

**Módulo de Identidad:**
- Registro y autenticación de funcionarios con usuario y contraseña
- Roles de usuario: admin, superadmin, conductor
- Gestión de perfiles de funcionarios (datos personales, licencia de conducir, teléfono)
- Registro de pacientes con token de acceso único
- Herencia de tabla USUARIO base para funcionarios y pacientes
- Control de sesión y cierre de sesión

**Módulo de Documentación:**
- Autenticación mediante sistema centralizado del hospital
- CRUD de documentos informativos (subir, editar, eliminar, listar)
- Categorización de documentos por área médica
- Generación automática de código QR por documento
- Vista pública de documento vía QR (sin autenticación)
- Creación y gestión de encuestas de satisfacción
- Respuesta a encuestas desde dispositivo móvil
- Visualización de resultados y estadísticas de encuestas

**Módulo de Ambulancias:**
- Registro de solicitudes de traslado (conductor, copiloto, elemento, origen, destino, horarios)
- Clasificación del elemento trasladado (paciente, equipamiento, insumo)
- Actualización de estado del traslado (pendiente → en curso → en destino → en retorno → completado / cancelado)
- Consulta de traslados activos con estado actual
- Gestión de rutas del circuito nacional
- Historial de traslados con filtros
- Trazabilidad completa mediante bitácora de cambios de estado

### 7.2 Fuera del Alcance

| Ítem | Justificación |
|------|---------------|
| Aplicación móvil nativa | Se accede vía navegador web responsive |
| Módulo de historia clínica electrónica | No solicitado por el cliente |
| Facturación electrónica | No solicitado por el cliente |
| Sistema de turnos | No solicitado por el cliente |
| Integración con WhatsApp / notificaciones push | Alcance limitado |
| App en la nube pública | Restricción del hospital: alojamiento en servidores propios |

---

## 9. Priorización MoSCoW

| Categoría | Significado | Cantidad de requisitos |
|-----------|-------------|----------------------|
| **Must** | Imprescindible para el MVP. Sin esto el producto no funciona | 12 |
| **Should** | Importante pero no crítico. Se puede postergar si falta tiempo | 9 |
| **Could** | Deseable. Se implementa si hay tiempo disponible | 1 |
| **Won't** | No se hará en esta versión | 0 |

---

## 10. Requisitos Funcionales

### 10.1 Módulo de Identidad

| ID | Funcionalidad | Descripción | Prioridad | MoSCoW | US |
|----|--------------|-------------|-----------|--------|-----|
| FR-01 | Inicio de sesión | El funcionario debe poder iniciar sesión con usuario y contraseña validados contra el sistema centralizado del hospital | Alta | Must | HU-01 |
| FR-02 | Cierre de sesión | El funcionario debe poder cerrar sesión de forma segura | Alta | Must | HU-01 |
| FR-03 | Recuperación de contraseña | El sistema debe permitir restablecer la contraseña olvidada | Media | Should | — |
| FR-04 | Registro de funcionarios | El superadmin debe poder dar de alta nuevos funcionarios con sus datos (nombre, username, contraseña, rol, licencia, teléfono) | Alta | Must | — |
| FR-05 | Gestión de roles | El sistema debe soportar los roles: admin, superadmin, conductor, cada uno con distintos permisos | Alta | Must | — |
| FR-06 | Registro de pacientes | Los pacientes se registran automáticamente al viajar en ambulancia, con un token de acceso único | Media | Should | — |
| FR-07 | Perfil de usuario | El funcionario debe poder ver y editar su perfil (teléfono, email) | Baja | Could | — |
| FR-08 | Listado de usuarios | El superadmin debe poder listar y buscar funcionarios activos e inactivos | Media | Should | — |

### 10.2 Módulo de Documentación

| ID | Funcionalidad | Descripción | Prioridad | MoSCoW | US |
|----|--------------|-------------|-----------|--------|-----|
| FR-09 | Subir documento | Cargar PDF con título, descripción y categoría; generar QR automáticamente | Alta | Must | HU-02 |
| FR-10 | Editar documento | Modificar título, descripción y categoría (el QR existente sigue siendo válido) | Media | Should | HU-03 |
| FR-11 | Eliminar documento | Eliminar documento con confirmación; el QR deja de funcionar | Media | Should | HU-04 |
| FR-12 | Categorizar documentos | Clasificar por área médica (cardiología, nefrología, etc.) | Alta | Must | HU-05 |
| FR-13 | Visualizar e imprimir QR | Mostrar QR en tamaño adecuado para impresión y descarga | Alta | Must | HU-06 |
| FR-14 | Acceso público por QR | Vista pública del documento sin autenticación, optimizada para móviles | Alta | Must | HU-07 |
| FR-15 | Crear encuesta | Crear encuesta con preguntas de opción múltiple, escala y texto libre | Media | Should | HU-08 |
| FR-16 | Responder encuesta | Formulario responsive para pacientes, sin autenticación | Alta | Must | HU-09 |
| FR-17 | Ver resultados | Estadísticas y gráficos de respuestas, filtro por fechas | Media | Should | HU-10 |
| FR-18 | Listar documentos | Listado con búsqueda, filtro por categoría y paginación | Alta | Must | HU-11 |

### 10.3 Módulo de Ambulancias

| ID | Funcionalidad | Descripción | Prioridad | MoSCoW | US |
|----|--------------|-------------|-----------|--------|-----|
| FR-19 | Registrar traslado | Formulario con conductor, copiloto, elemento, origen, destino, horarios, ruta | Alta | Must | HU-12 |
| FR-20 | Clasificar elemento trasladado | Tipo: paciente biológico, equipamiento médico, insumo | Alta | Must | HU-12 |
| FR-21 | Actualizar estado | Ciclo: pendiente → en curso → en destino → en retorno → completado / cancelado | Alta | Must | HU-13 |
| FR-22 | Consultar traslados activos | Lista con estado actual, filtros por estado | Alta | Must | HU-14 |
| FR-23 | Gestionar rutas | CRUD de rutas con origen, destino y distancia | Media | Should | HU-15 |
| FR-24 | Historial de traslados | Listado histórico con filtros por fecha, conductor y estado | Media | Should | HU-16 |

---

## 11. Requisitos No Funcionales

| ID | Categoría | Requisito | Fuente |
|----|-----------|-----------|--------|
| NFR-01 | Arquitectura | Arquitectura hexagonal (puertos y adaptadores) | RNF-01 |
| NFR-02 | Base de datos | MySQL como motor de base de datos relacional | RNF-02 |
| NFR-03 | Frontend | Interfaz responsive con HTML5, CSS3, JavaScript ES6+, Bootstrap 5 | RNF-03 |
| NFR-04 | Backend | PHP >= 8.1 con tipado estricto, PSR-4 para autoloading | RNF-04, RNF-10 |
| NFR-05 | Autenticación | Integración con sistema de autenticación existente del hospital | RNF-05 |
| NFR-06 | Alojamiento | Servidores del DTI del Hospital de Clínicas (piso 6) | RNF-06 |
| NFR-07 | Seguridad | Protección contra SQL injection, XSS y CSRF | RNF-07 |
| NFR-08 | Disponibilidad | Disponible durante horario operativo del hospital | RNF-08 |
| NFR-09 | Rendimiento | Generación de QR y visualización de documentos < 3 segundos | RNF-09 |
| NFR-10 | Compatibilidad | Navegadores modernos: Chrome, Firefox, Edge, Safari (versiones actuales) | RES-02 |
| NFR-11 | Residencia de datos | Almacenamiento exclusivo en servidores del hospital, sin nube externa | RES-03 |

### 11.1 Requisitos de Seguridad

| ID | Categoría | Requisito |
|----|-----------|-----------|
| SEG-01 | Hash de contraseñas | Almacenar contraseñas con bcrypt (cost 12+) |
| SEG-02 | Sesión | Timeout de sesión por inactividad (30 minutos) |
| SEG-03 | Sesión | Cerrar sesión al cerrar el navegador (session cookie) |
| SEG-04 | SQL injection | Uso de prepared statements / PDO para todas las consultas |
| SEG-05 | XSS | Escapar toda salida HTML con htmlspecialchars() |
| SEG-06 | CSRF | Implementar tokens CSRF en todos los formularios |
| SEG-07 | HTTPS | Forzar conexión HTTPS en producción |
| SEG-08 | Roles | Verificación de permisos por rol en cada endpoint |
| SEG-09 | Logs | Registrar intentos de inicio de sesión fallidos |
| SEG-10 | Input validation | Validar y sanitizar todo ingreso de datos del usuario |
| SEG-11 | Archivos | Validar tipo MIME y tamaño máximo en subida de archivos (PDF, 10MB) |
| SEG-12 | Headers | Configurar headers de seguridad (CSP, X-Frame-Options, HSTS) |

---

## 12. Requisitos de Datos

### 12.1 Resumen de la Base de Datos

El sistema requiere 13 tablas organizadas en tres módulos:

| Módulo | Tablas | Propósito |
|--------|--------|-----------|
| Identidad | USUARIO, FUNCIONARIO, PACIENTE | Gestión de personas con herencia table-per-class |
| Documentación | CATEGORIA, DOCUMENTO, ENCUESTA, PREGUNTA, RESPUESTA | Documentos, QR, encuestas y respuestas |
| Ambulancias | VEHICULO, RUTA, TRASLADO, ELEMENTO_TRASLADO, HISTORIAL_ESTADO | Traslados, rutas y trazabilidad |

### 12.2 Entidades Clave

- **USUARIO**: Tabla base con datos comunes de todas las personas (nombre, apellido, email, documento de identidad)
- **FUNCIONARIO**: Extiende USUARIO, agrega credenciales de acceso (username, password_hash), rol (admin, superadmin, conductor), licencia de conducir, teléfono
- **PACIENTE**: Extiende USUARIO, agrega token de acceso único para visualizar documentos y viajar en ambulancia
- **DOCUMENTO**: PDF informativos con QR único, categoría y encuesta asociada
- **TRASLADO**: Solicitudes de viaje con conductor, ruta, horarios y estados
- **ELEMENTO_TRASLADO**: Tabla polimórfica para paciente, órgano, equipamiento o insumo

### 12.3 Flujo de Identidad

```
Registro de Funcionario (Super Admin)
         │
         ▼
Creación en USUARIO (datos básicos)
         │
         ▼
Creación en FUNCIONARIO (username, password_hash, rol)
         │
         ▼
Inicio de sesión (login)
         │
         ▼
Acceso a módulos según rol
         │
         ▼
Cierre de sesión
```

```
Paciente llega al hospital
         │
         ▼
Se registra en USUARIO + PACIENTE (token generado)
         │
         ▼
Accede a documentos vía QR (sin login)
         │
         ▼
Viaja en ambulancia (asociado como elemento trasladado)
```

---

## 13. Requisitos de Integración

| Integración | Tipo | Descripción |
|-------------|------|-------------|
| Sistema centralizado del hospital | Autenticación | Validación de credenciales existentes (usuario y contraseña) |
| Panel principal del hospital | UI | Accesos directos en el panel existente hacia los nuevos módulos |
| Impresora | QR | Impresión de códigos QR para distribución física |

---

## 14. Restricciones

| ID | Restricción | Implicancia |
|----|-------------|-------------|
| C-01 | No modificar sistemas preexistentes del hospital | Solo se agregan accesos directos al panel principal |
| C-02 | Alojamiento en servidores propios del hospital | Sin cloud computing; recursos limitados del DTI |
| C-03 | Stack tecnológico definido | PHP, MySQL, HTML/CSS/JS, Bootstrap |
| C-04 | Equipo pequeño (3 desarrolladores) | Priorización estricta de funcionalidades |
| C-05 | Proyecto académico con fechas fijas | Calendario de entregas inamovible |

---

## 15. Supuestos y Dependencias

### 15.1 Supuestos

| ID | Supuesto |
|----|----------|
| A-01 | El DTI del hospital proporcionará acceso al sistema centralizado para la integración |
| A-02 | Los servidores del DTI tienen Apache/Nginx, MySQL y PHP disponibles |
| A-03 | Los administrativos cuentan con navegador web moderno y acceso a internet |
| A-04 | Los pacientes poseen dispositivos móviles con cámara y conexión a internet |
| A-05 | Las credenciales del sistema centralizado serán provistas por el hospital |

### 15.2 Dependencias

| ID | Dependencia | Riesgo |
|----|-------------|--------|
| D-01 | Acceso a servidores del DTI para despliegue | Retraso si no está disponible |
| D-02 | Documentación del sistema de autenticación existente | Documentación incompleta |
| D-03 | Aprobación del cliente para los diseños propuestos | Cambios de último momento |
| D-04 | Definición de rutas del circuito nacional | Datos incompletos |

---

## 16. Arquitectura del Sistema

| Capa | Tecnología | Propósito |
|------|------------|-----------|
| Frontend | HTML5, CSS3, Bootstrap 5, JavaScript ES6+ | Interfaz de usuario responsive |
| Backend | PHP >= 8.1 | Lógica de negocio, API REST |
| Base de datos | MySQL 8+ | Persistencia de datos |
| Servidor | Apache / Nginx | Servidor web |
| Arquitectura | Hexagonal (Puertos y Adaptadores) | Separación de capas: Domain, Application, Infrastructure |
| Control de versiones | Git + GitHub | Control de versiones y colaboración |
| CI/CD | GitHub Actions | Validación automática de sintaxis |

---

## 17. Flujo de Navegación del Usuario

### 18.1 Funcionario (Admin / Super Admin)

```
Inicio de sesión
      │
      ▼
┌─────────────────────────────┐
│      Panel Principal        │
│  (dashboard con módulos)    │
└──────┬──────────┬───────────┘
       │          │
       ▼          ▼
┌──────────┐ ┌──────────┐
│ Documen- │ │ Ambula-  │
│ tación   │ │ ncias    │
└────┬─────┘ └────┬─────┘
     │            │
     ▼            ▼
┌──────────┐ ┌──────────┐
│ Listar   │ │ Registrar│
│ docs     │ │ traslado │
│ Subir    │ │ Ver acti-│
│ Editar   │ │ vos      │
│ Eliminar │ │ Historial│
│ Encuestas│ │ Rutas    │
└──────────┘ └──────────┘
```

### 18.2 Funcionario (Conductor)

```
Inicio de sesión
      │
      ▼
┌─────────────────────────────┐
│      Panel Conductor        │
│  (solo ambulancias, vista   │
│   de sus traslados activos) │
└─────────────────────────────┘
      │
      ▼
┌─────────────────────────────┐
│     Detalle de Traslado     │
│  Ver ruta, actualizar estado│
└─────────────────────────────┘
```

### 18.3 Paciente (acceso por QR)

```
Escanea QR (desde móvil)
      │
      ▼
┌─────────────────────────────┐
│  Vista pública del documen- │
│  to (sin autenticación,     │
│  responsive para móviles)   │
└─────────────────────────────┘
      │
      ▼
┌─────────────────────────────┐
│  Encuesta asociada (opcio-  │
│  nal, sin autenticación)    │
└─────────────────────────────┘
```

---

## 18. Matriz de Riesgos

| ID | Riesgo | Probabilidad | Impacto | Mitigación | Plan de Contingencia |
|----|--------|-------------|---------|------------|---------------------|
| R-01 | El DTI no brinda acceso a servidores a tiempo | Alta | Alto | Solicitar acceso con anticipación; tener entorno local listo | Desplegar en un VPS temporal de prueba |
| R-02 | Documentación del sistema de autenticación existente es incompleta | Media | Alto | Reunión técnica con DTI para mapear endpoints | Implementar autenticación propia como fallback |
| R-03 | Cambios de alcance solicitados por el cliente | Media | Medio | MoSCoW definido y firmado; canal único de cambios | Aplazar funcionalidades "Could" al próximo sprint |
| R-04 | Rotación o falta de disponibilidad del equipo | Baja | Alto | Documentación continua del código; pair programming | Redistribuir tareas entre los miembros disponibles |
| R-05 | Dependencia de datos externos (rutas de traslado) | Media | Medio | Solicitar datos al inicio del proyecto | Usar datos mockeables y permitir edición manual |
| R-06 | Problemas de compatibilidad con navegadores del hospital | Baja | Medio | Pruebas en los navegadores objetivo desde el primer sprint | Proveer polyfills o recomendar actualización |
| R-07 | Fechas de entrega académicas no flexibles | Alta | Alto | Planificación realista con margen; entregas incrementales | Priorizar funcionalidades Must sobre el resto |

---

## 19. Plan de Entregas por Sprint

### Sprint 1 (Semanas 1–2): Fundación
| Tarea | Responsable |
|-------|-------------|
| Configuración del repositorio Git y ramas | Equipo |
| Instalación y configuración del entorno (PHP, MySQL) | Equipo |
| Diseño de la base de datos (MER y DDL) | Equipo |
| Creación de tablas (módulo Identidad) | Backend |
| Maquetado de login y layout base (Bootstrap) | Frontend |

### Sprint 2 (Semanas 3–4): Identidad y Documentación base
| Tarea | Responsable |
|-------|-------------|
| CRUD de usuarios (registro, listado, edición) | Backend |
| Login/logout con integración al sistema del hospital | Backend |
| Gestión de roles y permisos | Backend |
| CRUD de categorías de documentos | Backend |
| Subida y listado de documentos (PDF, título, descripción) | Backend |
| Frontend: formularios de documentos y categorías | Frontend |

### Sprint 3 (Semanas 5–6): QR, Encuestas y Ambulancias
| Tarea | Responsable |
|-------|-------------|
| Generación de QR automática al subir documento | Backend |
| Vista pública del documento por QR | Backend + Frontend |
| CRUD de encuestas (preguntas, opciones) | Backend |
| Responder encuesta desde vista pública | Frontend |
| Ver resultados de encuestas (gráficos) | Frontend |
| CRUD de rutas de ambulancia | Backend |

### Sprint 4 (Semanas 7–8): Traslados y Cierre
| Tarea | Responsable |
|-------|-------------|
| Registro de traslados (conductor, ruta, elemento) | Backend |
| Actualización de estados de traslado | Backend + Frontend |
| Historial y consulta de traslados activos | Frontend |
| Pruebas funcionales integrales | Equipo |
| Despliegue en servidores del DTI | Equipo |
| Manual de instalación y documentación final | Equipo |

---

## 20. Criterios de Liberación

| ID | Criterio | Descripción |
|----|----------|-------------|
| R-01 | Requisitos alta prioridad | Todos los requisitos funcionales de alta prioridad implementados |
| R-02 | Pruebas funcionales | Pruebas funcionales aprobadas para los tres módulos |
| R-03 | Autenticación | Integración con el sistema de autenticación del hospital verificada |
| R-04 | QR funcional | Código QR generado y verificado funcional para documentos de prueba |
| R-05 | Despliegue | Sistema desplegado en servidores del DTI |
| R-06 | Manuales | Manual de instalación y configuración entregado |

---

## 21. Apéndice

### 21.1 Lista de Documentos para Carga Inicial

- Indicaciones de interrupción voluntaria del embarazo
- Prostatectomía radical
- Preparación para estudios imagenológicos
- Estudios diagnósticos con pertecneciato
- Centellograma de perfusión miocárdica
- Indicaciones ecocardiograma con dobutamina
- Indicaciones para pacientes en tratamiento con warfarina
- Indicaciones ecocardiograma transesofágico
- Indicaciones para ingreso a centro de nefrología
- Plan de alta enfermería, Nefrología
- Indicaciones de enfermería para usuarios trasplantados
- Prevención de infecciones
- Encuesta de satisfacción del usuario trasplantado
- Pauta para pacientes ostomizados

### 21.2 Ciclo de Estados de Traslados

```
PENDIENTE → EN CURSO → EN DESTINO → EN RETORNO → COMPLETADO
                                         ↘
                                       CANCELADO
```

### 21.3 Mapeo de Roles y Permisos

| Rol | Documentación | Ambulancias | Usuarios |
|-----|---------------|-------------|----------|
| Super Admin | CRUD completo | CRUD completo | CRUD completo |
| Admin | CRUD completo | CRUD completo | Solo lectura |
| Conductor | Solo lectura | Solo consulta | Solo su perfil |

### 21.4 Mapeo de Historias de Usuario

| Módulo | Épica | Historias de Usuario |
|--------|-------|---------------------|
| Identidad | Gestión de usuarios | HU-01 (login/logout), gestión de perfiles |
| Documentación | Gestión de documentos | HU-02, HU-03, HU-04, HU-05, HU-06, HU-11 |
| Documentación | Acceso paciente | HU-07 |
| Documentación | Encuestas | HU-08, HU-09, HU-10 |
| Ambulancias | Gestión de traslados | HU-12, HU-13, HU-14, HU-15, HU-16 |

---

*Documento generado a partir del relevamiento completo del proyecto Elyra para el Hospital de Clínicas.*
