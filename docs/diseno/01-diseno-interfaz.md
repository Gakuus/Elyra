# 12 - Diseño de la Solución — Diseño de Interfaz

## Arquitectura de Navegación

El sistema utiliza un **layout de sidebar persistente + topbar** para todas las pantallas internas (autenticadas). La vista pública (paciente vía QR) usa un layout simple y mobile-first.

```
┌──────────────────────────────────────────────────────────────────┐
│                        PANEL PRINCIPAL                           │
│                (Sistema Centralizado del Hospital)                │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────────────┐    ┌──────────────────────┐            │
│  │    Documentación     │    │      Ambulancias      │            │
│  │      Pacientes       │    │                       │            │
│  └──────────────────────┘    └──────────────────────┘            │
│                                                                  │
│  (Módulos existentes del hospital — acceso mediante SSO)         │
└──────────────────────────────────────────────────────────────────┘
```

El sistema se integra al panel principal del hospital mediante dos accesos directos. Una vez autenticado, el usuario ingresa al layout interno con sidebar.

---

## Layout General (Dashboard Interno)

```
┌───────────┬──────────────────────────────────────────────────────┐
│           │  Hospital de Clínicas        [👤 Admin] [🚪 Salir]    │
│           ├──────────────────────────────────────────────────────┤
│  📋 Docs  │  ◀ Documentos                                        │
│  📊 Enc.  │                                                      │
│  🚑 Amb.  │  [+ Subir documento]  [Categoría: ▼]  [🔍 Buscar]   │
│  👥 Cond. │                                                      │
│  🗺 Rutas │  ┌────┬──────────┬────────────┬──────────┬────────┐  │
│           │  │ QR │ Título   │ Categoría  │ Subido   │ Acción │  │
│           │  ├────┼──────────┼────────────┼──────────┼────────┤  │
│           │  │ 👁 │ Indicac. │ Cardiología│ 15/05/26 │ ✏️ 🗑 📋│  │
│           │  │ 👁 │ Prep.    │ Imagenol.  │ 14/05/26 │ ✏️ 🗑 📋│  │
│           │  └────┴──────────┴────────────┴──────────┴────────┘  │
│           │                                                      │
│           │  [📊 Ir a Encuestas]                                 │
│           ├──────────────────────────────────────────────────────┤
│           │  © 2026 Hospital de Clínicas - Elyra v1.0            │
└───────────┴──────────────────────────────────────────────────────┘
```

**Estructura del layout:**
- **Sidebar** (250px): fija a la izquierda, con el logo "Elyra", divider, y enlaces de navegación con iconos. En mobile se oculta y se muestra como overlay.
- **Topbar**: pegajosa (sticky), muestra el nombre del hospital, usuario autenticado con dropdown, y botón de cerrar sesión.
- **Page content**: área principal con breadcrumb, título de página y contenido.
- **Footer**: barra simple con copyright y versión.

---

## Pantallas — Módulo de Documentación

### 1. Login

```
┌──────────────────────────────────────────────────┐
│                                                  │
│                    ┌──────────────┐               │
│                    │   🏥         │               │
│                    │   Elyra      │               │
│                    └──────────────┘               │
│                                                  │
│            Hospital de Clínicas                  │
│         Sistema de Gestión Hospitalaria           │
│                                                  │
│   ┌──────────────────────────────────────────┐   │
│   │  Usuario                                 │   │
│   │  ┌────────────────────────────────────┐  │   │
│   │  │  admin                             │  │   │
│   │  └────────────────────────────────────┘  │   │
│   │                                          │   │
│   │  Contraseña                              │   │
│   │  ┌────────────────────────────────────┐  │   │
│   │  │  ********                          │  │   │
│   │  └────────────────────────────────────┘  │   │
│   │                                          │   │
│   │  ┌────────────────────────────────────┐  │   │
│   │  │         INICIAR SESIÓN             │  │   │
│   │  └────────────────────────────────────┘  │   │
│   └──────────────────────────────────────────┘   │
│                                                  │
│   ⚠️ Credenciales inválidas (emergencia)         │
│                                                  │
└──────────────────────────────────────────────────┘
```

