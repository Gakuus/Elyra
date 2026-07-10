# Tests Unitarios — Elyra

**30 archivos · 239 tests · 437 aserciones · 0 errores · 0 fallos**

Ejecución: `php vendor/bin/phpunit`

---

## Mapeo con Historias de Usuario (PRD)

| HU | Descripción | Tests que la cubren |
|---|---|---|
| **HU-01** | Login/logout de funcionarios | `AuthServiceTest` |
| **HU-02** | Subir documento PDF con QR | `DocumentoTest`, `DocumentFlowTest::testHU02_SubirDocumento` |
| **HU-03** | Editar documento existente | `DocumentFlowTest::testHU03_EditarDocumento` |
| **HU-04** | Eliminar documento (baja lógica) | `DocumentFlowTest::testHU04_EliminarDocumentoBajaLogica` |
| **HU-05** | Categorizar por área médica | `DocumentFlowTest::testHU05_CategorizarDocumento` |
| **HU-06** | Visualizar e imprimir QR | `CodigoQRTest`, `CodigoQREntityTest`, `QRGeneratorServiceTest` |
| **HU-07** | Acceso público por token QR | `CodigoQRTest` (generación única de token SHA-256) |
| **HU-08** | Crear encuesta con preguntas | `DocumentFlowTest::testHU08_CrearEncuestaConPreguntas` |
| **HU-09** | Responder encuesta | `RespuestaTest` |
| **HU-10** | Ver resultados de encuestas | — (pendiente) |
| **HU-11** | Listar documentos con filtros | `DocumentFlowTest::testHU11_ListarDocumentosConFiltros` |
| **HU-12** | Registrar traslado en ambulancia | `TrasladoTest`, `TrasladoStateMachineTest::testHU12_RegistrarTraslado` |
| **HU-13** | Actualizar estado del traslado | `TrasladoTest`, `TrasladoStateMachineTest` (ciclo completo) |
| **HU-14** | Consultar traslados activos | `TrasladoStateMachineTest::testHU14_ConsultarTrasladosActivos` |
| **HU-15** | Gestionar rutas | `RutaTest`, `RutaRepositoryTest` |
| **HU-16** | Historial de traslados | `TrasladoStateMachineTest::testHU16_HistorialTransiciones` |

---

## Value Objects (6 tests, 88 aserciones)

### `EmailTest`
- Email válido
- Email inválido lanza `InvalidArgumentException`
- `equals()` con mismo/diferente email
- `__toString()`

### `EstadoTrasladoTest`
- 6 estados válidos (pendiente, en_curso, en_destino, en_retorno, completado, cancelado)
- 3 inválidos lanzan `InvalidArgumentException` (vacío, inexistente, número)
- Case-insensitive: `PENDIENTE` → `pendiente`
- 12 transiciones válidas/inválidas
- Terminales: completado ✅, cancelado ✅, pendiente ❌, en_curso ❌
- Static factory `pendiente()`, `valores()`, `equals()`, `__toString()`

### `RolUsuarioTest`
- 3 roles válidos (admin, superadmin, conductor)
- Inválido lanza `InvalidArgumentException`
- `esAdmin()`, `esSuperadmin()`, `esConductor()`
- `equals()`, `__toString()`

### `CodigoQRTest`
- Genera token SHA-256 de 64 caracteres hex (no UUID)
- Tokens únicos entre instancias
- `__toString()` coincide con `value()`
- `equals()`, `fromString()`

### `TipoElementoTest`
- 3 tipos válidos (paciente, equipamiento, insumo)
- Inválido lanza `InvalidArgumentException`
- `equals()`, `__toString()`

### `TipoPreguntaTest`
- 3 tipos válidos (multiple_choice, escala, texto_libre)
- Inválido lanza `InvalidArgumentException`
- `equals()`, `__toString()`

---

## Entidades (10 tests, 131 aserciones)

### `TrasladoTest`
- Creación con datos mínimos y completos
- `setId()`
- `actualizarEstado()` válido e inválido
- Cancelación requiere motivo (`DomainException`)
- Cancelación con motivo
- Ciclo completo: pendiente → en_curso → en_destino → en_retorno → completado
- Cancelación desde pendiente y desde en_curso
- Estado `pendiente` por defecto
- copilotoId, vehiculoId, rutaId opcionales (null)

### `FuncionarioTest`
- Creación con constructor
- `verificarPassword()` con bcrypt
- `esAdmin()`, `esSuperadmin()`, `esConductor()`
- `isActivo()`, `setActivo()`
- Setters: `setTelefono()`, `setUsername()`, `setPasswordHash()`

### `PacienteTest`
- Creación (hereda de Usuario)
- `verificarPassword()` con/sin hash
- `getUsername()`, `setUsername()`
- `getCodigoQrId()`, `getTokenAcceso()`
- `isActivo()`, `setActivo()`

### `UsuarioTest`
- Clase base: creación con tipo, nombre, apellido
- `getNombreCompleto()`: "Nombre Apellido"
- `getFoto()` null por defecto
- `setFoto()` con base64

### `VehiculoTest`
- Creación con patente, modelo, año
- Setters y getters

### `RutaTest`
- Creación con nombre, origen, destino, distancia
- Setters y getters

### `ElementoTrasladoTest`
- Creación con tipo, paciente, descripción, cantidad
- Setters y getters

### `HistorialEstadoTest`
- Creación con trasladoId, estados, quién actualizó
- Setters y getters

