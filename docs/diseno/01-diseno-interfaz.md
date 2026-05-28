# 12 - Diseño de la Solución — Diseño de Interfaz

## Estructura de Navegación

```
┌──────────────────────────────────────────────────────┐
│  PANEL PRINCIPAL (Sistema Centralizado del Hospital) │
├──────────────────────────────────────────────────────┤
│                                                      │
│  ┌──────────────┐   ┌──────────────┐                │
│  │ Documentación│   │  Ambulancias │                │
│  │   Pacientes  │   │              │                │
│  └──────────────┘   └──────────────┘                │
│                                                      │
│  (Módulos existentes del hospital...)                │
└──────────────────────────────────────────────────────┘
```

El sistema se integra al panel principal del hospital mediante dos accesos directos. Cada módulo es una aplicación web independiente accesible desde el panel una vez autenticado.

## Pantallas — Módulo de Documentación

### 1. Login (provisto por el sistema centralizado)

Pantalla de inicio de sesión con usuario y contraseña existente del hospital.

### 2. Dashboard de Documentación

```
┌──────────────────────────────────────────────────────────┐
│  Elyra │ Documentación            [Admin] [Cerrar sesión] │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  [ + Subir documento ]  [Categoría: ▼] [Buscar...]      │
│                                                          │
│  ┌────┬──────────┬────────────┬──────────┬───────────┐  │
│  │ QR │ Título   │ Categoría  │ Subido   │ Acciones  │  │
│  ├────┼──────────┼────────────┼──────────┼───────────┤  │
│  │ 👁 │ Indicac. │ Cardiología│ 15/05/26 │ ✏️ 🗑 📋 │  │
│  │ 👁 │ Prep.    │ Imagenol.  │ 14/05/26 │ ✏️ 🗑 📋 │  │
│  │ 👁 │ Plan     │ Nefrología │ 12/05/26 │ ✏️ 🗑 📋 │  │
│  └────┴──────────┴────────────┴──────────┴───────────┘  │
│                                                          │
│  [📊 Encuestas]                                          │
└──────────────────────────────────────────────────────────┘
```

**Componentes:**
- Barra superior: logo, nombre del módulo, usuario autenticado
- Barra de acciones: botón subir, filtro por categoría, buscador
- Tabla de documentos: QR preview, título, categoría, fecha, acciones
- Acciones por documento: 👁 Ver QR, ✏️ Editar, 🗑 Eliminar, 📋 Copiar link

### 3. Formulario de Subir Documento

```
┌──────────────────────────────────────────────────────────┐
│  Elyra │ Documentación > Subir documento    [← Volver]   │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Título:    ┌─────────────────────────────────────────┐  │
│             │ Indicaciones pre-operatorias             │  │
│             └─────────────────────────────────────────┘  │
│                                                          │
│  Categoría: ┌─────────────────────────────────────────┐  │
│             │ Cardiología                    ▼         │  │
│             └─────────────────────────────────────────┘  │
│                                                          │
│  Archivo:   [📎 Seleccionar archivo]   documento.pdf     │
│             (PDF, máx. 10 MB)                            │
│                                                          │
│  Descripción:                                            │
│  ┌─────────────────────────────────────────────────────┐ │
│  │ Instrucciones para pacientes sobre...               │ │
│  │                                                     │ │
│  └─────────────────────────────────────────────────────┘ │
│                                                          │
│  [📤 Subir documento]        [Cancelar]                  │
└──────────────────────────────────────────────────────────┘
```

### 4. Detalle del Documento (vista Administrativo)

```
┌──────────────────────────────────────────────────────────┐
│  Elyra │ Documentación > Detalle            [← Volver]    │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Título: Indicaciones para interrupción voluntaria       │
│         del embarazo                                     │
│  Categoría: Ginecología            Subido: 15/05/2026    │
│                                                          │
│  ┌──────────────┐   ┌────────────────────────────────┐   │
│  │   ┌──────┐   │   │  Link de acceso:               │   │
│  │   │ █ █  │   │   │  https://elyra.hc.edu.uy/      │   │
│  │   │ █ █  │   │   │  doc/abc123                    │   │
│  │   │ █ █  │   │   │                                │   │
│  │   └──────┘   │   │  [📋 Copiar] [🖨 Imprimir QR]   │   │
│  │   Código QR  │   └────────────────────────────────┘   │
│  └──────────────┘                                        │
│                                                          │
│  [✏️ Editar] [🗑 Eliminar] [📊 Asociar encuesta]          │
└──────────────────────────────────────────────────────────┘
```