**Especificaciones:**
- **Fondo**: gradiente lineal de azul oscuro (#0a5ed7) a azul petróleo (#1a1f36).
- **Tarjeta**: centrada, max 420px, bordes redondeados (16px), sombra pronunciada.
- **Logo**: ícono cuadrado (64×64) con fondo azul y texto "Elyra" en blanco, centrado.
- **Campos**: ancho completo, con etiqueta flotante o superior.
- **Botón**: primary, ancho completo, con texto "Iniciar Sesión".
- **Error**: alerta roja con icono de advertencia, solo visible cuando hay error de autenticación.

**Estados:**
| Estado | Comportamiento |
|---|---|
| Normal | Logo, campos vacíos, botón habilitado |
| Cargando | Botón deshabilitado con spinner |
| Error | Alerta roja arriba del formulario, campos preservan valores |
| Validación | Campos con `required`, mensaje nativo del navegador si están vacíos |

### 2. Dashboard de Documentación

```
┌───────────┬──────────────────────────────────────────────────────────────┐
│           │  🏥 Hospital de Clínicas          👤 Administrador  🚪       │
│  ───────  ├──────────────────────────────────────────────────────────────┤
│  📋 Docs  │  Documentos > Listado                                        │
│  📊 Enc.  │                                                              │
│  🚑 Amb.  │  [+ Subir]  [Categoría: ▼ Todas]  [🔍 Buscar título...]     │
│  👥 Cond. │                                                              │
│  🗺 Rutas │  ┌────┬──────────────┬─────────────┬──────────┬────────────┐ │
│           │  │ QR │ Título       │ Categoría   │ Subido   │ Acciones   │ │
│           │  ├────┼──────────────┼─────────────┼──────────┼────────────┤ │
│           │  │ 👁 │ Indicaciones │ Cardiología │ 15/05/26 │ ✏️ 🗑 📋  │ │
│           │  │ 👁 │ Preparación  │ Imagenología│ 14/05/26 │ ✏️ 🗑 📋  │ │
│           │  │ 👁 │ Plan Nutric. │ Nefrología  │ 12/05/26 │ ✏️ 🗑 📋  │ │
│           │  │ 👁 │ Cuidados     │ Cardiología │ 10/05/26 │ ✏️ 🗑 📋  │ │
│           │  │ 👁 │ Guía         │ Ginecología │ 08/05/26 │ ✏️ 🗑 📋  │ │
│           │  └────┴──────────────┴─────────────┴──────────┴────────────┘ │
│           │                                                              │
│           │  Mostrando 5 de 12 documentos                   [📊 Encuestas]│
│           │                                                              │
│           │  < Anterior  1 2 3  Siguiente >                              │
├───────────┴──────────────────────────────────────────────────────────────┤
│  © 2026 Hospital de Clínicas — Elyra v1.0                                │
└──────────────────────────────────────────────────────────────────────────┘
```

**Componentes:**
| Componente | Descripción |
|---|---|
| **Sidebar** | Navegación principal con iconos. Ítem activo resaltado en azul. |
| **Breadcrumb** | "Documentos > Listado" con enlaces clicables. |
| **Action bar** | Botón primario "Subir", dropdown de categorías, input de búsqueda con icono de lupa. |
| **Tabla** | Columnas: QR (icono 👁 que abre modal), Título, Categoría (badge), Fecha de subida, Acciones. |
| **Acciones** | ✏️ Editar (link), 🗑 Eliminar (con confirmación), 📋 Copiar link público. |
| **Paginación** | Navegación inferior con números de página. |
| **Empty state** | Si no hay documentos: ilustración + mensaje "Aún no hay documentos subidos" + botón "Subir primer documento". |
| **Loading state** | Esqueleto de tabla con 3-4 filas de carga animadas. |

**Interacciones:**
- Al hacer clic en 👁 se abre un modal con el QR del documento.
- Al hacer clic en 📋 se copia automáticamente el enlace al portapapeles con feedback visual ("¡Copiado!").
- Al hacer clic en 🗑 aparece un confirm dialog: "¿Eliminar documento X? Esta acción no se puede deshacer."
- El filtro de categoría filtra la tabla en tiempo real (JS del lado cliente).
- La búsqueda busca en título y descripción con debounce de 300ms.

### 3. Formulario de Subir Documento

```
┌───────────┬───────────────────────────────────────────────────────────────┐
│           │  🏥 Hospital de Clínicas          👤 Administrador  🚪        │
│  ───────  ├───────────────────────────────────────────────────────────────┤
│  📋 Docs  │  Documentos > Subir documento                                │
│  📊 Enc.  │                                                               │
│  🚑 Amb.  │  ┌──────────────────────────────────────────────────────┐    │
│  👥 Cond. │  │  📄 Subir nuevo documento                           │    │
│  🗺 Rutas │  │                                                      │    │
│           │  │  Título del documento                                │    │
│           │  │  ┌──────────────────────────────────────────────┐   │    │
│           │  │  │ Indicaciones pre-operatorias                  │   │    │
│           │  │  └──────────────────────────────────────────────┘   │    │
│           │  │                                                      │    │
│           │  │  Categoría                                          │    │
│           │  │  ┌──────────────────────────────────────────────┐   │    │
│           │  │  │ Cardiología                          ▼        │   │    │
│           │  │  └──────────────────────────────────────────────┘   │    │
│           │  │                                                      │    │
│           │  │  Archivo PDF                                        │    │
│           │  │  ┌──────────────────────────────────────────────┐   │    │
│           │  │  │                                                │   │    │
│           │  │  │  📎 Arrastra tu PDF aquí o haz clic            │   │    │
│           │  │  │  (máx. 10 MB — solo PDF)                       │   │    │
│           │  │  │                                                │   │    │
│           │  │  └──────────────────────────────────────────────┘   │    │
│           │  │                                                      │    │
│           │  │  Descripción (opcional)                             │    │
│           │  │  ┌──────────────────────────────────────────────┐   │    │
│           │  │  │ Instrucciones detalladas para pacientes       │   │    │
│           │  │  │ que serán sometidos a cirugía cardíaca...     │   │    │
│           │  │  └──────────────────────────────────────────────┘   │    │
│           │  │                                                      │    │
│           │  │  [📤 Subir documento]    [← Cancelar]                │    │
│           │  └──────────────────────────────────────────────────────┘    │
│           │                                                               │
├───────────┴───────────────────────────────────────────────────────────────┤
│  © 2026 Hospital de Clínicas — Elyra v1.0                                 │
└───────────────────────────────────────────────────────────────────────────┘
```

**Especificaciones del formulario:**

| Campo | Tipo | Requerido | Validación |
|---|---|---|---|
| Título | Texto | Sí | Mín. 3 caracteres, máx. 200 |
| Categoría | Select | Sí | Debe seleccionar una opción |
| Archivo | File (drag & drop) | Sí | Solo PDF, máx. 10 MB |
| Descripción | Textarea | No | Máx. 500 caracteres |

**Zona de upload (drag & drop):**
- **Normal**: borde punteado gris con icono y texto.
- **Hover**: borde azul sólido, fondo azul claro.
- **Archivo seleccionado**: muestra nombre del archivo + tamaño + icono de check verde.
- **Error**: borde rojo + mensaje "Archivo demasiado grande. Máximo 10 MB" o "Formato no permitido. Solo PDF."
- **Arrastrando**: el área se ilumina con un overlay semitransparente.

**Interacciones:**
- Al enviar, el botón cambia a "Subiendo..." con spinner y se deshabilita.
- Si hay error del servidor, se muestra alerta roja en la parte superior.
- Al cancelar, redirige al dashboard de documentos.

### 4. Detalle del Documento (vista Administrativo)

```
┌───────────┬───────────────────────────────────────────────────────────────────┐
│           │  🏥 Hospital de Clínicas              👤 Administrador  🚪         │
│  ───────  ├───────────────────────────────────────────────────────────────────┤
│  📋 Docs  │  Documentos > Detalle                                            │
│  📊 Enc.  │                                                                   │
│  🚑 Amb.  │  ┌────────────────────────────────────────────────────────────┐  │
│  👥 Cond. │  │  📄 Indicaciones pre-operatorias                          │  │
│  🗺 Rutas │  │  Cardiología  •  Subido: 15/05/2026  •  👁 245 vistas      │  │
│           │  ├────────────────────────────────────────────────────────────┤  │
│           │  │                                                            │  │
│           │  │  ┌──────────────┐   ┌────────────────────────────────┐    │  │
│           │  │  │  ┌────────┐  │   │  Link público del documento    │    │  │
│           │  │  │  │ ██ ██  │  │   │  https://hc.edu.uy/doc/a1b2c3  │    │  │
│           │  │  │  │ ██ ██  │  │   │                                │    │  │
│           │  │  │  │ ██ ██  │  │   │  [📋 Copiar enlace]            │    │  │
│           │  │  │  └────────┘  │   │  [🖨️ Imprimir QR]              │    │  │
│           │  │  │  Código QR   │   └────────────────────────────────┘    │  │
│           │  │  └──────────────┘                                         │  │
│           │  │                                                            │  │
│           │  │  Descripción:                                             │  │
│           │  │  Instrucciones detalladas para pacientes que serán        │  │
│           │  │  sometidos a cirugía cardíaca en el Hospital de Clínicas. │  │
│           │  │                                                            │  │
│           │  │  ──────────────────────────────────────────────────────    │  │
│           │  │                                                            │  │
│           │  │  [✏️ Editar]    [🗑 Eliminar]    [📊 Asociar encuesta]       │  │
│           │  └────────────────────────────────────────────────────────────┘  │
│           │                                                                   │
├───────────┴───────────────────────────────────────────────────────────────────┤
│  © 2026 Hospital de Clínicas — Elyra v1.0                                     │
└───────────────────────────────────────────────────────────────────────────────┘
```

**Componentes:**
- **Header**: Título del documento + badge de categoría + fecha + contador de vistas.
- **QR Code**: generado dinámicamente con la librería QRious o similar.
- **Panel de enlace**: muestra URL completa con botones copiar e imprimir QR.
- **Descripción**: bloque de texto (si existe).
- **Acciones**: editar, eliminar (con confirmación), asociar encuesta (abre modal para seleccionar encuesta existente).

**Modal "Asociar encuesta":**
```
┌──────────────────────────────────────┐
│  Asociar encuesta al documento       │
├──────────────────────────────────────┤
│                                      │
│  Seleccioná una encuesta existente   │
│  ┌────────────────────────────────┐  │
│  │ Encuesta de satisfacción ▼     │  │
│  └────────────────────────────────┘  │
│                                      │
│  [Asociar]    [Cancelar]             │
└──────────────────────────────────────┘
```

### 5. Vista del Paciente (acceso vía QR — mobile)

```
┌──────────────────────────────────────┐
│  🏥 Hospital de Clínicas             │
│  📄 Documento Informativo            │
├──────────────────────────────────────┤
│                                      │
│  Indicaciones pre-operatorias        │
│  Cardiología                         │
│                                      │
│  ┌────────────────────────────────┐  │
│  │                                │  │
│  │  [Vista previa del PDF         │  │
│  │   embebida con visor           │  │
│  │   nativo del navegador]        │  │
│  │                                │  │
│  └────────────────────────────────┘  │
│                                      │
│  [📥 Descargar PDF]                  │
│                                      │
│  ─────────────────────────────       │
│  ¿Te resultó útil esta               │
│  información?                        │
│                                      │
│  [😊]  [😐]  [😞]                    │
│  Sí    Regular  No                   │
│                                      │
│  ──────────────────────────────      │
│  📋 Encuesta de satisfacción         │
│  Tu opinión nos ayuda a mejorar     │
│                                      │
│  [▶ Responder encuesta]              │
│                                      │
├──────────────────────────────────────┤
│  © Hospital de Clínicas              │
└──────────────────────────────────────┘
```

**Especificaciones mobile:**
- **Layout 100% vertical**, sin sidebar ni topbar.
- **Header** compacto con logo del hospital y título "Documento Informativo".
- **Visor PDF**: embed del PDF usando `<iframe>` o visor nativo del navegador, con altura mínima de 400px.
- **Feedback de utilidad**: 3 botones de emoji (Sí/Regular/No) que envían voto sin recargar la página (fetch POST). Al seleccionar uno, se marca visualmente y se agradece.
- **Encuesta**: botón "Responder encuesta" que redirige al formulario de encuesta pública.

**Estados de feedback:**
| Estado | Visual |
|---|---|
| Sin votar | 3 botones grises con borde |
| Votado | Botón seleccionado resalta en azul, los demás se atenúan, aparece "¡Gracias por tu feedback!" |
| Error | El voto no se pierde, se reintenta automáticamente |

### 6. Gestión de Encuestas

```
┌───────────┬─────────────────────────────────────────────────────────────────┐
│           │  🏥 Hospital de Clínicas          👤 Administrador  🚪           │
│  ───────  ├─────────────────────────────────────────────────────────────────┤
│  📋 Docs  │  Encuestas > Listado                                           │
│  📊 Enc.  │                                                                 │
│  🚑 Amb.  │  [+ Nueva encuesta]                                            │
│  👥 Cond. │                                                                 │
│  🗺 Rutas │  ┌──────┬──────────────────┬──────────┬────────────┬──────────┐ │
│           │  │ ID   │ Título           │ Preguntas │ Estado     │ Acciones │ │
│           │  ├──────┼──────────────────┼──────────┼────────────┼──────────┤ │
│           │  │ 1    │ Satisfacción     │    5     │ ✅ Activa  │  📊 👁   │ │
│           │  │ 2    │ Post-operatorio  │    8     │ ⏸ Inactiva │  📊 👁   │ │
│           │  │ 3    │ Atención recibida│   10     │ ✅ Activa  │  📊 👁   │ │
│           │  └──────┴──────────────────┴──────────┴────────────┴──────────┘ │
│           │                                                                 │
│           │  [📊 Ver resultados generales]                                   │
├───────────┴─────────────────────────────────────────────────────────────────┤
│  © 2026 Hospital de Clínicas — Elyra v1.0                                   │
└─────────────────────────────────────────────────────────────────────────────┘
```

**Especificaciones:**

| Columna | Descripción |
|---|---|
| ID | Número autoincremental |
| Título | Nombre de la encuesta |
| Preguntas | Cantidad de preguntas (badge numérica) |
| Estado | Badge coloreado: verde "Activa" / gris "Inactiva" |
| Acciones | 📊 Resultados (gráficas), 👁 Ver/editar |

**Badges de estado:**
- ✅ Activa: fondo verde claro, texto verde oscuro
- ⏸ Inactiva: fondo gris claro, texto gris oscuro

**Estados de la tabla:**
- **Con datos**: filas con información, paginación si >10.
- **Vacía**: mensaje "No hay encuestas creadas" + botón "Crear primera encuesta".
- **Cargando**: skeleton loader de 3 filas.

---

## Pantallas — Módulo de Ambulancias

### 7. Dashboard de Ambulancias

```
┌───────────┬──────────────────────────────────────────────────────────────────┐
│           │  🏥 Hospital de Clínicas          👤 Administrador  🚪           │
│  ───────  ├──────────────────────────────────────────────────────────────────┤
│  📋 Docs  │  Ambulancias > Traslados                                        │
│  📊 Enc.  │                                                                  │
│  🚑 Amb.  │  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌───────────┐       │
│  👥 Cond. │  │ 🟢 1     │  │ ⏳ 3     │  │ ✅ 12    │  │ 📊 Total  │       │
│  🗺 Rutas │  │ En curso │  │ Pendiente│  │ Complet. │  │ 16 hoy    │       │
│           │  └──────────┘  └──────────┘  └──────────┘  └───────────┘       │
│           │                                                                  │
│           │  [+ Nuevo traslado]  [Estado: ▼ Todos]  [🔍 Buscar...]          │
│           │                                                                  │
│           │  ┌───────┬────────────┬────────────┬───────────┬──────────────┐ │
│           │  │ Cód.  │ Elemento   │ Conductor  │ Estado    │ Acciones     │ │
│           │  ├───────┼────────────┼────────────┼───────────┼──────────────┤ │
│           │  │TR-001 │ Juan Pérez │ C. Sánchez │ 🟢 En ruta│ 👁 Ver 🗑   │ │
│           │  │TR-002 │ Equipo RX  │ M. López   │ ⏳ Pend.  │ 👁 Ver 🗑   │ │
│           │  │TR-003 │ Insumos    │ P. Gómez   │ ✅ Compl. │ 👁 Ver 🗑   │ │
│           │  │TR-004 │ María S.   │ L. Fernández│ 🔴 Canc. │ 👁 Ver 🗑   │ │
│           │  └───────┴────────────┴────────────┴───────────┴──────────────┘ │
│           │                                                                  │
│           │  [📋 Historial completo]    [🗺 Gestión de rutas]                 │
├───────────┴──────────────────────────────────────────────────────────────────┤
│  © 2026 Hospital de Clínicas — Elyra v1.0                                    │
└──────────────────────────────────────────────────────────────────────────────┘
```

**Tarjetas de resumen (stat cards):**
| Tarjeta | Color | Ícono | Descripción |
|---|---|---|---|
| En curso | Azul | 🟢 | Traslados actualmente activos |
| Pendientes | Amarillo | ⏳ | Traslados agendados sin iniciar |
| Completados hoy | Verde | ✅ | Traslados finalizados en el día |
| Total | Gris | 📊 | Suma del día |

**Badges de estado de traslado:**

| Estado | Badge |
|---|---|
| Pendiente | Fondo amarillo, texto oscuro — ⏳ Pendiente |
| En curso | Fondo azul claro, texto azul — 🟢 En ruta |
| En destino | Fondo azul claro, texto azul — 🏥 En destino |
| En retorno | Fondo azul claro, texto azul — 🔙 En retorno |
| Completado | Fondo verde claro, texto verde — ✅ Completado |
| Cancelado | Fondo rojo claro, texto rojo — 🔴 Cancelado |

**Interacciones:**
- Click en fila → navega al detalle del traslado.
- Botón "Ver" → mismo comportamiento.
- Tarjetas de resumen: click en "En curso" filtra la tabla a solo esos.
- Botón "Nuevo traslado" → formulario de creación.

### 8. Formulario de Nuevo Traslado

```
┌───────────┬────────────────────────────────────────────────────────────────────┐
│           │  🏥 Hospital de Clínicas              👤 Administrador  🚪          │
│  ───────  ├────────────────────────────────────────────────────────────────────┤
│  📋 Docs  │  Ambulancias > Nuevo traslado                                      │
│  📊 Enc.  │                                                                    │
│  🚑 Amb.  │  ┌───────────────────────────────────────────────────────────┐    │
│  👥 Cond. │  │  🚑 Datos del traslado                                    │    │
│  🗺 Rutas │  ├───────────────────────────────────────────────────────────┤    │
│           │  │                                                           │    │
│           │  │  Conductor        Copiloto                                │    │
│           │  │  ┌──────────────┐ ┌────────────────────────────────────┐ │    │
│           │  │  │ C. Sánchez ▼ │ │ María López                        │ │    │
│           │  │  └──────────────┘ └────────────────────────────────────┘ │    │
│           │  │                                                           │    │
│           │  │  Elemento a trasladar                                     │    │
│           │  │  ┌───────────────────────────────────────────────────┐   │    │
│           │  │  │ Juan Pérez — Historia Clínica #12345              │   │    │
│           │  │  └───────────────────────────────────────────────────┘   │    │
│           │  │                                                           │    │
│           │  │  Tipo:   ● Paciente   ○ Equipamiento   ○ Insumo         │    │
│           │  │                                                           │    │
│           │  │  ─────────────────────────────────────────────────────    │    │
│           │  │                                                           │    │
│           │  │  Origen               Destino                            │    │
│           │  │  ┌──────────────────┐ ┌──────────────────────────────┐   │    │
│           │  │  │ Hospital Clínicas │ │ Hospital Paysandú           │   │    │
│           │  │  └──────────────────┘ └──────────────────────────────┘   │    │
│           │  │                                                           │    │
│           │  │  Ruta                                                    │    │
│           │  │  ┌───────────────────────────────────────────────────┐   │    │
│           │  │  │ Ruta 1: Montevideo → Paysandú (378 km)     ▼     │   │    │
│           │  │  └───────────────────────────────────────────────────┘   │    │
│           │  │                                                           │    │
│           │  │  Salida estimada      Llegada estimada                   │    │
│           │  │  ┌─────────┐ 🕐 09:00 ┌─────────┐ 🕐 15:30              │    │
│           │  │  │28/05/26 │          │28/05/26 │                        │    │
│           │  │  └─────────┘          └─────────┘                        │    │
│           │  │                                                           │    │
│           │  │  [💾 Registrar traslado]    [Cancelar]                    │    │
│           │  └───────────────────────────────────────────────────────────┘    │
│           │                                                                    │
├───────────┴────────────────────────────────────────────────────────────────────┤
│  © 2026 Hospital de Clínicas — Elyra v1.0                                      │
└────────────────────────────────────────────────────────────────────────────────┘
```

**Secciones del formulario:**
1. **Datos del traslado**: conductor (select), copiloto (text/autocomplete), elemento (text), tipo (radio group).
2. **Origen/Destino**: dos inputs de texto con autocompletado de localidades.
3. **Ruta**: select que se actualiza según origen/destino.
4. **Tiempos estimados**: fecha + hora de salida, fecha + hora de llegada.

**Radio buttons de tipo:**
```
○ Paciente       ● Equipamiento      ○ Insumo
```
Visualmente como botones/tarjetas seleccionables con iconos.

**Validaciones:**
| Campo | Regla |
|---|---|
| Conductor | Requerido |
| Elemento | Requerido, mín. 3 caracteres |
| Tipo | Requerido (selección por defecto: Paciente) |
| Origen | Requerido |
| Destino | Requerido, distinto de origen |
| Fecha salida | Requerido, no puede ser anterior a hoy |
| Hora salida | Requerido |
| Hora llegada | Requerido, debe ser posterior a hora salida |

### 9. Detalle y Seguimiento de Traslado

```
┌───────────┬────────────────────────────────────────────────────────────────────┐
│           │  🏥 Hospital de Clínicas              👤 Administrador  🚪          │
│  ───────  ├────────────────────────────────────────────────────────────────────┤
│  📋 Docs  │  Ambulancias > TR-001                                             │
│  📊 Enc.  │                                                                    │
│  🚑 Amb.  │  ┌───────────────────────────────────────────────────────────┐    │
│  👥 Cond. │  │  🟢 TR-001 — EN CURSO                                    │    │
│  🗺 Rutas │  └───────────────────────────────────────────────────────────┘    │
│           │                                                                    │
│           │  ┌──────────── Timeline ────────────────────────────────────┐     │
│           │  │  ✅ 28/05 09:15  🏁 Salida                               │     │
│           │  │    ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─               │     │
│           │  │  🔵 28/05 11:30  🛣 En ruta — Actual                     │     │
│           │  │    ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─               │     │
│           │  │  ⭕ —            🏥 En destino                            │     │
│           │  │    ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─               │     │
│           │  │  ⭕ —            🔙 En retorno                            │     │
│           │  │    ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─               │     │
│           │  │  ⭕ —            ✅ Completado                            │     │
│           │  └──────────────────────────────────────────────────────────┘     │
│           │                                                                    │
│           │  ┌───────────────────────────────────────────────────────────┐    │
│           │  │  📋 Datos del traslado                                   │    │
│           │  ├───────────────────────────────────────────────────────────┤    │
│           │  │  Conductor:    Carlos Sánchez                             │    │
│           │  │  Copiloto:     María López                                │    │
│           │  │  Paciente:     Juan Pérez — HC #12345                     │    │
│           │  │  Tipo:         Paciente                                   │    │
│           │  │  Origen:       Montevideo — 28/05 09:15                   │    │
│           │  │  Destino:      Paysandú — estimado 28/05 15:30            │    │
│           │  │  Ruta:         Ruta 1 (Montevideo → Paysandú, 378 km)    │    │
│           │  └───────────────────────────────────────────────────────────┘    │
│           │                                                                    │
│           │  [🔄 Actualizar estado]  [✏️ Editar]  [🗑 Cancelar traslado]      │
│           │                                                                    │
│           │  ┌──── Historial de cambios ────────────────────────────────┐    │
│           │  │  09:15  Pendiente  →  En curso    por Administrador      │    │
│           │  │  09:10  Creado                    por Administrador      │    │
│           │  └──────────────────────────────────────────────────────────┘    │
├───────────┴────────────────────────────────────────────────────────────────────┤
│  © 2026 Hospital de Clínicas — Elyra v1.0                                      │
└────────────────────────────────────────────────────────────────────────────────┘
```

**Timeline visual:**
- Cada paso del flujo de estado se muestra como un elemento de timeline vertical.
- Pasos completados: check verde y texto en verde.
- Paso actual: círculo azul relleno y texto en negrita, marcado como "Actual".
- Pasos futuros: círculo vacío (⭕) y texto en gris.
- Línea conectora entre pasos: verde hasta el actual, gris después.

**Selector de actualización de estado (modal):**
```
┌──────────────────────────────────┐
│  Actualizar estado — TR-001      │
├──────────────────────────────────┤
│                                  │
│  Estado actual: 🟢 En ruta      │
│                                  │
│  Nuevo estado:                  │
│  ┌────────────────────────────┐ │
│  │ 🏥 En destino       ▼     │ │
│  └────────────────────────────┘ │
│  (solo estados válidos según   │
│   máquina de estados)          │
│                                  │
│  [Actualizar]  [Cancelar]        │
└──────────────────────────────────┘
```

**Máquina de estados del traslado:**
```
Pendiente → En curso → En destino → En retorno → Completado
    ↓                                                          
  Cancelado (desde cualquier estado antes de Completado)
```

### 10. Gestión de Rutas

```
┌───────────┬────────────────────────────────────────────────────────────────┐
│           │  🏥 Hospital de Clínicas          👤 Administrador  🚪          │
│  ───────  ├────────────────────────────────────────────────────────────────┤
│  📋 Docs  │  Ambulancias > Rutas                                           │
│  📊 Enc.  │                                                                │
│  🚑 Amb.  │  [+ Nueva ruta]                                               │
│  👥 Cond. │                                                                │
│  🗺 Rutas │  ┌────┬──────────────────────────┬─────────┬──────────┬──────┐ │
│           │  │ ID │ Nombre                   │ Origen  │ Destino  │ Km   │ │
│           │  ├────┼──────────────────────────┼─────────┼──────────┼──────┤ │
│           │  │ 1  │ Montevideo → Paysandú    │ Mdeo    │ Paysandú │ 378  │ │
│           │  │ 2  │ Montevideo → Salto       │ Mdeo    │ Salto    │ 496  │ │
│           │  │ 3  │ Montevideo → Rocha       │ Mdeo    │ Rocha    │ 210  │ │
│           │  │ 4  │ Montevideo → Rivera      │ Mdeo    │ Rivera   │ 503  │ │
│           │  │ 5  │ Montevideo → Colonia     │ Mdeo    │ Colonia  │ 177  │ │
│           │  └────┴──────────────────────────┴─────────┴──────────┴──────┘ │
│           │                                                                │
│           │  Mostrando 5 rutas                       [🗺 Ver mapa completo] │
├───────────┴────────────────────────────────────────────────────────────────┤
│  © 2026 Hospital de Clínicas — Elyra v1.0                                  │
└────────────────────────────────────────────────────────────────────────────┘
```

---

## Pantallas — Gestión de Conductores

### 11. Listado de Conductores

```
┌───────────┬────────────────────────────────────────────────────────────────┐
│           │  🏥 Hospital de Clínicas          👤 Administrador  🚪          │
│  ───────  ├────────────────────────────────────────────────────────────────┤
│  📋 Docs  │  Conductores > Listado                                         │
│  📊 Enc.  │                                                                │
│  🚑 Amb.  │  [+ Nuevo conductor]   [🔍 Buscar...]                         │
│  👥 Cond. │                                                                │
│  🗺 Rutas │  ┌────┬──────────────┬────────────┬───────────┬───────────┐   │
│           │  │ ID │ Nombre       │ Teléfono   │ Estado    │ Acciones  │   │
│           │  ├────┼──────────────┼────────────┼───────────┼───────────┤   │
│           │  │ 1  │ Carlos Sánchez│ 099 123 456│ ✅ Activo │ ✏️ 🗑    │   │
│           │  │ 2  │ María López   │ 098 456 789│ ✅ Activo │ ✏️ 🗑    │   │
│           │  │ 3  │ Pedro Gómez   │ 097 789 012│ ❌ Inact. │ ✏️ 🗑    │   │
│           │  └────┴──────────────┴────────────┴───────────┴───────────┘   │
│           │                                                                │
├───────────┴────────────────────────────────────────────────────────────────┤
│  © 2026 Hospital de Clínicas — Elyra v1.0                                  │
└────────────────────────────────────────────────────────────────────────────┘
```

---

## Especificaciones Generales de UI/UX

### Sistema de Rejilla (Grid System)
- **Desktop (>992px)**: sidebar visible (250px) + content area flexible.
- **Tablet (768-992px)**: sidebar colapsado a iconos (60px) o completamente oculto con botón hamburguesa.
- **Mobile (<768px)**: sidebar oculta, overlay al abrir, contenido a ancho completo.

### Estados Globales de Componentes

| Estado | Descripción | Visual |
|---|---|---|
| **Normal** | Componente en reposo | Estilo por defecto |
| **Hover** | Mouse sobre elemento interactivo | Sutil cambio de color/sombra |
| **Active/Focus** | Elemento seleccionado o con foco | Borde azul + sombra |
| **Disabled** | Elemento deshabilitado | Opacidad reducida (0.5), cursor not-allowed |
| **Loading** | Carga de datos en progreso | Skeleton loader o spinner |
| **Empty** | Sin datos para mostrar | Ilustración + mensaje + CTA |
| **Error** | Error en operación | Alerta roja con icono y mensaje |
| **Success** | Operación exitosa | Alerta verde o feedback visual breve |

### Micro-interacciones

| Elemento | Animación |
|---|---|
| Hover en fila de tabla | Background sutil (#f8f9fa) |
| Hover en tarjeta de estadística | Elevación (translateY -2px + sombra) |
| Transición de sidebar | Slide suave (300ms ease) |
| Aparición de alertas | Fade in (200ms) |
| Botón de submit al cargar | Spinner + texto "Procesando..." |
| Copiar al portapapeles | Tooltip "¡Copiado!" por 2s |
| Feedback de utilidad (paciente) | Scale(1.1) al seleccionar + transición suave |

### Formularios — Reglas Generales

- **Labels**: siempre visibles, no placeholders como label.
- **Mensajes de error**: debajo del campo, en rojo, con icono de advertencia.
- **Campos requeridos**: marcados con asterisco rojo (*).
- **Focus**: borde azul + box-shadow sutil.
- **Botón primario**: a la izquierda o centrado; botón secundario (cancelar) a la derecha.
- **Deshabilitado durante submit**: botón primary se deshabilita y muestra spinner.

### Modal / Diálogos

| Tipo | Uso | Tamaño |
|---|---|---|
| Confirmación | Eliminar documento/traslado | Small (300px) |
| Formulario rápido | Asociar encuesta, actualizar estado | Medium (500px) |
| Visualización | QR code, detalle rápido | Medium (500px) |

**Estructura del modal:**
```
┌──────────────────────────────┐
│  Título del modal       [✕]  │
├──────────────────────────────┤
│                              │
│  (contenido)                 │
│                              │
├──────────────────────────────┤
│  [Acción principal] [Cancelar]│
└──────────────────────────────┘
```

### Mensajes de Confirmación (Eliminar)

```
┌──────────────────────────────────┐
│  ⚠️ ¿Eliminar documento?         │
│                                  │
│  Se eliminará "Indicaciones..."  │
│  y su QR dejará de funcionar.    │
│  Esta acción no se puede         │
│  deshacer.                       │
│                                  │
│  [Sí, eliminar]  [Cancelar]      │
└──────────────────────────────────┘
```

### Notificaciones Toast

Para operaciones exitosas breves (copiar link, eliminar, crear):
```
┌──────────────────────────────────┐
│  ✅ Documento subido exitosamente │
└──────────────────────────────────┘
```
Aparece arriba a la derecha, auto-destruye en 4 segundos.

---

## Diseño Responsive

El sistema utiliza **Bootstrap 5** con los siguientes breakpoints y comportamientos:

| Breakpoint | Dispositivo | Sidebar | Tabla | Formularios |
|---|---|---|---|---|
| < 576px | Mobile | Overlay con hamburguesa | Convertida a cards | Vertical, ancho completo |
| 576-768px | Tablet grande | Colapsada con hamburguesa | Scroll horizontal | Dos columnas en secciones |
| 768-992px | Desktop pequeño | Visible (250px) | Normal | Normal |
| > 992px | Desktop | Visible (250px) | Normal con hover | Normal con row/col |

**Comportamiento mobile para tablas:**
En pantallas < 576px, las tablas se transforman en tarjetas (card layout):
```
┌──────────────────────────┐
│  🔍 Buscar...           │
├──────────────────────────┤
│  ┌────────────────────┐  │
│  │ 📄 Indicaciones    │  │
│  │ Cardiología        │  │
│  │ 15/05/2026         │  │
│  │ [✏️] [🗑] [📋]      │  │
│  └────────────────────┘  │
│  ┌────────────────────┐  │
│  │ 📄 Preparación     │  │
│  │ Imagenología       │  │
│  │ 14/05/2026         │  │
│  │ [✏️] [🗑] [📋]      │  │
│  └────────────────────┘  │
└──────────────────────────┘
```

---

## Paleta de Colores

| Elemento | Color | Uso |
|---|---|---|
| **Primario** | `#0d6efd` | Botones principales, links, sidebar activo, focus |
| **Primario dark** | `#0a5ed7` | Hover de botones primarios |
| **Primario light** | `#cfe2ff` | Fondos de alerta informativa, hover de filas |
| **Secundario** | `#6c757d` | Textos secundarios, badges inactivos |
| **Sidebar bg** | `#1a1f36` | Fondo de la barra lateral |
| **Sidebar hover** | `#2d3250` | Hover en items del sidebar |
| **Success** | `#198754` | Estado completado, activo |
| **Success bg** | `#d1e7dd` | Fondo de badge completado |
| **Warning** | `#ffc107` | Estado pendiente |
| **Warning bg** | `#fff3cd` | Fondo de badge pendiente |
| **Danger** | `#dc3545` | Estado cancelado/error |
| **Danger bg** | `#f8d7da` | Fondo de badge cancelado |
| **Info** | `#0dcaf0` | Estado en curso |
| **Info bg** | `#cfe2ff` | Fondo de badge en curso |
| **Fondo página** | `#f5f7fa` | Background general del content area |
| **Fondo blanco** | `#ffffff` | Cards, sidebar items |
| **Borde** | `#dee2e6` | Bordes de tabla, inputs, cards |
| **Texto** | `#212529` | Color de texto principal |

---

## Tipografía

- **Fuente**: `Segoe UI`, `system-ui`, `-apple-system`, `Arial`, sans-serif (legibilidad en pantalla).
- **Tamaño base**: 16px (equivalente a ~12pt, ajustado para lectura en pantalla).
- **Jerarquía**:
  - `h1`: 1.75rem (28px) — solo en dashboard principal
  - `h2`: 1.5rem (24px) — títulos de página
  - `h3`: 1.25rem (20px) — títulos de sección en cards
  - `h4`: 1rem (16px) — subtítulos
  - Cuerpo: 0.9rem (~14px) — texto general
  - Small: 0.8rem (~13px) — metadatos, badges
- **Peso**: Bold (700) para títulos, Semibold (600) para headers de tabla, Regular (400) para cuerpo.

---

## Accesibilidad

| Práctica | Implementación |
|---|---|
| **Contraste** | Relación de contraste ≥ 4.5:1 para texto normal |
| **Navegación por teclado** | Todos los botones y links accesibles con Tab |
| **Focus visible** | Outline azul en todos los elementos interactivos |
| **Alt text** | Iconos decorativos con `aria-hidden`; iconos funcionales con `aria-label` |
| **Formularios** | Labels asociados a inputs, mensajes de error con `aria-describedby` |
| **Responsive** | Meta viewport configurado, layout fluido |
| **Idioma** | `lang="es"` en todas las páginas |

---

## Flujo de Navegación General

```
                         ┌──────────┐
                         │  Login   │
                         └────┬─────┘
                              │ autenticación
                              ▼
                    ┌─────────────────┐
                    │   Dashboard     │
                    │   (Sidebar)     │
                    └────┬─────┬──────┘
                         │     │
              ┌──────────┘     └──────────┐
              ▼                            ▼
    ┌──────────────────┐      ┌──────────────────┐
    │  Documentación   │      │   Ambulancias    │
    │  ┌────────────┐  │      │  ┌────────────┐  │
    │  │ Subir      │  │      │  │ Nuevo      │  │
    │  │ Listar     │  │      │  │ Listar     │  │
    │  │ Detalle    │  │      │  │ Detalle    │  │
    │  │ Encuestas  │  │      │  │ Historial  │  │
    │  └────────────┘  │      │  │ Rutas      │  │
    └──────────────────┘      │  │ Conductores│  │
                              │  └────────────┘  │
                              └──────────────────┘
                                    │
                                    ▼
                         ┌──────────────────┐
                         │  Vista Pública   │
                         │  (vía QR, sin    │
                         │   autenticación) │
                         └──────────────────┘
```
