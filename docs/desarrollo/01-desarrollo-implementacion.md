# 13 - Desarrollo e Implementación

## Tecnologías Utilizadas

| Capa | Tecnología | Versión |
|---|---|---|
| Backend | PHP | >= 8.1 |
| Base de Datos | MySQL | 8+ |
| Frontend | HTML5 | — |
| Frontend | CSS3 | — |
| Frontend | JavaScript | ES6+ |
| Framework CSS | Bootstrap | 5 |
| Servidor Web | Apache / Nginx | — |
| Control de Versiones | Git + GitHub | — |
| CI/CD | GitHub Actions | — |

## Estructura del Proyecto

```
elyra/
├── .github/workflows/ci.yml     # Integración continua
├── config/                       # Configuración de la aplicación
│   └── database.php              # Conexión a MySQL
├── database/
│   ├── schema.sql                # DDL completo
│   ├── migrations/               # Migraciones SQL
│   └── seeds/                    # Datos de prueba
├── docs/                         # Documentación del proyecto
├── public/
│   ├── index.php                 # Front controller (entrada única)
│   ├── css/                      # Estilos personalizados
│   └── js/                       # Scripts del frontend
├── src/
│   ├── Domain/                   # Núcleo del negocio
│   │   ├── Entity/               # Entidades del dominio
│   │   ├── ValueObject/          # Objetos de valor
│   │   ├── Repository/           # Interfaces de repositorio
│   │   └── Service/              # Servicios de dominio
│   ├── Application/              # Capa de aplicación
│   │   ├── Ports/                # Puertos (interfaces)
│   │   │   ├── Input/            # Puertos de entrada
│   │   │   └── Output/           # Puertos de salida
│   │   ├── UseCases/             # Casos de uso
│   │   └── DTO/                  # Data Transfer Objects
│   └── Infrastructure/           # Adaptadores
│       ├── Persistence/MySQL/    # Repositorios MySQL
│       ├── Web/
│       │   ├── Controller/       # Controladores HTTP
│       │   └── Routes/           # Definición de rutas
│       └── Service/              # Servicios externos (QR, auth)
├── tests/
│   ├── Unit/                     # Pruebas unitarias
│   └── Integration/              # Pruebas de integración
├── composer.json                 # Dependencias PHP
├── package.json                  # Herramientas frontend
└── README.md                     # Documentación del repositorio
```

## Lenguajes de Programación

### PHP (Backend)

Se utiliza PHP >= 8.1 con tipado estricto, siguiendo el estándar PSR-4 para autoloading. No se utiliza framework web pesado para mantener la arquitectura limpia y el control total sobre la implementación hexagonal.

Características de PHP 8.1 utilizadas:
- Tipos declarativos (propiedades tipadas, union types)
- Enumeraciones (PHP 8.1 enums)
- Match expression
- Named arguments
- readonly properties

### JavaScript (Frontend)

JavaScript ES6+ para la interactividad del lado del cliente:
- Fetch API para comunicación asíncrona con el backend
- Manipulación del DOM para actualizaciones dinámicas
- Validación de formularios del lado del cliente
- Consumo de endpoints REST para búsqueda y filtros

### HTML / CSS / Bootstrap

- HTML5 semántico con estructura clara y accesible
- CSS3 con diseño responsive mediante Bootstrap 5
- Sistema de grilla (grid) para adaptación a distintos dispositivos
- Componentes Bootstrap: tablas, formularios, modales, alertas, navbar

## Frameworks y Librerías

| Librería | Propósito |
|---|---|
| PHPQRCode | Generación de códigos QR en el servidor |
| Bootstrap 5 | Framework CSS para interfaz responsive |
| ESLint | Linter para JavaScript |
| Stylelint | Linter para CSS |

## Capturas del Sistema

> *(Las capturas se agregarán una vez implementado el sistema)*

## Explicación de Módulos

### Módulo de Documentación para Pacientes

El módulo permite al administrativo cargar documentos PDF, los cuales son almacenados en el servidor. Cada documento recibe un código QR único generado automáticamente. Los pacientes acceden a los documentos escaneando el QR con su dispositivo móvil, sin necesidad de autenticación.

**Flujo de implementación:**
1. El administrativo sube un PDF mediante un formulario web.
2. El sistema valida el archivo (extensión, tamaño) y lo almacena en el servidor.
3. Se genera un código QR con un identificador único asociado al documento.
4. El QR se guarda como imagen en el servidor y se asocia al documento en la BD.
5. El paciente escanea el QR impreso y es redirigido a una vista pública del documento.
6. Opcionalmente, el paciente puede completar una encuesta de satisfacción asociada.

### Módulo de Trazabilidad de Ambulancias

El módulo permite registrar, gestionar y dar seguimiento a los traslados realizados en ambulancia.

**Flujo de implementación:**
1. El administrativo registra un nuevo traslado con todos los datos requeridos.
2. El sistema asigna un código único al traslado y lo registra con estado "pendiente".
3. Durante el ciclo operativo, el administrativo actualiza el estado del traslado.
4. Cada cambio de estado queda registrado en el historial para su trazabilidad.
5. Se pueden consultar traslados activos, historial completo y generar reportes.
