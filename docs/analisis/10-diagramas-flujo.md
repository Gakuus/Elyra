# 11 - Modelado del Sistema — Diagramas de Flujo

## Proceso 1: Carga de Documento y Generación de QR

```
┌──────────────────────┐
│   Administrativo     │
│   inicia sesión      │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│   Accede al módulo   │
│   de documentación   │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│   Selecciona         │
│   "Subir documento"  │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│   Completa formulario│
│   - Título           │
│   - Descripción      │
│   - Categoría        │
│   - Archivo PDF      │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│   ¿Datos válidos?    │
└────────┬─────────────┘
         │
    ┌────┴────┐
    │         │
   Sí        No
    │         │
    ▼         ▼
┌──────────┐ ┌──────────────────┐
│ Guardar  │ │ Mostrar error    │
│ documento│ │ al administrativo│
│ en BD    │ └──────────────────┘
└────┬─────┘
     ▼
┌──────────────────────┐
│ Generar código QR    │
│ asociado al          │
│ documento            │
└────┬─────────────────┘
     ▼
┌──────────────────────┐
│ Almacenar QR         │
│ en servidor          │
└────┬─────────────────┘
     ▼
┌──────────────────────┐
│ Mostrar confirmación │
│ y QR generado        │
└──────────────────────┘
```

## Proceso 2: Acceso del Paciente a Documento vía QR

```
┌──────────────────────┐
│   Paciente recibe    │
│   código QR impreso  │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│   Escanea QR con     │
│   su dispositivo     │
│   móvil              │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│   Sistema busca      │
│   documento asociado │
│   al QR              │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│   ¿Documento         │
│   encontrado?        │
└────────┬─────────────┘
         │
    ┌────┴────┐
    │         │
   Sí        No
    │         │
    ▼         ▼
┌──────────┐ ┌──────────────────┐
│ Mostrar  │ │ Mostrar mensaje  │
│ documento│ │ "Documento no    │
│ en vista │ │ disponible"      │
│ móvil    │ └──────────────────┘
└────┬─────┘
     ▼
┌──────────────────────┐
│   ¿Encuesta          │
│   asociada?          │
└────────┬─────────────┘
         │
    ┌────┴────┐
    │         │
   Sí        No
    │         │
    ▼         ▼
┌──────────┐ ┌──────────┐
│ Preguntar│ │  Fin     │
│ si desea │ │          │
│ responder│ │          │
└────┬─────┘ └──────────┘
     │
    Sí
     │
     ▼
┌──────────────────────┐
│   Mostrar encuesta   │
│   y guardar          │
│   respuestas         │
└──────────────────────┘
```

## Proceso 3: Registro de Traslado en Ambulancia

```
┌──────────────────────┐
│   Administrativo     │
│   inicia sesión      │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│   Accede al módulo   │
│   de ambulancias     │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│   Selecciona         │
│   "Nuevo traslado"   │
└────────┬─────────────┘
         ▼
┌─────────────────────────────────────┐
│   Completa formulario de traslado:  │
│   - Conductor responsable           │
│   - Copiloto / Acompañante          │
│   - Paciente / Elemento a trasladar │
│   - Tipo (paciente/equipo/insumo)   │
│   - Punto de origen                 │
│   - Destino                         │
│   - Hora de salida                  │
│   - Hora estimada de llegada        │
│   - Ruta (circuito nacional)        │
└────────┬────────────────────────────┘
         ▼
┌──────────────────────┐
│   ¿Datos válidos?    │
└────────┬─────────────┘
         │
    ┌────┴────┐
    │         │
   Sí        No
    │         │
    ▼         ▼
┌──────────┐ ┌──────────────────┐
│ Guardar  │ │ Mostrar error    │
│ traslado │ │ al administrativo│
│ Estado:  │ └──────────────────┘
│ Pendiente│
└────┬─────┘
     ▼
┌──────────────────────┐
│ Mostrar confirmación │
│ y resumen del        │
│ traslado             │
└──────────────────────┘
```

## Proceso 4: Ciclo de Vida del Traslado (Seguimiento)

```
┌─────────────────────────────────────┐
│   Estado inicial: PENDIENTE         │
│   Traslado registrado, no iniciado  │
└────────┬────────────────────────────┘
         ▼
┌─────────────────────────────────────┐
│   Administrativo registra salida    │
│   → EN CURSO                       │
│   (Se registra hora de salida)      │
└────────┬────────────────────────────┘
         ▼
┌─────────────────────────────────────┐
│   Ambulancia en ruta al destino     │
│   → EN CURSO                        │
│   (Administrativo puede actualizar  │
│    novedades durante el trayecto)   │
└────────┬────────────────────────────┘
         ▼
┌─────────────────────────────────────┐
│   Ambulancia llega a destino        │
│   → EN DESTINO                      │
│   (Se registra hora de llegada)     │
└────────┬────────────────────────────┘
         ▼
┌─────────────────────────────────────┐
│   Ambulancia inicia retorno         │
│   → EN RETORNO                      │
└────────┬────────────────────────────┘
         ▼
┌─────────────────────────────────────┐
│   Ambulancia regresa al hospital    │
│   → COMPLETADO                      │
│   (Se registra hora de retorno)     │
└────────┬────────────────────────────┘
         ▼
┌─────────────────────────────────────┐
│   Alternativa:                      │
│   Administrativo cancela traslado   │
│   → CANCELADO                       │
│   (Se registra motivo de cancelación)│
└─────────────────────────────────────┘
```

## Proceso 5: Encuesta de Satisfacción

```
┌──────────────────────┐
│  Administrativo crea │
│  encuesta con        │
│  preguntas           │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│  Asocia encuesta a   │
│  documento o         │
│  servicio            │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│  Paciente accede     │
│  a documento vía QR  │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│  ¿Desea responder    │
│  encuesta?           │
└────────┬─────────────┘
         │
     ┌───┴───┐
     │       │
    Sí      No
     │       │
     ▼       ▼
┌────────┐ ┌────────┐
│ Mostrar│ │  Fin   │
│ encuesta│ │        │
└───┬────┘ └────────┘
     ▼
┌──────────────────────┐
│  Paciente completa   │
│  y envía respuestas  │
└────────┬─────────────┘
         ▼
┌──────────────────────┐
│  Validar respuestas  │
└────────┬─────────────┘
         │
    ┌────┴────┐
    │         │
   Sí        No
    │         │
    ▼         ▼
┌──────────┐ ┌──────────────────┐
│ Guardar  │ │ Mostrar error    │
│ respuestas│ │ al paciente      │
│ en BD    │ └──────────────────┘
└────┬─────┘
     ▼
┌──────────────────────┐
│  Mostrar             │
│  confirmación        │
│  al paciente         │
└──────────────────────┘
```
