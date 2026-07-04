# 12 - Diseño de la Solución — Arquitectura del Sistema

## Modelo Cliente-Servidor Web

```
┌──────────────────────────────────────────────────────────────┐
│                        CLIENTE                                │
│  ┌────────────────────────────────────────────────────────┐  │
│  │                    Navegador Web                        │  │
│  │  ┌──────────┐  ┌──────────┐  ┌───────────────────┐    │  │
│  │  │  HTML5   │  │ CSS3     │  │  JavaScript ES6+   │    │  │
│  │  │          │  │ Bootstrap│  │  (Fetch API, DOM)  │    │  │
│  │  │          │  │   5      │  │                    │    │  │
│  │  └──────────┘  └──────────┘  └───────────────────┘    │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────┬───────────────────────────────────┘
                           │  HTTP/HTTPS
                           ▼
┌──────────────────────────────────────────────────────────────┐
│                        SERVIDOR WEB (Apache/Nginx)            │
├──────────────────────────────────────────────────────────────┤
│  ┌────────────────────────────────────────────────────────┐  │
│  │              PHP ≥ 8.1 — Front Controller              │  │
│  │  public/index.php → Routing → Dispatcher               │  │
│  └────────────────────────────────────────────────────────┘  │
│                           │                                   │
│                           ▼                                   │
│  ┌────────────────────────────────────────────────────────┐  │
│  │          ARQUITECTURA HEXAGONAL (Puertos y Adaptadores) │  │
│  │                                                        │  │
│  │  ┌─────────────┐  ┌──────────┐  ┌──────────────────┐  │  │
│  │  │  INFRA-     │  │   APP    │  │     DOMINIO      │  │  │
│  │  │  STRUCTURE  │──▶│(CASOS DE │──▶│ (ENTIDADES Y    │  │  │
│  │  │ (ADAPTERS)  │  │   USO)   │  │  REPOSITORIOS)  │  │  │
│  │  │             │  │          │  │                  │  │  │
│  │  │ MySQL  ◀────┤  │ Ports ◀──┤  │ Domain Entities ◀│  │  │
│  │  │ Session ◀───┤  │ DTOs     │  │ Value Objects    │  │  │
│  │  │ QR Gen ◀────┤  │          │  │ Domain Services  │  │  │
│  │  │ Email ◀─────┤  │          │  │ Repository (iface)│  │  │
│  │  │ Web Ctrl ◀──┤  │          │  │                  │  │  │
│  │  └─────────────┘  └──────────┘  └──────────────────┘  │  │
│  └────────────────────────────────────────────────────────┘  │
│                           │                                   │
│                           ▼                                   │
│  ┌────────────────────────────────────────────────────────┐  │
│  │              BASE DE DATOS MySQL                        │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

## Arquitectura Hexagonal Detallada

### Capa de Dominio (Core — sin dependencias externas)

```
src/Domain/
├── Entity/
│   ├── Usuario.php
│   ├── Documento.php
│   ├── Categoria.php
│   ├── Encuesta.php
│   ├── Pregunta.php
│   ├── Opcion.php
│   ├── RespuestaEncuesta.php
│   ├── RespuestaPregunta.php
│   ├── Conductor.php
│   ├── Ruta.php
│   ├── Traslado.php
│   └── HistorialEstado.php
├── ValueObject/
│   ├── Email.php
│   ├── CodigoQR.php
│   ├── EstadoTraslado.php
│   ├── TipoElemento.php
│   └── TipoPregunta.php
├── Repository/
│   ├── DocumentoRepositoryInterface.php
│   ├── TrasladoRepositoryInterface.php
│   ├── EncuestaRepositoryInterface.php
│   ├── ConductorRepositoryInterface.php
│   └── UsuarioRepositoryInterface.php
└── Service/
    ├── DocumentoService.php
    ├── TrasladoService.php
    └── EncuestaService.php