### 5. Vista del Paciente (acceso vía QR — mobile)

```
┌──────────────────────────────┐
│  Hospital de Clínicas        │
│  📄 Documento Informativo    │
├──────────────────────────────┤
│                              │
│  Indicaciones para           │
│  interrupción voluntaria     │
│  del embarazo                │
│                              │
│  ┌────────────────────────┐  │
│  │                        │  │
│  │  (Vista previa del     │  │
│  │   documento PDF        │  │
│  │   embebida)            │  │
│  │                        │  │
│  └────────────────────────┘  │
│                              │
│  [📥 Descargar PDF]          │
│                              │
│  ─────────────────────────   │
│  ¿Te resultó útil esta       │
│  información?                │
│  [😊 Sí] [😐 Regular] [😞 No] │
│                              │
│  📋 Encuesta de satisfacción │
│  [▶ Responder encuesta]      │
└──────────────────────────────┘
```

### 6. Gestión de Encuestas

```
┌──────────────────────────────────────────────────────────┐
│  Elyra │ Encuestas                          [← Volver]   │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  [ + Nueva encuesta ]                                    │
│                                                          │
│  ┌────┬────────────┬───────────┬────────┬────────────┐   │
│  │ ID │ Título     │ Preguntas │ Estado │ Respuestas │   │
│  ├────┼────────────┼───────────┼────────┼────────────┤   │
│  │ 1  │ Satisfac.  │    5      │ ✅ Act.│    42      │   │
│  │ 2  │ Post-op.   │    8      │ ⏸ Inac.│    18      │   │
│  └────┴────────────┴───────────┴────────┴────────────┘   │
│                                                          │
│  [📊 Ver resultados]                                      │
└──────────────────────────────────────────────────────────┘
```

## Pantallas — Módulo de Ambulancias

### 7. Dashboard de Ambulancias

```
┌──────────────────────────────────────────────────────────┐
│  Elyra │ Ambulancias                 [Admin] [Cerrar ses.] │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  [ + Nuevo traslado ]  [Estado: ▼] [Buscar...]          │
│                                                          │
│  ┌──────┬──────────┬──────────┬──────────┬───────────┐   │
│  │ Cód. │ Paciente │ Conductor│ Estado   │ Acciones  │   │
│  ├──────┼──────────┼──────────┼──────────┼───────────┤   │
│  │TR-001│ Juan Pérez│Carlos S. │ 🟢 En ruta│ 👁 Ver  │   │
│  │TR-002│ Equipo RX│ María L. │ ⏳ Pend.  │ 👁 Ver  │   │
│  │TR-003│ Insumos  │ Pedro G. │ ✅ Compl.│ 👁 Ver  │   │
│  └──────┴──────────┴──────────┴──────────┴───────────┘   │
│                                                          │
│  Resumen: 🟢 1 en curso  ⏳ 3 pendientes  ✅ 12 hoy      │
│                                                          │
│  [📋 Historial] [🗺 Rutas]                                │
└──────────────────────────────────────────────────────────┘
```

### 8. Formulario de Nuevo Traslado

```
┌──────────────────────────────────────────────────────────┐
│  Elyra │ Ambulancias > Nuevo traslado       [← Volver]   │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Conductor:    ┌─────────────────────────────────────┐   │
│               │ Carlos Sánchez              ▼        │   │
│               └─────────────────────────────────────┘   │
│  Copiloto:     ┌─────────────────────────────────────┐   │
│               │ María López                           │   │
│               └─────────────────────────────────────┘   │
│                                                          │
│  Elemento a trasladar:                                   │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Juan Pérez                                        │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  Tipo:  ○ Paciente  ● Equipamiento  ○ Insumo           │
│                                                          │
│  Origen:  ┌──────────────────────────────────────────┐  │
│           │ Hospital de Clínicas                     │  │
│           └──────────────────────────────────────────┘  │
│  Destino: ┌──────────────────────────────────────────┐  │
│           │ Hospital de Paysandú                     │  │
│           └──────────────────────────────────────────┘  │
│  Ruta:    ┌──────────────────────────────────────────┐  │
│           │ Ruta 1 (Montevideo - Paysandú)    ▼      │  │
│           └──────────────────────────────────────────┘  │
│                                                          │
│  Salida estimada: ┌──────────┐                          │
│                   │ 28/05/26  │  🕐 09:00                │
│                   └──────────┘                          │
│  Llegada estimada:┌──────────┐                          │
│                   │ 28/05/26  │  🕐 15:30                │
│                   └──────────┘                          │
│                                                          │
│  [💾 Registrar traslado]        [Cancelar]               │
└──────────────────────────────────────────────────────────┘
```