### `PreguntaTest`
- Creación con tipo (VO), texto, orden, opciones, requerida
- `getTipo()`, `isRequerida()`, `getOpciones()`

### `RespuestaTest`
- Creación con preguntaId, valor, pacienteId
- Setters y getters

### `CategoriaTest`
- Creación con nombre, descripción
- Setters y getters

### `EncuestaTest`
- Creación con título, creadaPor
- `isActiva()` por defecto true
- `setActiva()`

### `DocumentoTest`
- Creación con todos los campos
- `setId()`, `setActivo()`
- `getExtension()`: maneja mayúsculas (.PDF → pdf, .JPG → jpg)
- `getArchivoContenido()` / `setArchivoContenido()`

### `CodigoQREntityTest`
- Creación con id, nombre, descripción
- `setId()`
- Descripción nullable
- `generarToken()`: SHA-256 hex de 64 chars
- Tokens únicos en llamadas consecutivas

---

## Servicios (4 tests, 101 aserciones)

### `ValidatorTest`
- `required()`: pasa con string, falla con null/vacío, pasa con espacios
- `email()`: pasa con email válido, falla con inválido, acepta null
- `minLength()` y `maxLength()`
- Validaciones múltiples encadenadas
- Múltiples errores en distintos campos
- `reset()` (no `clear()` — el método se llama `reset`)
- `numeric()`: pasa con número, falla con string, pasa con null/vacío
- `inArray()`: pasa con valor permitido, falla con no permitido

### `FileStorageServiceTest`
- `store()`: archivos PDF/docx permitidos, otros extensiones rechazadas
- `read()`: lectura de archivo almacenado
- `delete()`: eliminación física
- `getMimeType()`: detección correcta
- Archivos temporales creados/limpiados en `setUp/tearDown`

### `QRGeneratorServiceTest`
- `generateFile()`: crea archivo PNG en directorio destino
- `generateBase64()`: devuelve string base64
- `delete()`: elimina archivo
- Caracteres especiales en el nombre
- Creación automática del directorio si no existe

### `AuthServiceTest`
- `login()` exitoso con funcionario (username + password bcrypt)
- `login()` falla con contraseña incorrecta → "Credenciales inválidas"
- `login()` falla con usuario inexistente → "Credenciales inválidas"
- `login()` falla con funcionario inactivo → "desactivado"
- `login()` falla con paciente inactivo → "desactivado"
- `login()` exitoso con paciente
- `login()` falla con paciente sin password hash
- `logout()`: destruye sesión
- `isAuthenticated()`
- `getCurrentUserId()`
- `getCurrentUserRole()`
- `requireRole()`: pasa con rol correcto, falla sin autenticación, falla para paciente

---

## Repositorios (4 tests, PDO mockeado, 33 aserciones)

Usan `ReflectionClass::newInstanceWithoutConstructor()` para evitar la conexión real a MySQL.

### `TrasladoRepositoryTest`
- `findById()`: retorna null sin resultados
- `findByCodigo()`: retorna null sin resultados
- `nextCodigo()`: formato `TR-\d{5}`
- `countTotal()`: entero desde `fetchColumn`
- `countByEstado()`: filtra por estado
- `count()`: filtro genérico
- `findAll()`: retorna array

### `ConductorRepositoryTest`
- `countTotal()`, `countActivos()`
- `findById()`: null sin resultados

### `RutaRepositoryTest`
- `countTotal()`
- `findById()`: null sin resultados
- `findAll()`: retorna array

### `VehiculoRepositoryTest`
- `countTotal()`
- `findByPatente()`: null sin resultados
- `findById()`: null sin resultados

---

## Integración (2 tests, 33 aserciones)

### `TrasladoStateMachineTest`
Cubre HU-12, HU-13, HU-14 y HU-16:
- Ciclo completo exitoso (5 transiciones)
- Cancelación con motivo desde pendiente y desde en_curso
- Cancelación sin motivo lanza `DomainException`
- No se puede retroceder (en_curso → pendiente)
- No se puede cambiar estado terminal (completado)
- Lista de estados permitidos vs `EstadoTraslado::valores()`
- HU-12: registro con todos los campos
- HU-14: identificación de activos vs terminales
- HU-16: historial de 5 estados recorridos

### `DocumentFlowTest`
Cubre HU-02, HU-03, HU-04, HU-05, HU-08 y HU-11:
- HU-02: subir documento con campos obligatorios
- HU-03: editar título
- HU-04: baja lógica (setActivo false)
- HU-05: categorizar con especialidadId
- HU-08: crear encuesta con preguntas multiple_choice y escala
- HU-11: filtrar documentos por categoría

---

## Notas técnicas

- Framework: **PHPUnit 13.2.4** con PHP 8.4.20
- Atributos: `#[CoversClass]`, `#[DataProvider]` (sintaxis PHP 8.4 + PHPUnit 13)
- Repositorios: PDO mockeado (`createMock(\PDO::class)`) — evita conexión real MySQL
- AuthService: sesión iniciada en `setUp` (requerido por `SessionManager::login`)
- QR: el ValueObject genera `bin2hex(random_bytes(32))` = SHA-256 hex de 64 caracteres, **no UUID**
- Validator: `numeric()` y `email()` retornan early si el valor es null/vacío (comportamiento intencional)
- Archivos temporales: `FileStorageServiceTest` y `QRGeneratorServiceTest` crean/limpian en `setUp/tearDown`
- Documento: constructor tiene `$codigoQrId` opcional antes de `$categoriaId` obligatorio — siempre pasar `codigoQrId: null` explícitamente