```

**Responsabilidades:**
- Entidades con comportamiento y reglas de negocio.
- Value Objects inmutables para conceptos con validación.
- Interfaces de repositorio (puertos de salida).
- Servicios de dominio con lógica pura.

### Capa de Aplicación (Casos de Uso — orquestación)

```
src/Application/
├── Ports/
│   ├── Input/                          /* Puertos de entrada */
│   │   ├── SubirDocumentoPort.php
│   │   ├── RegistrarTrasladoPort.php
│   │   ├── ActualizarEstadoPort.php
│   │   ├── CrearEncuestaPort.php
│   │   └── ResponderEncuestaPort.php
│   └── Output/                         /* Puertos de salida */
│       ├── QRGeneratorInterface.php
│       ├── FileStorageInterface.php
│       └── AuthProviderInterface.php
├── UseCases/
│   ├── SubirDocumentoUseCase.php
│   ├── EliminarDocumentoUseCase.php
│   ├── RegistrarTrasladoUseCase.php
│   ├── ActualizarEstadoTrasladoUseCase.php
│   ├── CrearEncuestaUseCase.php
│   ├── ResponderEncuestaUseCase.php
│   └── ObtenerResultadosUseCase.php
└── DTO/
    ├── DocumentoDTO.php
    ├── TrasladoDTO.php
    └── EncuestaDTO.php
```

**Responsabilidades:**
- Casos de uso que orquestan el flujo.
- DTOs para transferencia de datos entre capas.
- Puertos de entrada (interfaces que los controladores web implementan).

### Capa de Infraestructura (Adaptadores — implementaciones concretas)

```
src/Infrastructure/
├── Persistence/
│   └── MySQL/
│       ├── DocumentoRepository.php        /* implements DocumentoRepositoryInterface */
│       ├── TrasladoRepository.php
│       ├── EncuestaRepository.php
│       ├── ConductorRepository.php
│       └── UsuarioRepository.php
├── Web/
│   ├── Controller/
│   │   ├── DocumentoController.php
│   │   ├── TrasladoController.php
│   │   ├── EncuestaController.php
│   │   ├── AuthController.php
│   │   └── PublicController.php           /* acceso sin auth (QR) */
│   └── Routes/
│       └── web.php                        /* definición de rutas */
├── Service/
│   ├── QRGeneratorService.php            /* implements QRGeneratorInterface */
│   ├── FileStorageService.php            /* implements FileStorageInterface */
│   └── AuthService.php                   /* implements AuthProviderInterface */
└── Session/
    └── PhpSessionManager.php
```

**Responsabilidades:**
- Implementaciones concretas de los puertos.
- Controladores web que reciben requests y llaman casos de uso.
- Repositorios MySQL con consultas SQL/PDO.
- Servicios externos (generación QR, almacenamiento de archivos).

## Flujo de una Petición Web

```
                         ┌─────────────┐
                         │  Navegador  │
                         └──────┬──────┘
                                │ GET /documentos
                                ▼
┌───────────────────────────────────────────────────────────────┐
│                      public/index.php                         │
│  Front Controller: carga rutas, crea container, despacha      │
└───────────────────────────┬───────────────────────────────────┘
                            │
                            ▼
┌───────────────────────────────────────────────────────────────┐
│              Infrastructure/Web/Routes/web.php                │
│  Encuentra la ruta → DocumentoController::index()            │
└───────────────────────────┬───────────────────────────────────┘
                            │
                            ▼
┌───────────────────────────────────────────────────────────────┐
│             Infrastructure/Web/Controller/                    │
│                   DocumentoController.php                     │
│  1. Valida request (GET, auth, parámetros)                    │
│  2. Crea DTO si es necesario                                 │
│  3. Llama al caso de uso: ListarDocumentosUseCase            │
└───────────────────────────┬───────────────────────────────────┘
                            │
                            ▼
