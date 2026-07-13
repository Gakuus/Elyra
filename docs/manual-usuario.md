# Manual de Usuario — Elyra

## Sistema de Gestión Hospitalaria

**Hospital de Clínicas — Montevideo, Uruguay**
**Versión 1.0 — Julio 2026**

---

## Índice

1. [Introducción](#1-introducción)
2. [Primeros Pasos](#2-primeros-pasos)
3. [Panel Principal (Dashboard)](#3-panel-principal-dashboard)
4. [Gestión de Documentos](#4-gestión-de-documentos)
5. [Gestión de Traslados](#5-gestión-de-traslados)
6. [Mapa en Vivo y Tracking GPS](#6-mapa-en-vivo-y-tracking-gps)
7. [Encuestas de Satisfacción](#7-encuestas-de-satisfacción)
8. [Gestión de Conductores](#8-gestión-de-conductores)
9. [Gestión de Funcionarios](#9-gestión-de-funcionarios)
10. [Gestión de Rutas](#10-gestión-de-rutas)
11. [Gestión de Noticias](#11-gestión-de-noticias)
12. [Mi Perfil](#12-mi-perfil)
13. [Acceso Público](#13-acceso-público)
14. [Seguridad y Buenas Prácticas](#14-seguridad-y-buenas-prácticas)
15. [Preguntas Frecuentes](#15-preguntas-frecuentes)

---

## 1. Introducción

### ¿Qué es Elyra?

Elyra es un sistema de gestión hospitalaria diseñado para el Hospital de Clínicas de Montevideo. Permite gestionar:

- **Traslados de pacientes** entre hospitales con tracking GPS en tiempo real
- **Documentos médicos** con generación automática de códigos QR
- **Encuestas de satisfacción** para pacientes
- **Directorio de funcionarios** y conductores de ambulancias
- **Noticias internas** del hospital

### ¿Quién puede usar el sistema?

| Rol | Descripción | Acceso principal |
|-----|-------------|------------------|
| **Administrador** | Gestiona todo el sistema | Todos los módulos |
| **Super Admin** | Acceso total + config avanzada | Todos los módulos |
| **Conductor** | Conductor de ambulancia | Traslados + GPS tracking |
| **Copiloto** | Copiloto de ambulancia | Traslados + GPS tracking |
| **Médico** | Personal médico | Dashboard + perfil |
| **Enfermero** | Personal de enfermería | Dashboard + perfil |
| **Técnico** | Personal técnico | Dashboard + perfil |
| **Recepcionista** | Personal de recepción | Dashboard + perfil |
| **Farmacéutico** | Personal de farmacia | Dashboard + perfil |
| **Paciente** | Paciente del hospital | Documentos propios + encuestas |

### Requisitos del navegador

- Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- JavaScript habilitado
- Conexión a internet (para mapas y GPS)

---

## 2. Primeros Pasos

### 2.1 Iniciar Sesión

1. Abrí el navegador y andá a la dirección del sistema (ej: `http://localhost:8000`)
2. Vas a ver la pantalla de login con el logo de Elyra
3. Ingresá tu **usuario** y **contraseña**
4. Hacé clic en **"Iniciar sesión"**

> **Si te equivocás 5 veces**, tu cuenta se bloquea automáticamente durante 15 minutos por seguridad.

### 2.2 Crear Cuenta (Pacientes)

Si sos paciente y no tenés cuenta:

1. En la pantalla de login, hacé clic en **"¿No tenés cuenta? Registrate"**
2. Completá todos los campos obligatorios (marcados con asterisco rojo):
   - **Nombre** y **Apellido**
   - **Email** (se usa para recuperar la contraseña)
   - **Cédula** (8 dígitos, sin puntos ni guiones)
   - **Usuario** (mínimo 3 caracteres, será tu nombre de acceso)
   - **Teléfono** (opcional, 8-9 dígitos)
   - **Contraseña** (mínimo 8 caracteres — recomendamos usar mayúsculas, números y símbolos)
   - **Repetir contraseña**
3. Hacé clic en **"Crear cuenta"**
4. Vas a ser redirigido al panel principal

### 2.3 Recuperar Contraseña

Si olvidaste tu contraseña:

1. En la pantalla de login, hacé clic en **"¿Olvidaste tu contraseña?"**
2. Ingresá tu **email** registrado
3. Hacé clic en **"Enviar enlace de recuperación"**
4. Revisá tu bandeja de entrada (y la carpeta de spam)
5. Abrí el enlace del email y creá una **nueva contraseña**
6. Iniciá sesión con la nueva contraseña

> **El enlace expira en 1 hora** por seguridad. Si vence, solicitá uno nuevo.

---

## 3. Panel Principal (Dashboard)

### 3.1 Dashboard de Administradores

Al iniciar sesión, vas a ver el panel principal con:

**Estadísticas generales** (tarjetas superiores):
- Total de documentos
- Documentos generales
- Encuestas creadas
- Traslados activos
- Conductores activos

**Actividad reciente**:
- Últimos documentos subidos con fecha y quien los subió
- Links rápidos para ver o editar cada documento

**Acceso rápido**:
- Subir nuevo documento
- Ver documentos generales
- Ver encuestas
- Ver traslados

### 3.2 Dashboard de Pacientes

Los pacientes ven un panel simplificado:

- **Tus documentos**: cantidad de documentos asignados
- **Encuestas**: encuestas disponibles para responder
- **Traslados**: información de traslados
- **Acceso rápido**: links a documentos, encuestas y traslados

---

## 4. Gestión de Documentos

### 4.1 Subir Documento (Administradores)

1. Andá a **Documentación → Subir** en el menú lateral
2. Completá el formulario:
   - **Título**: nombre descriptivo del documento
   - **Categoría**: seleccioná el tipo de documento (ej: Consentimiento, Historia Clínica)
   - **Especialidad**: opcional, asociar a una especialidad médica
   - **Paciente**: opcional, asociar a un paciente específico
   - **Descripción**: opcional, detalles adicionales
   - **Archivo**: seleccioná el archivo PDF (máximo 10 MB)
3. Hacé clic en **"Subir documento"**
4. El sistema genera automáticamente un **código QR** para acceso público

### 4.2 Ver Documentos

**Documentos Generales** (administradores):
1. Andá a **Documentación → Generales**
2. Usá la **barra de búsqueda** para filtrar por título
3. Usá el **filtro de categoría** para ver solo un tipo
4. Hacé clic en el ícono del ojo 👁️ para ver el detalle
5. Hacé clic en el ícono de descarga 📥 para descargar el PDF

**Documentos por Paciente** (administradores):
1. Andá a **Documentación → Por CI**
2. Ingresá la **cédula del paciente** (8 dígitos)
3. Se mostraran todos los documentos asignados a ese paciente

**Mis Documentos** (pacientes):
1. Andá a **Documentos** en el menú lateral
2. Se muestran solo tus documentos
3. Usá la búsqueda o el filtro de categoría para encontrar algo específico

### 4.3 Editar Documento

1. En la lista de documentos, hacé clic en el ícono de editar ✏️
2. Modificá los campos que necesites (título, descripción, categoría, especialidad, paciente)
3. Hacé clic en **"Guardar cambios"**

> **Nota**: No podés cambiar el archivo PDF una vez subido. Si necesitás reemplazarlo, subí uno nuevo y eliminá el anterior.

### 4.4 Eliminar Documento

1. En la lista de documentos, hacé clic en el ícono de eliminar 🗑️
2. Aparecerá un **modal de confirmación** preguntando si estás seguro
3. Hacé clic en **"Eliminar"** para confirmar

> **Atención**: Esta acción es permanente. El documento y su archivo PDF se eliminarán del sistema.

### 4.5 Código QR

Cada documento subido genera automáticamente un **código QR** que permite acceso público al PDF. Para verlo:

1. En la vista de detalle del documento, hacé clic en **"Ver QR"**
2. Se muestra el código QR que puede escanearse con cualquier celular
3. Al escanear, se abre el PDF directamente sin necesidad de iniciar sesión

---

## 5. Gestión de Traslados

### 5.1 Crear un Traslado

1. Andá a **Ambulancias → Nuevo** en el menú lateral
2. Completá el formulario:

**Conductor** (obligatorio):
- Seleccioná el conductor de la lista desplegable

**Copiloto** (opcional):
- Seleccioná el copiloto si corresponde

**Tipo de traslado** (obligatorio):
- **Paciente**: traslado de un paciente entre hospitales
- **Equipamiento**: traslado de equipamiento médico
- **Insumo**: traslado de insumos médicos
- **Órgano**: traslado de órganos para trasplante

**Según el tipo seleccionado**:
- Si es **Paciente**: seleccioná el paciente de la lista
- Si es **Órgano**: seleccioná el órgano del catálogo + el paciente asociado
- Si es **Equipamiento** o **Insumo**: seleccioná el elemento del catálogo

**Origen y Destino** (obligatorio):
- Seleccioná el punto de origen (ej: Hospital de Clínicas - Emergencias)
- Seleccioná el punto de destino (ej: Sanatorio Español)
- **No pueden ser iguales**

**Ruta** (opcional):
- Seleccioná una ruta predefinida del sistema
- La distancia se carga automáticamente para calcular la hora de llegada estimada

**Fecha y hora de salida** (obligatorio):
- Seleccioná la fecha y hora programadas de salida

**Observaciones** (opcional):
- Agregá notas adicionales sobre el traslado

3. Hacé clic en **"Crear traslado"**

### 5.2 Estados del Traslado

Los traslados pasan por una secuencia de estados:

```
PENDIENTE → EN CURSO → EN DESTINO → EN RETORNO → COMPLETADO
    ↓
  CANCELADO (desde cualquier estado antes de Completado)
```

| Estado | Significado |
|--------|-------------|
| **Pendiente** | Traslado creado, esperando iniciar |
| **En curso** | La ambulancia salió del origen |
| **En destino** | La ambulancia llegó al destino |
| **En retorno** | La ambulancia regresa al hospital |
| **Completado** | Traslado finalizado exitosamente |
| **Cancelado** | Traslado cancelado (con motivo obligatorio) |

### 5.3 Actualizar Estado del Traslado

1. Andá a **Ambulancias → Traslados**
2. Hacé clic en el traslado que querés actualizar
3. Hacé clic en **"Actualizar estado"**
4. Seleccioná el nuevo estado permitido
5. Agregá una **observación** (opcional pero recomendada)
6. Si cancelás, ingresá el **motivo de cancelación** (obligatorio)
7. Hacé clic en **"Confirmar"**

### 5.4 Ver Detalle del Traslado

1. Andá a **Ambulancias → Traslados**
2. Hacé clic en el código del traslado (ej: TR-001)
3. Vas a ver:
   - **Información general**: código, conductor, copiloto, origen, destino
   - **Elemento transportado**: tipo, descripción del paciente/equipo/insumo/órgano
   - **Timeline visual**: progreso del traslado con fechas reales
   - **Observaciones**: notas del traslado
   - **Motivo de cancelación**: si fue cancelado

### 5.5 Historial de Traslados

1. Andá a **Ambulancias → Historial**
2. Podés filtrar por:
   - **Estado**: pendiente, en curso, completado, cancelado, etc.
   - **Conductor**: nombre del conductor
   - **Fecha**: día específico
   - **Búsqueda**: texto libre
3. Los resultados se muestran en una tabla con fecha, código, conductor, elemento, origen, destino y estado

---

## 6. Mapa en Vivo y Tracking GPS

### 6.1 Mapa en Vivo

1. Andá a **Ambulancias → Mapa en vivo**
2. Se muestra un mapa interactivo de Montevideo con:
   - **Marcadores de origen** 🟢 (punto de partida de cada traslado)
   - **Marcadores de destino** 🔴 (punto de llegada de cada traslado)
   - **Rutas reales** (líneas azules siguiendo calles reales vía OSRM)
   - **Posición GPS de ambulancias** 🚑 (si están reportando ubicación)
3. La información se actualiza automáticamente cada 5 segundos
4. Hacé clic en un marcador para ver detalles del traslado
5. Usá la **barra lateral** para ver la lista de traslados activos

### 6.2 Tracking GPS (Conductores)

**Para conductores**, el tracking GPS funciona así:

1. Andá a **Ambulancias → Tracking**
2. Aceptá el permiso de **ubicación** cuando el navegador lo pida
3. El sistema empieza a reportar tu ubicación automáticamente cada 5 segundos
4. Se muestra tu posición actual en el mapa
5. Tu ubicación se comparte con los administradores en tiempo real

**Datos que se reportan**:
- Latitud y longitud
- Velocidad (si disponible)
- Dirección de movimiento (heading)
- ID del traslado activo

> **Importante**: El GPS debe estar habilitado en tu dispositivo para que el tracking funcione. Si no podés usar GPS, contactá al administrador.

### 6.3 Requisitos para GPS

- Navegador moderno (Chrome, Firefox, Safari, Edge)
- Permiso de ubicación otorgado
- Conexión a internet estable
- GPS habilitado en el dispositivo (especialmente en celulares)

---

## 7. Encuestas de Satisfacción

### 7.1 Crear Encuesta (Administradores)

1. Andá a **Documentación → Nueva encuesta**
2. Completá:
   - **Título**: nombre de la encuesta (ej: "Encuesta de Satisfacción Q1 2026")
   - **Descripción**: objetivo de la encuesta
3. Agregá preguntas haciendo clic en **"Agregar pregunta"**:
   - **Tipo de pregunta**:
     - **Texto libre**: el paciente escribe una respuesta
     - **Opción múltiple**: el paciente elige entre opciones predefinidas
     - **Escala**: del 1 al 5 (para satisfacción)
   - **Texto de la pregunta**: la pregunta en sí
   - **Opciones** (solo para opción múltiple): agregá las opciones disponibles
   - **Requerida**: marcá si la pregunta es obligatoria
4. Agregá todas las preguntas que necesites
5. Hacé clic en **"Crear encuesta"**

### 7.2 Ver Resultados

1. Andá a **Documentación → Encuestas**
2. Hacé clic en **"Ver resultados"** junto a la encuesta que querés consultar
3. Se muestran:
   - **Total de respuestas** recibidas
   - **Gráficos** por pregunta (barras para opción múltiple, torta para escala)
   - **Promedio** de escala (si hay preguntas de escala)
   - **Textos libres** que escribieron los pacientes

### 7.3 Responder Encuesta (Pacientes)

**Por el sistema**:
1. Andá a **Encuestas** en el menú lateral
2. Seleccioná la encuesta que querés responder
3. Respondé cada pregunta
4. Hacé clic en **"Enviar respuestas"**

**Por enlace público** (sin iniciar sesión):
1. Recibís un enlace por email o SMS
2. Abrí el enlace en tu navegador
3. Respondé las preguntas
4. Hacé clic en **"Enviar"**

---

## 8. Gestión de Conductores

> Solo administradores y super admins pueden gestionar conductores.

### 8.1 Ver Conductores

1. Andá a **Ambulancias → Conductores**
2. Se muestra la lista con:
   - Nombre y apellido
   - Usuario
   - Email
   - Licencia profesional
   - Teléfono
   - Estado (activo/inactivo)
3. Usá la **búsqueda** para encontrar un conductor específico
4. Filtrá por **activo/inactivo** con los botones superiores

### 8.2 Crear Conductor

1. Andá a **Ambulancias → Conductores → Crear**
2. Completá:
   - **Rol**: conductor o copiloto
   - **Nombre** y **Apellido**
   - **Usuario** (único en el sistema)
   - **Contraseña** (mínimo 8 caracteres)
   - **Email**
   - **Cédula** (8 dígitos)
   - **Licencia profesional**: seleccioná de la lista de 29 licencias válidas en Uruguay
   - **Categoría de licencia de conducir**: B1, B2, C1, C2, D1, D2
   - **Teléfono**
3. Hacé clic en **"Crear conductor"**

### 8.3 Editar Conductor

1. En la lista de conductores, hacé clic en el ícono de editar ✏️
2. Modificá los campos necesarios
3. Para cambiar la contraseña, dejá el campo vacío si no querés cambiarla
4. Hacé clic en **"Guardar cambios"**

### 8.4 Desactivar / Reactivar Conductor

**Desactivar**:
1. En la lista, hacé clic en el botón **"Desactivar"** 🔴
2. Confirmá en el modal que aparece
3. El conductor no podrá iniciar sesión hasta que se reactive

**Reactivar**:
1. Filtrá la lista por "Inactivos"
2. Hacé clic en **"Reactivar"** 🟢
3. Confirmá la acción

---

## 9. Gestión de Funcionarios

> Solo administradores y super admins.

### 9.1 Ver Funcionarios

1. Andá a **Ambulancias → Funcionarios**
2. Se muestra la lista completa de staff del hospital
3. Podés buscar por nombre, apellido o usuario
4. Filtrar por activo/inactivo

### 9.2 Crear Funcionario

1. Andá a **Ambulancias → Funcionarios → Crear**
2. Seleccioná el **rol**: superadmin, admin, médico, enfermero, técnico, recepcionista o farmacéutico
3. Completá los datos personales y profesionales
4. Hacé clic en **"Crear funcionario"**

### 9.3 Editar / Desactivar / Reactivar

El funcionamiento es igual al de conductores (sección 8.3 y 8.4).

---

## 10. Gestión de Rutas

> Solo administradores y super admins.

### 10.1 Ver Rutas

1. Andá a **Ambulancias → Rutas**
2. Se muestran todas las rutas predefinidas con:
   - Nombre
   - Origen y destino
   - Distancia en kilómetros
   - Descripción

### 10.2 Crear Ruta

1. Andá a **Ambulancias → Rutas → Crear**
2. Completá:
   - **Nombre** de la ruta (ej: "Clínicas → Sanatorio Español")
   - **Origen**: dirección o punto de partida
   - **Destino**: dirección o punto de llegada
   - **Distancia (km)**: distancia aproximada
   - **Descripción**: notas sobre la ruta
3. Hacé clic en **"Crear ruta"**

> Las rutas se usan para calcular automáticamente la **hora de llegada estimada** cuando se crea un traslado.

---

## 11. Gestión de Noticias

> Solo administradores y super admins.

### 11.1 Ver Noticias

1. Andá a **Gestión → Noticias**
2. Se muestran todas las noticias con título, contenido, imagen, autor y estado

### 11.2 Crear Noticia

1. Andá a **Gestión → Noticias → Crear**
2. Completá:
   - **Título**: nombre de la noticia
   - **Contenido**: texto completo de la noticia
   - **Imagen** (opcional): foto JPG, PNG, WebP o GIF (máximo 5 MB)
3. Hacé clic en **"Crear noticia"**

### 11.3 Activar / Desactivar Noticia

1. En la lista de noticias, hacé clic en el interruptor 🔀
2. Las noticias desactivadas no se muestran en la página pública

### 11.4 Eliminar Noticia

1. Hacé clic en el ícono de eliminar 🗑️
2. Confirmá la acción
3. La imagen asociada también se elimina del servidor

---

## 12. Mi Perfil

### 12.1 Ver y Editar Perfil

1. Andá a **Mi Perfil** en el menú lateral
2. Visualizá tu información actual
3. Para editar, hacé clic en **"Editar perfil"**
4. Modificá los campos que necesites:
   - Email
   - Teléfono
   - Contraseña (dejá vacío si no querés cambiarla)
5. Hacé clic en **"Guardar cambios"**

### 12.2 Cambiar Contraseña

1. En tu perfil, ingresá tu **contraseña actual**
2. Ingresá la **nueva contraseña** (mínimo 8 caracteres)
3. Repetí la **nueva contraseña**
4. Guardá los cambios

---

## 13. Acceso Público

### 13.1 Documentos Públicos (por QR)

Cualquier persona puede acceder a un documento médico escaneando su código QR:

1. Escaneá el código QR con la cámara del celular
2. Se abre el navegador con el documento
3. Podés ver el detalle y descargar el PDF
4. **No se requiere iniciar sesión**

### 13.2 Encuestas Públicas

Los pacientes pueden responder encuestas sin iniciar sesión:

1. Recibís un enlace por email o SMS
2. Abrí el enlace
3. Respondé las preguntas
4. Enviá las respuestas
5. **No se requiere cuenta**

### 13.3 Documentos del Paciente (por Token)

Los pacientes pueden acceder a sus documentos con un enlace único:

1. Recibís un enlace con un token único (ej: `/publico/mis-documentos?token=abc123`)
2. Abrí el enlace
3. Se muestran todos tus documentos asignados
4. **No se requiere iniciar sesión**

---

## 14. Seguridad y Buenas Prácticas

### 14.1 Contraseñas Seguras

- Usá **mínimo 8 caracteres**
- Combiná **letras, números y símbolos**
- **No uses** tu nombre, cédula o fecha de nacimiento
- **No compartas** tu contraseña con nadie
- **Cambiala** periódicamente

### 14.2 Sesión

- Tu sesión expira después de **30 minutos de inactividad**
- Si cerrás el navegador, la sesión se cierra automáticamente
- Si detectamos que cambiaste de navegador, la sesión se invalida por seguridad
- Cerrá sesión al terminar de usar el sistema (botón "Cerrar sesión" arriba a la derecha)

### 14.3 Acceso por Roles

Cada usuario solo puede ver y hacer lo que su rol le permite:

| Si sos... | Podés... | No podés... |
|-----------|----------|-------------|
| **Paciente** | Ver tus documentos, responder encuestas, ver tu perfil | Subir documentos, gestionar usuarios, ver traslados |
| **Conductor** | Ver y actualizar tus traslados, usar GPS tracking | Gestionar documentos, conductores, funcionarios |
| **Admin** | Gestionar todo: documentos, traslados, usuarios, encuestas | — |
| **Super Admin** | Todo lo del admin + configuración avanzada | — |

### 14.4 Protección de Datos

- Todos los datos se almacenan en servidores seguros
- Las contraseñas nunca se guardan en texto plano (se usan algoritmos de hash)
- Los documentos médicos están protegidos por permisos de acceso
- Cada acción queda registrada en un log de auditoría inmutable
- No se comparten datos con servicios de terceros

### 14.5 Lo que NO debés hacer

- **No compartas** tu usuario y contraseña
- **No accedas** desde computadoras públicas sin cerrar sesión
- **No hagas clic** en enlaces sospechosos de emails
- **No subas** documentos que no estén autorizados
- **No intentes** acceder a datos de otros pacientes

---

## 15. Preguntas Frecuentes

### ¿Olvidé mi contraseña?

Andá a la pantalla de login y hacé clic en **"¿Olvidaste tu contraseña?"**. Ingresá tu email y recibís un enlace para crear una nueva.

### No puedo iniciar sesión, dice "usuario desactivado"

Tu cuenta fue desactivada por un administrador. Contactá al administrador del sistema para que la reactive.

### No puedo subir un archivo

Verificá que:
- El archivo sea un **PDF**
- El tamaño no super los **10 MB**
- Tengas permisos de administrador

### El mapa no muestra mi ubicación

Verificá que:
- Tengas **GPS habilitado** en tu dispositivo
- Hayas dado **permiso de ubicación** al navegador
- Tengas **conexión a internet**

### No veo la sección de traslados

Depende de tu rol:
- **Pacientes**: solo ven la sección pero no ven datos (por privacidad)
- **Conductores y copilotos**: ven traslados y pueden actualizar estados
- **Admin y super admin**: ven todo

### ¿Cómo funciona el código QR?

Cada documento subido genera automáticamente un código QR que contiene un enlace único. Cualquier persona con acceso al QR puede ver el PDF del documento sin necesidad de cuenta. Útil para compartirla con otros profesionales de salud.

### ¿Puedo usar el sistema en el celular?

Sí. Elyra es **responsivo** y se adapta al tamaño de pantalla. Las funciones de GPS tracking están optimizadas para usar desde un celular.

### ¿Mis datos están seguros?

Sí. Elyra implementa múltiples capas de seguridad:
- Contraseñas encriptadas con Bcrypt
- Protección contra inyección SQL
- Protección contra ataques XSS y CSRF
- Auditoría inmutable de todas las acciones
- Sesiones seguras con timeout automático
- Headers de seguridad del navegador (CSP, HSTS, etc.)

### ¿Quién puede ver mis documentos médicos?

Solo:
- **Vos** (como paciente)
- **Administradores** del sistema
- **Personas con el enlace público** (QR o token)

Los demás roles (médicos, enfermeros, etc.) **no tienen acceso** a documentos médicos a menos que se les asigne explícitamente.

### ¿Cómo reporto un problema?

Contactá al administrador del sistema o enviá un email a soporte del Hospital de Clínicas.

---

## Información de Contacto

**Hospital de Clínicas — Montevideo, Uruguay**
**Sistema Elyra v1.0**
**Soporte técnico**: administrador del sistema

---

*Manual de usuario generado en Julio 2026.*
*Elyra — Sistema de Gestión Hospitalaria.*

---
---

# PARTE TÉCNICA

Las siguientes secciones describen la arquitectura, estructura y funcionamiento interno del sistema Elyra.

---

## 16. Arquitectura del Sistema

### 16.1 Arquitectura Hexagonal (Puertos y Adaptadores)

Elyra implementa la **arquitectura hexagonal**, también conocida como *Ports & Adapters*. Esta arquitectura separa la lógica de negocio del mundo exterior a través de interfaces (puertos) y adaptadores.

```
                    ┌─────────────────────────────────┐
                    │      ADAPTADORES DE ENTRADA      │
                    │   (Controladores, CLI, APIs)     │
                    └──────────────┬──────────────────┘
                                   │
                    ┌──────────────▼──────────────────┐
                    │          PUERTOS DE ENTRADA       │
                    │    (Interfaces de Casos de Uso)   │
                    └──────────────┬──────────────────┘
                                   │
        ┌──────────────────────────▼──────────────────────────┐
        │                CAPA DE APLICACIÓN                    │
        │            (Casos de Uso / Use Cases)                │
        │  Auth │ Documento │ Traslado │ Encuesta │ Ubicacion │
        └──────────────────────────┬──────────────────────────┘
                                   │
                    ┌──────────────▼──────────────────┐
                    │       DOMINIO (CORE)              │
        │  Entidades │ Value Objects │ Repositorios    │
        └──────────────────────────┬──────────────────┘
                                   │
                    ┌──────────────▼──────────────────┐
                    │      PUERTOS DE SALIDA            │
                    │   (Interfaces de Repositorios)    │
                    └──────────────┬──────────────────┘
                                   │
                    ┌──────────────▼──────────────────┐
                    │      ADAPTADORES DE SALIDA        │
                    │  (MySQL, Email, QR, OSRM, GPS)    │
                    └─────────────────────────────────┘
```

**La regla fundamental**: el dominio y la aplicación **nunca** dependen de infraestructura. Las dependencias van de afuera hacia adentro.

### 16.2 Las Tres Capas

#### Capa de Dominio (`src/Domain/`)

Es el núcleo del sistema. Contiene:

- **Entidades**: objetos con identidad y comportamiento (ej: `Traslado`, `Funcionario`, `Paciente`)
- **Value Objects**: objetos inmutables y autovalidados (ej: `Email`, `EstadoTraslado`, `Coordenada`)
- **Interfaces de Repositorio**: contratos que definen qué operaciones de persistencia existen, sin importar cómo se implementan

Ejemplo de una entidad con máquina de estados:
```
Traslado → actualizarEstado(nuevoEstado)
  Valida transiciones permitidas usando EstadoTraslado
  Registra HistorialEstado si la transición es válida
```

#### Capa de Aplicación (`src/Application/`)

Orquesta los casos de uso. Cada caso de uso:

1. Recibe un DTO de entrada (request)
2. Usa entidades del dominio
3. Llama a repositorios (a través de interfaces)
4. Devuelve un DTO de salida (response)

```
RegistrarTrasladoUseCase::execute(RegistrarTrasladoRequest):
  1. Valida conductor (existe, activo, rol válido)
  2. Valida vehiculo (existe)
  3. Crea entidad Traslado (con máquina de estados)
  4. Crea entidad ElementoTraslado
  5. Graba en repositorio (dentro de transacción)
  6. Retorna RegistrarTrasladoResponse con ID y código
```

#### Capa de Infraestructura (`src/Infrastructure/`)

Implementa los adaptadores:

- **Persistencia**: repositorios MySQL que implementan las interfaces del dominio
- **Web**: controladores HTTP, middleware, router
- **Servicios**: sesión, email, QR, rate limiting, logging, GPS

### 16.3 Flujo de una Petición HTTP

```
1. Navegador → GET /traslados
2. public/index.php (Bootstrap)
   → Headers de seguridad (CSP, HSTS, X-Frame-Options)
   → ErrorHandler global
   → SessionManager::start()
3. Router.php (de web.php)
   → Coincide patrón: /traslados → TrasladoController::index()
   → Ejecuta CsrfMiddleware (solo POST/PUT/DELETE)
4. TrasladoController::index()
   → BaseController::requireRole(['admin', ...])
   → SessionManager::requireAuth()
   → Crea ListarTrasladosUseCase
   → Ejecuta use case
   → Extrae datos del response
   → render('traslados/index', $data)
5. views/traslados/index.php
   → HTML con Bootstrap 5 + variables PHP
6. Respuesta HTTP al navegador
```

---

## 17. Estructura de Código

### 17.1 Árbol de Directorios

```
src/
├── Domain/                    # Núcleo del negocio
│   ├── Entity/               # 15 entidades
│   │   ├── Traslado.php      # Con máquina de estados
│   │   ├── Funcionario.php   # Staff con rol
│   │   ├── Paciente.php      # Con token QR
│   │   ├── Documento.php     # Con código QR
│   │   └── ...
│   ├── ValueObject/           # 10 value objects
│   │   ├── EstadoTraslado.php # FSM: pendiente→en_curso→...
│   │   ├── Coordenada.php    # GPS con Haversine
│   │   ├── Email.php         # Validación automática
│   │   ├── RolUsuario.php    # Enum de 9 roles
│   │   └── ...
│   └── Repository/            # 11 interfaces
│       ├── TrasladoRepositoryInterface.php
│       ├── ConductorRepositoryInterface.php
│       └── ...
│
├── Application/               # Casos de uso
│   └── UseCases/
│       ├── Auth/             # 7 casos: login, registro, CRUD funcionarios
│       ├── Documento/        # 5 casos: subir, listar, ver, editar, eliminar
│       ├── Traslado/         # 5 casos: crear, listar, ver, actualizar estado, historial
│       ├── Encuesta/         # 4 casos: crear, publicar, responder, resultados
│       ├── Conductor/        # 3 casos: crear, actualizar, listar
│       ├── Ruta/             # 3 casos: crear, actualizar, listar
│       └── Ubicacion/        # 3 casos: registrar GPS, obtener activas, historial ruta
│
└── Infrastructure/            # Adaptadores
    ├── Persistence/MySQL/     # 12 repositorios MySQL
    ├── Service/               # 12 servicios
    │   ├── SessionManager.php
    │   ├── AuthService.php
    │   ├── AuditLogger.php
    │   ├── RateLimiter.php
    │   ├── Validator.php
    │   ├── FileStorageService.php
    │   ├── QRGeneratorService.php
    │   ├── RouteCacheService.php
    │   ├── LocationBroadcaster.php
    │   └── ...
    └── Web/
        ├── Controller/        # 14 controladores
        ├── Middleware/        # CSRF + Honeypot
        ├── Router.php         # Router personalizado
        └── Routes/web.php     # 94 rutas
```

### 17.2 Convenciones de Código

- **PHP 8.5** con `declare(strict_types=1);` en todos los archivos
- **PSR-4**: namespace `Elyra\` mapeado a `src/`
- **PHPStan nivel 9**: verificación estricta de tipos
- **Sin framework MVC**: routing y controladores custom
- **Vistas PHP**: templates en `views/` con extracción via `extract($data, EXTR_SKIP)`
- **Frontend vanilla**: Bootstrap 5 + JavaScript sin framework (React, Vue, etc.)

---

## 18. Base de Datos

### 18.1 Diagrama de Tablas Principales

```
┌─────────────┐     ┌──────────────┐     ┌─────────────────┐
│   usuario    │────▶│  funcionario  │     │   categoria     │
│  (base)      │     │  (con rol)    │     │  (documentos)   │
└──────┬──────┘     └──────────────┘     └────────┬────────┘
       │                                           │
       ▼                                           ▼
┌──────────────┐                         ┌─────────────────┐
│   paciente   │                         │    documento     │
│  (con token) │◀─── paciente_id ───────│  (con QR)        │
└──────────────┘                         └─────────────────┘
                                                    │
                                              codigo_qr_id
                                                    │
                                                    ▼
                                           ┌─────────────────┐
                                           │   codigo_qr      │
                                           └─────────────────┘

┌──────────────┐     ┌──────────────┐     ┌─────────────────┐
│  vehiculo    │◀──┐ │    ruta      │◀──┐ │   conductor     │
│  (ambulancia)│   │ │  (origen/    │   │ │  (FK usuario)   │
└──────────────┘   │ │   destino)   │   │ └────────┬────────┘
                   │ └──────────────┘   │          │
                   │                    │          ▼
                   │         ┌──────────▼──────────────────┐
                   │         │          traslado             │
                   └────────▶│  (código, estado, GPS coords)│
                             └──────────┬──────────────────┘
                                        │
                              ┌─────────┴─────────┐
                              ▼                   ▼
                    ┌─────────────────┐  ┌──────────────────┐
                    │elemento_traslado│  │ historial_estado   │
                    │(paciente/organo │  │ (audit trail)      │
                    │ /equip/insumo)  │  │                    │
                    └─────────────────┘  └──────────────────┘

┌─────────────────────┐  ┌──────────────────────┐
│ ubicacion_conductor  │  │ historial_ubicacion    │
│ (posición actual,    │  │ (breadcrumb trail,     │
│  1 fila por driver)  │  │  append-only)          │
└─────────────────────┘  └──────────────────────┘

┌─────────────────────┐
│     audit_log        │
│ (inmutable, no se    │
│  puede UPDATE/DELETE)│
└─────────────────────┘
```

### 18.2 Módulos de Tablas

| Módulo | Tablas | Propósito |
|--------|--------|-----------|
| **Identidad** | `usuario`, `funcionario`, `paciente` | Usuarios con herencia STI (Single Table Inheritance) |
| **Documentos** | `documento`, `categoria`, `codigo_qr` | Gestión documental con códigos QR |
| **Encuestas** | `encuesta`, `pregunta`, `respuesta` | Encuestas de satisfacción |
| **Traslados** | `traslado`, `elemento_traslado`, `historial_estado`, `vehiculo`, `ruta` | Ambulancias y traslados |
| **Catálogo** | `catalogo_elemento` | Elementos transportables (insumos, equipamiento, órganos) |
| **GPS** | `ubicacion_conductor`, `historial_ubicacion` | Tracking en tiempo real |
| **Auditoría** | `audit_log` | Registro inmutable de acciones |

### 18.3 Tabla de Traslados (Detalle)

La tabla `traslado` es la más importante del sistema:

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT PK | Identificador único |
| `codigo` | VARCHAR(50) UNIQUE | Código legible (ej: TR-001) |
| `conductor_id` | INT FK | Conductor asignado |
| `copiloto_id` | INT FK NULL | Copiloto (opcional) |
| `vehiculo_id` | INT FK | Ambulancia |
| `ruta_id` | INT FK NULL | Ruta predefinida |
| `origen` | VARCHAR(255) | Nombre del punto de origen |
| `origen_lat` | DECIMAL(10,7) | Latitud del origen |
| `origen_lng` | DECIMAL(10,7) | Longitud del origen |
| `destino` | VARCHAR(255) | Nombre del destino |
| `destino_lat` | DECIMAL(10,7) | Latitud del destino |
| `destino_lng` | DECIMAL(10,7) | Longitud del destino |
| `estado` | ENUM | pendiente, en_curso, en_destino, en_retorno, completado, cancelado |
| `motivo_cancelacion` | TEXT NULL | Motivo si fue cancelado |
| `hora_salida_estimada` | DATETIME | Salida programada |
| `hora_salida_efectiva` | DATETIME NULL | Salida real |
| `hora_llegada_destino` | DATETIME NULL | Llegada al destino |
| `hora_inicio_retorno` | DATETIME NULL | Inicio del retorno |
| `hora_llegada_hospital` | DATETIME NULL | Llegada al hospital |
| `registrado_por` | INT FK | Quién creó el traslado |
| `observaciones` | TEXT | Notas |

---

## 19. Sistema GPS y Tracking en Tiempo Real

### 19.1 Flujo del Tracking GPS

```
┌──────────────┐    ┌───────────────┐    ┌──────────────┐
│  Navegador    │───▶│  API REST     │───▶│   MySQL      │
│  (Celular     │    │  POST         │    │  (upsert +   │
│   Conductor)  │    │  /api/ubicacion│   │   insert)    │
└──────────────┘    └───────┬───────┘    └──────────────┘
                            │
                            ▼
                    ┌───────────────┐    ┌──────────────┐
                    │  Location     │───▶│  Navegadores  │
                    │  Broadcaster  │    │  (Admin Map)  │
                    │  (SSE)        │    │  Leaflet.js   │
                    └───────────────┘    └──────────────┘
```

**Paso 1 — Captura GPS (navegador del conductor)**:
- `navigator.geolocation.watchPosition()` obtiene posición cada 5 segundos
- JavaScript envía POST a `/api/ubicacion` con `{latitud, longitud, heading, velocidad}`
- Se incluye token CSRF en header `X-CSRF-Token`

**Paso 2 — Registro en servidor**:
- `UbicacionController::registrar()` valida autenticación (conductor/admin/superadmin)
- Rate limiting: 60 requests por minuto por usuario
- `RegistrarUbicacionUseCase` ejecuta:
  1. Valida coordenadas (rango válido para Uruguay)
  2. Auto-descubre `traslado_id` activo si no se proporciona
  3. Crea entidad `UbicacionConductor`

**Paso 3 — Persistencia MySQL**:
- `ubicacion_conductor`: `INSERT ... ON DUPLICATE KEY UPDATE` (una fila por conductor)
- `historial_ubicacion`: `INSERT` (append-only, nunca se borra)

**Paso 4 — Difusión en tiempo real**:
- `LocationBroadcaster` usa Server-Sent Events (SSE)
- Archivos en `/tmp/elyra_sse/` como pub/sub
- Máximo 50 listeners concurrentes
- Los mapas se actualizan cada 5 segundos

### 19.2 Mapa Administrativo (Leaflet.js)

**Tecnologías**:
- **Leaflet.js**: librería de mapas interactivos
- **OpenStreetMap**: proveedor de tiles (mapas de fondo)
- **OSRM** (Open Source Routing Machine): cálculo de rutas reales por calles

**Funcionalidades**:
- Marcadores de hospital (rosa), ambulancia (coloreado por estado), origen (verde), destino (rojo)
- Rutas reales por calles (no líneas rectas) vía OSRM
- Sidebar con lista de traslados activos
- Indicador de GPS activo por conductor
- Auto-refresh cada 5 segundos
- Pop-ups con detalles al hacer clic en marcadores

**Caché de rutas**:
- Las rutas OSRM se guardan en `storage/route-cache/`
- TTL de 30 días
- Reduce llamadas a la API externa

### 19.3 Coordenadas GPS

El Value Object `Coordenada` valida:
- Latitud: -90 a +90
- Longitud: -180 a +180
- Precisión: 7 decimales (~1 cm)

Método `distanciaHaversine()`: calcula distancia en kilómetros entre dos coordenadas usando la fórmula de Haversine.

---

## 20. Sistema de Seguridad

### 20.1 Autenticación

| Componente | Implementación |
|------------|----------------|
| **Contraseñas** | Bcrypt con cost factor 12, auto-rehash si el factor cambia |
| **Rate Limiting Login** | 5 intentos por 15 minutos (por IP + por usuario) |
| **Bloqueo de Cuenta** | 5 intentos fallidos → bloqueo 15 minutos |
| **Sesiones** | `use_strict_mode=1`, `use_only_cookies=1`, `use_trans_sid=0` |
| **Regeneración de ID** | `session_regenerate_id(true)` al iniciar sesión |
| **User-Agent Binding** | Sesión válida solo en el mismo navegador |
| **Timeout** | 30 minutos de inactividad |
| **CSRF** | Token por sesión + rotación post-login |
| **Password Reset** | Token de un solo uso, expira en 1 hora |

### 20.2 Autorización (RBAC)

Roles implementados via `RolUsuario` Value Object:

| Rol | Permisos principales |
|-----|---------------------|
| `superadmin` | Acceso total, configuración avanzada |
| `admin` | Gestión de usuarios, documentos, traslados, encuestas |
| `conductor` | Traslados asignados, GPS tracking |
| `copiloto` | Traslados asignados, GPS tracking |
| `medico` | Dashboard, perfil |
| `enfermero` | Dashboard, perfil |
| `tecnico` | Dashboard, perfil |
| `recepcionista` | Dashboard, perfil |
| `farmaceutico` | Dashboard, perfil |

Control de acceso: `BaseController::requireRole()` valida el rol antes de ejecutar cualquier acción.

### 20.3 Protección contra Ataques

| Ataque | Mitigación |
|--------|-----------|
| **Inyección SQL** | Prepared statements (PDO) en todos los repositorios |
| **XSS (Cross-Site Scripting)** | `htmlspecialchars()` en vistas, `escapeHtml()` en JS, `json_encode` con flags HEX |
| **CSRF (Cross-Site Request Forgery)** | Token CSRF en todos los forms + header `X-CSRF-Token` para AJAX |
| **Clickjacking** | `X-Frame-Options: SAMEORIGIN` |
| **MIME Sniffing** | `X-Content-Type-Options: nosniff` |
| **HSTS** | `Strict-Transport-Security: max-age=31536000; includeSubDomains` |
| **Open Redirect** | Eliminado de `CsrfMiddleware` |
| **Header Injection** | `Content-Disposition` filenames sanitizados |
| **LIKE Wildcard Injection** | `addcslashes($input, '%_')` en búsquedas |
| **Bot Automation** | Honeypot field (`website` hidden) |
| **Brute Force** | Rate limiting con ventanas de tiempo |
| **File Upload** | Whitelist de extensiones + validación MIME + 10MB límite |

### 20.4 Headers de Seguridad HTTP

Todos los responses incluyen:

```
Content-Security-Policy: script-src 'self' 'nonce-{random}'
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), geolocation=(), ...
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

### 20.5 Auditoría Inmutable

El sistema registra **todas** las acciones en la tabla `audit_log`:

- Login, logout, intentos fallidos
- CRUD de documentos, traslados, funcionarios, conductores, rutas, encuestas, noticias
- Cambios de estado de traslados
- Intentos de acceso denegado

La tabla es **inmutable**: no existe código que ejecute `UPDATE` o `DELETE` sobre ella. Cada registro incluye:
- Fecha/hora
- Usuario (ID, tipo, nombre)
- IP del cliente
- User-Agent
- Acción
- Entidad afectada (tipo + ID)
- Detalles en JSON

---

## 21. Servicios de Infraestructura

| Servicio | Archivo | Función |
|----------|---------|---------|
| **SessionManager** | `SessionManager.php` | Ciclo de vida de sesión: start/regenerate/destroy, timeout 30min, user-agent binding, tracking multi-sesión |
| **AuthService** | `AuthService.php` | Login: rate limiting (5/15min), bcrypt verification, auto-rehash |
| **AuditLogger** | `AuditLogger.php` | Log inmutable: login, logout, CRUD, state changes, access denied |
| **RateLimiter** | `RateLimiter.php` | Rate limiting basado en archivos con `flock()`: login, GPS, uploads, registro, encuestas |
| **Validator** | `Validator.php` | Validación fluente: `required`, `email`, `minLength`, `inArray`, `fileSize`, `fileMime` |
| **FileStorageService** | `FileStorageService.php` | Upload seguro: whitelist extensiones, MIME validation, nombres aleatorios, 10MB |
| **QRGeneratorService** | `QRGeneratorService.php` | Generación de QR vía API externa + fallback GD |
| **RouteCacheService** | `RouteCacheService.php` | API OSRM con caché de 30 días en `storage/route-cache/` |
| **LocationBroadcaster** | `LocationBroadcaster.php` | SSE (Server-Sent Events) para GPS en tiempo real, max 50 listeners |
| **ErrorHandler** | `ErrorHandler.php` | Manejador global de excepciones, logs en `storage/logs/` |
| **EmailService** | `PhpMailEmailService.php` | Envío de emails vía PHPMailer + Gmail SMTP |

---

## 22. Frontend (JavaScript)

### 22.1 Archivos JS Principales

| Archivo | Función |
|---------|---------|
| `elyra.js` | Core: inyección automática de CSRF token en forms, wrapper `Elyra.fetch()` con header `X-CSRF-Token`, filtro de inputs numéricos |
| `components/ui.js` | UI: toast notifications, modales, visor QR (lazy-load qrcodejs), drag-and-drop de PDFs con barra de progreso, preview de documentos |
| `tracking-conductor.js` | GPS del conductor: `watchPosition()`, envío cada 5s, toggle start/stop, manejo de errores de permiso |
| `mapa-traslados.js` | Mapa admin: Leaflet + OpenStreetMap, marcadores coloreados, rutas OSRM, sidebar, auto-refresh 5s |
| `nuevo-traslado.js` | Formulario de traslado: campos dinámicos, filtrado de catálogo, cálculo automático de ETA |

### 22.2 Dependencias Externas (CDN)

- **Bootstrap 5**: CSS + JS (layout, componentes)
- **Leaflet.js**: mapas interactivos
- **QRCode.js**: generación de QR en el navegador
- **Font Awesome**: iconos

---

## 23. API REST

### 23.1 Endpoints GPS

| Método | Ruta | Descripción | Auth |
|--------|------|-------------|------|
| `POST` | `/api/ubicacion` | Registrar posición GPS | Conductor/Admin |
| `GET` | `/api/ubicaciones/activas` | Obtener posiciones activas | Auth |
| `GET` | `/api/ubicaciones/historial` | Historial de ruta | Auth |
| `GET` | `/api/ubicaciones/stream` | Stream SSE en tiempo real | Auth |
| `GET` | `/api/traslados/activos` | Traslados con estado | Auth |

### 23.2 Endpoints de Datos

| Método | Ruta | Descripción | Auth |
|--------|------|-------------|------|
| `GET` | `/api/catalogo` | Catálogo de elementos | Admin |
| `GET` | `/api/pacientes` | Lista de pacientes | Admin |
| `GET` | `/api/copilotos` | Lista de copilotos | Admin |
| `GET` | `/api/rutas-info` | Información de rutas | Admin |
| `GET` | `/api/ruta/real` | Ruta OSRM real por calles | Auth |

### 23.3 Formato de Respuesta GPS

```json
{
  "id": 1,
  "conductor_id": 5,
  "traslado_id": 12,
  "latitud": -34.9011,
  "longitud": -56.1645,
  "heading": 180,
  "velocidad": 45.2,
  "updated_at": "2026-07-12 14:30:00"
}
```

---

## 24. Flujo Completo de un Traslado

```
1. ADMIN crea traslado
   → TrasladoController::nuevo()
   → RegistrarTrasladoUseCase
   → Se valida conductor, vehiculo, ruta
   → Se crea Traslado con estado PENDIENTE
   → Se crea ElementoTraslado (paciente/organo/equipo/insumo)
   → Se calcula ETA si hay ruta con distancia
   → Código TR-XXX generado automáticamente

2. CONDUCTOR inicia traslado
   → Actualiza estado: PENDIENTE → EN CURSO
   → GPS tracking activado automáticamente
   → UbicacionController recibe posiciones cada 5s
   → Mapa admin muestra ambulancia moviéndose

3. AMBULANCIA llega al destino
   → Actualiza estado: EN CURSO → EN DESTINO
   → Se registra hora_llegada_destino

4. AMBULANCIA regresa
   → Actualiza estado: EN DESTINO → EN RETORNO
   → Se registra hora_inicio_retorno

5. AMBULANCIA llega al hospital
   → Actualiza estado: EN RETORNO → COMPLETADO
   → Se registra hora_llegada_hospital
   → GPS tracking se desactiva
   → Traslado aparece en historial

HISTORIAL:
   Cada cambio de estado genera un registro en historial_estado
   con estado_anterior, estado_nuevo, observación, usuario, fecha
```

---

## 25. Despliegue y Configuración

### 25.1 Variables de Entorno (`.env`)

```env
APP_NAME=Elyra
APP_URL=http://localhost:8000
APP_ENV=development
APP_DEBUG=false

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=elyra
DB_USERNAME=root
DB_PASSWORD=secret

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=lainsmes@gmail.com
SMTP_PASS=app_password
```

### 25.2 Requisitos del Servidor

- PHP 8.5+ con extensiones: pdo_mysql, json, mbstring, gd, fileinfo
- MySQL 8.0+
- Composer
- Node.js (para ESLint/Stylelint, opcional)

### 25.3 Comandos de Desarrollo

```bash
# Instalar dependencias
composer install

# Servidor de desarrollo
php -S 0.0.0.0:8000 -t public/

# Análisis estático (0 errores, nivel 9)
composer phpstan

# Tests
composer test
# PHPUnit: 241 tests, 441 assertions

# Linting JS
npx eslint public/js/

# Linting CSS
npx stylelint "**/*.css"
```

### 25.4 Archivos de Runtime

```
storage/
├── logs/           # Logs diarios (YYYY-MM-DD.log)
├── sessions/       # Archivos de sesión PHP
├── uploads/        # Documentos PDF subidos
│   └── documentos/
└── route-cache/    # Caché de rutas OSRM (30 días)
```

---

*Documento generado en Julio 2026.*
*Elyra — Sistema de Gestión Hospitalaria — Hospital de Clínicas, Montevideo, Uruguay.*