### 9. Detalle y Seguimiento de Traslado

```
┌──────────────────────────────────────────────────────────┐
│  Elyra │ Ambulancias > TR-001              [← Volver]    │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Código: TR-001              Estado: 🟢 EN CURSO        │
│                                                          │
│  ┌────────────────────────────────────────────────────┐  │
│  │  🏁 Salida:      28/05/26 09:15  ✓                 │  │
│  │  🛣 En ruta:     Actual                             │  │
│  │  🏥 En destino:  —                                 │  │
│  │  🔙 En retorno:  —                                 │  │
│  │  ✅ Completado:  —                                 │  │
│  └────────────────────────────────────────────────────┘  │
│                                                          │
│  Datos del traslado:                                     │
│  Conductor: Carlos Sánchez                               │
│  Copiloto: María López                                   │
│  Paciente: Juan Pérez                                    │
│  Tipo: Paciente    Origen: Montevideo                    │
│  Ruta: Montevideo - Paysandú    Destino: Paysandú        │
│                                                          │
│  [🔄 Actualizar estado] [✏️ Editar] [🗑 Cancelar]          │
│                                                          │
│  Historial de cambios:                                   │
│  ┌────────────────────────────────────────────────────┐  │
│  │ 09:15 │ Pendiente → En curso │ Admin: Tom          │  │
│  │ 09:10 │ Creado              │ Admin: Tom           │  │
│  └────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────┘
```

### 10. Gestión de Rutas

```
┌──────────────────────────────────────────────────────────┐
│  Elyra │ Ambulancias > Rutas                [← Volver]   │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  [ + Nueva ruta ]                                        │
│                                                          │
│  ┌────┬────────────────────┬─────────┬──────────┬──────┐ │
│  │ ID │ Nombre             │ Origen  │ Destino  │ Km  │ │
│  ├────┼────────────────────┼─────────┼──────────┼──────┤ │
│  │ 1  │ Mvd - Paysandú     │ Mdeo    │ Paysandú │ 378  │ │
│  │ 2  │ Mvd - Salto        │ Mdeo    │ Salto    │ 496  │ │
│  │ 3  │ Mvd - Rocha         │ Mdeo    │ Rocha    │ 210  │ │
│  └────┴────────────────────┴─────────┴──────────┴──────┘ │
└──────────────────────────────────────────────────────────┘
```

## Diseño Responsive

El sistema utiliza **Bootstrap 5** con las siguientes consideraciones:

| Breakpoint | Dispositivo | Comportamiento |
|---|---|---|
| < 576px | Mobile (paciente) | Layout vertical, tabla → cards, menú colapsado |
| 576-768px | Tablet | Layout mixto, sidebar reducido |
| 768-992px | Desktop pequeño | Layout completo con tabla |
| > 992px | Desktop | Layout completo con todas las funcionalidades |

## Paleta de Colores Propuesta

| Elemento | Color |
|---|---|
| Primario | Azul institucional (`#0d6efd` o el color del hospital) |
| Secundario | Gris claro (`#6c757d`) |
| Éxito | Verde (`#198754`) — estado completado |
| Advertencia | Amarillo (`#ffc107`) — estado pendiente |
| Peligro | Rojo (`#dc3545`) — estado cancelado |
| Fondo | Blanco (`#ffffff`) |
| Texto | Gris oscuro (`#212529`) |

## Tipografía

- **Fuente**: Arial 11pt (según APA 7ª ed.) o la fuente institucional del hospital.
- **Encabezados**: Jerarquía de niveles (h1-h6) con Bootstrap.