┌───────────────────────────────────────────────────────────────┐
│              Application/UseCases/                            │
│              ListarDocumentosUseCase.php                      │
│  1. Obtiene datos del repositorio (a través de interfaz)     │
│  2. Aplica lógica de negocio si corresponde                  │
│  3. Retorna DTOs o Entity arrays al controller               │
└───────────────────────────┬───────────────────────────────────┘
                            │
                            ▼
┌───────────────────────────────────────────────────────────────┐
│              Infrastructure/Persistence/MySQL/                │
│                  DocumentoRepository.php                      │
│  1. Ejecuta consulta SQL (SELECT * FROM documento WHERE...)  │
│  2. Mapea resultados a entidades del dominio                 │
│  3. Retorna array de Documento                                │
└───────────────────────────┬───────────────────────────────────┘
                            │
                            ▼
┌───────────────────────────────────────────────────────────────┐
│  DocumentoController: renderiza vista con los datos            │
│  → public/index.php                                           │
│  → HTML + CSS + JS al navegador                              │
└───────────────────────────────────────────────────────────────┘
```

## Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Frontend | HTML5, CSS3, JavaScript ES6+, Bootstrap 5 |
| Backend | PHP ≥ 8.1 |
| Base de Datos | MySQL 8+ |
| Servidor Web | Apache o Nginx |
| QR Generation | phpqrcode library o similar |
| Autenticación | Integración con sistema existente del hospital |
| Control de Versiones | Git + GitHub |
| CI/CD | GitHub Actions |

## Diagrama de Despliegue

```
                     Internet (HTTPS)
                         |
                         ▼
            ┌──────────────────────────────────┐
            │  AWS EC2 — Nginx Proxy Reverso   │
            │  (Ubuntu Server, IP Pública)      │
            │  Recibe solicitudes y las deriva  │
            │  vía VPN                          │
            └──────────────┬───────────────────┘
                           │ VPN (túnel cifrado)
                           ▼
            ┌──────────────────────────────────┐
            │           PROXMOX                │
            │     (Hipervisor Tipo 1)          │
            └──────────────┬───────────────────┘
                           │
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│          VM Debian 12 — Servidor Flask                           │
│          ─────────────────────────────                           │
│                                                                  │
│   ┌─────────────────────────────────────────────────────────┐   │
│   │              Nginx :8080 (Enrutador interno)              │   │
│   │                                                          │   │
│   │  /registro/*  /proyectos  /websec-lab/  /terminal/   /  │   │
│   └──────┬──────────┬─────────────┬───────────┬──────────┬───┘   │
│          │          │             │           │          │       │
│          ▼          ▼             ▼           ▼          ▼       │
│   ┌──────────┐ ┌─────────┐ ┌──────────┐ ┌────────┐ ┌────────┐  │
│   │ Gunicorn │ │ dbitsp  │ │  Docker  │ │  ttyd  │ │ Docker │  │
│   │ Flask:5000│ │ SSH Deb │ │websec-lab│ │ :7681  │ │dos-target│
│   │(registro) │ │MySQL    │ │Web vuln. │ │Terminal│ │PC vuln. │  │
│   │           │ │doc_users│ │          │ │web     │ │         │  │
│   │           │ │Home dirs│ │          │ │autolog │ │         │  │
│   └──────────┘ └─────────┘ └──────────┘ └────────┘ └────────┘  │
│                                                                  │
│   LAN: 192.168.2.238:80                                          │
└──────────────────────────────────────────────────────────────────┘
                           │
                    Red interna ITSP
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│                           USUARIOS                                │
│  ┌────────────────┐  ┌────────────────┐  ┌────────────────────┐  │
│  │  Admin (PC)    │  │  Admin (PC)    │  │  Paciente (móvil)  │  │
│  │  Chrome/FF     │  │  Chrome/FF     │  │  Cámara + QR       │  │
│  └────────────────┘  └────────────────┘  └────────────────────┘  │
└──────────────────────────────────────────────────────────────────┘
```
