# Letra proyecto final

Nuestro cliente, el Hospital de Clínicas, nos ha solicitado el desarrollo de dos servicios que deberán ser implementados y alojados en los servidores del Departamento Técnico de Informática (DTI). Dichos servidores se encuentran ubicados en las instalaciones del hospital, específicamente en el piso 6.

## Primer servicio: Módulo de gestión de documentación para pacientes

Se desea desarrollar un servicio que permita a un funcionario administrativo cargar y gestionar documentación en un servidor, con el objetivo de que los pacientes del Hospital de Clínicas puedan acceder a dicha información de forma digital mediante el escaneo de un código QR. Este mecanismo permitirá que los usuarios visualicen los documentos directamente desde sus dispositivos móviles, sin necesidad de recibir copias impresas.

Actualmente, el Hospital de Clínicas destina una parte de su presupuesto a la impresión de documentos informativos para pacientes. La implementación de este sistema busca optimizar recursos y reducir costos, promoviendo la digitalización de la documentación y facilitando el acceso a la información por parte de los pacientes.

Entre los principales documentos que se entregan actualmente en formato impreso y que deberán estar disponibles en el sistema se encuentran los siguientes:

- Indicaciones de interrupción voluntaria del embarazo.
- Prostatectomía radical (indicaciones e información para el paciente).
- Preparación para estudios imagenológicos.
- Estudios diagnósticos con pertecneciato.
- Centellograma de perfusión miocárdica.
- Indicaciones ecocardiograma con dobutamina.
- Indicaciones para pacientes en tratamiento con warfarina.
- Indicaciones ecocardiograma transesofágico.
- Indicaciones para ingreso a centro de nefrología y trasplante.
- Plan de alta enfermería, Nefrología.
- Indicaciones de enfermería para usuarios trasplantados.
- Prevención de infecciones.
- Encuesta de satisfacción del usuario trasplantado.
- Pauta para pacientes ostomizados.

Adicionalmente, el sistema deberá contemplar la implementación de un módulo de encuestas, mediante el cual los pacientes puedan completar formularios de satisfacción relacionados con algunos de los servicios o actividades desarrolladas por el hospital. Las respuestas proporcionadas por los usuarios deberán almacenarse en el servidor, permitiendo posteriormente realizar análisis y cálculos de indicadores de satisfacción que contribuyan a la mejora continua de los servicios brindados por la institución.

## Segundo Servicio: Módulo de trazabilidad de ambulancias

Se desea desarrollar un sistema destinado a la gestión y seguimiento del transporte realizado mediante ambulancias. Este sistema deberá permitir registrar y administrar las diferentes solicitudes de traslado, así como realizar los controles necesarios asociados a cada operación.

Para cada solicitud de traslado se deberán registrar, como mínimo, los siguientes datos:

- El conductor responsable del vehículo
- El paciente o elemento a trasladar
- El copiloto o acompañante
- El punto de origen
- El destino, la hora de salida y la hora estimada o efectiva de llegada
- Asimismo, el sistema deberá contemplar la gestión de rutas dentro del circuito nacional
- Entre otras cosas

Cabe destacar que el elemento trasladado puede corresponder a un paciente biológico, no necesariamente una persona, así como también a equipamiento médico u otros insumos que requieran transporte especializado.

Las ambulancias partirán desde el Hospital de Clínicas, y un funcionario administrativo será responsable de realizar el seguimiento del estado del traslado, desde el momento de la salida hasta la llegada al destino y el posterior retorno al hospital. El sistema deberá permitir registrar y visualizar el estado de cada traslado durante todo su ciclo operativo.

## Introducción

### Infraestructura e integración con sistemas existentes

Las aplicaciones desarrolladas deberán ser alojadas en los servidores propios del Hospital de Clínicas, utilizando la infraestructura tecnológica disponible en la institución.

El hospital cuenta actualmente con un sistema centralizado que dispone de un panel principal de acceso para los usuarios. La propuesta consiste en que las aplicaciones desarrolladas se integren a dicho panel como accesos directos a nuevos módulos del sistema. De esta forma, los usuarios podrán iniciar sesión utilizando sus credenciales habituales (usuario y contraseña) y, una vez autenticados, acceder tanto a los módulos ya existentes como a los dos nuevos módulos que serán desarrollados en este proyecto.

Las aplicaciones están diseñadas para ser utilizadas principalmente por personal administrativo del área de la salud, quienes gestionarán la información y operarán los servicios del sistema. Asimismo, uno de los servicios también estará orientado a pacientes que asisten al Hospital de Clínicas, permitiéndoles acceder de forma sencilla a la documentación e información que la institución pone a su disposición.

### Cómo se trabaja con los módulos

#### Módulo de gestión y seguimiento de transporte de ambulancias

Este módulo permitirá a los funcionarios administrativos gestionar las solicitudes de traslado realizadas mediante ambulancias del Hospital de Clínicas. A través del sistema se podrán registrar los datos asociados a cada traslado, tales como el conductor, el acompañante, el paciente o elemento a trasladar, el punto de origen, el destino, la hora de salida y la hora de llegada. Asimismo, el sistema permitirá realizar el seguimiento del estado del traslado durante todo su recorrido, desde su salida del hospital hasta su llegada al destino y posterior retorno. De esta manera, el personal administrativo podrá mantener un control actualizado de las ambulancias en circulación y de las operaciones de transporte que se encuentren en curso.

#### Módulo de gestión y acceso digital a documentación para pacientes

Este módulo permitirá a los funcionarios administrativos cargar y administrar documentos informativos destinados a los pacientes del Hospital de Clínicas. Una vez cargados en el sistema, cada documento estará asociado a un código QR que los pacientes podrán escanear para acceder directamente a la información desde sus dispositivos móviles. El objetivo de este módulo es facilitar el acceso a la documentación médica y administrativa, al mismo tiempo que se reduce el uso de material impreso. Adicionalmente, el sistema podrá incluir formularios de encuestas de satisfacción que los pacientes podrán completar de forma digital, permitiendo que las respuestas se almacenen en el servidor para su posterior análisis por parte del hospital.
