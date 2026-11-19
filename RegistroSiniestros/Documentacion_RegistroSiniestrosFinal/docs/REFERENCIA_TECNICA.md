# Referencia técnica

[Volver al README](../README.md) · [Estructura del proyecto](ESTRUCTURA.md)

Esta referencia documenta las rutas de `index.php`, las comprobaciones de los controladores/modelos y el esquema definido en `z database/instalacion_completa.sql`. No modifica la base ni certifica que otra instalación tenga exactamente ese esquema.

## 1. Convenciones

- Ruta base: `index.php?page=RUTA`.
- `id` identifica un siniestro en el detalle/chat y una evidencia en `evidencia`.
- La sesión conserva, entre otros datos, `id_usuario`, `alias`, `rol_id` y `rol_slug`.
- `csrf_token` se incluye en los formularios de escritura protegidos.
- Los mensajes de éxito/error se presentan en HTML o JSON, no como texto de error en la URL.

## 2. Rutas de usuarios y sesión

| `page` | Uso habitual | Destino | Función |
| --- | --- | --- | --- |
| `logIn`, `login` | GET | `UsuarioC::index()` | Formulario; redirección al panel si hay sesión |
| `logInAuth` | POST | `UsuarioC::logIn()` | Valida `txtalias` y `txtpassword` con CSRF |
| `sigIn` | GET | `UsuarioC::mostrarRegistro()` | Formulario de registro |
| `sigInAuth` | POST | `UsuarioC::procesarRegistro()` | Alta de asegurado con CSRF |
| `logout` | Enlace GET | `UsuarioC::logOut()` | Destruye la sesión |
| `asegurado`, `ajustador`, `supervisor`, `admin` | GET con sesión | `UsuarioC::mostrarPerfil()` | Renderiza el panel según el rol de sesión, no según el nombre solicitado en la URL |
| `miPerfil` | GET con sesión | `UsuarioC::EditarPerfil()` | Formulario del perfil propio |
| `editarPerfil` | POST con sesión | `UsuarioC::procesarEdicion()` | Actualización del perfil con CSRF |

El acceso se realiza con alias y contraseña transformada con MD5 en el controlador. `sp_validar_usuario` exige `usuario.estatus = 1`. La columna `alias` no tiene restricción UNIQUE en el esquema incluido; el correo sí la tiene.

## 3. Rutas de siniestros y pólizas

| `page` | Uso habitual | Acceso comprobado / comportamiento |
| --- | --- | --- |
| `report` | GET | Formulario solo para asegurado |
| `guardarSiniestro` | POST | Asegurado, CSRF, póliza propia y validación de evidencias |
| `misSiniestros` | GET | Sesión; filtrado por propietario, asignación o supervisión |
| `detalle` | GET, `id` | Sesión y relación autorizada con el expediente |
| `guardarEvaluacion`, `guardarEdicionEvaluacion` | POST | Ajustador asignado o supervisor/admin; CSRF y diagnóstico no vacío |
| `actualizarEstatus` | POST | Supervisor/admin o ajustador asignado; CSRF, caso abierto y autorización transaccional |
| `asignaciones` | GET | Supervisor/admin; `filtro=pendientes` o listado completo |
| `asignarAjustador` | POST | Supervisor/admin, CSRF, caso no cerrado y ajustador activo |
| `polizas` | GET | Pólizas de la cuenta con sesión; no consulta global por ser supervisor |
| `agregarPoliza` | GET | Formulario disponible únicamente para el rol asegurado |
| `guardarPoliza` | POST | Asegurado, sesión y CSRF; alta atómica de póliza, aseguradora nueva opcional y vehículo nuevo o propio |
| `agregarAuto` | GET habitual | Carga directamente el formulario de vehículo desde el enrutador |
| `guardarAuto` | POST | Sesión y CSRF antes del INSERT de vehículo |

El formulario `agregarAuto` se incluye directamente en `index.php`

La asignación usa `id_siniestro`, `ajustador_id` y, opcionalmente, `volver`. Un `ajustador_id` igual a `0` retira la asignación. `volver=detalle` regresa al expediente; el otro destino previsto es el listado de asignaciones.

El estado **Cerrado** impide reasignar en el modelo. La vista también oculta determinados formularios al cerrar

## 4. Rutas de multimedia

| `page` | Método | Resultado |
| --- | --- | --- |
| `evidencia` | GET o HEAD, `id` | Contenido binario autorizado, con tipo MIME detectado |
| `migrarVideos` | GET | Pantalla de mantenimiento de supervisor/admin, sin enlace en el menú |
| `migrarVideos` | POST | Conversión de `id_multimedia` con CSRF |

## 5. API del chat

### Consultar: `chatMensajes`

```http
GET index.php?page=chatMensajes&id=3
Accept: application/json
```

| Parámetro | Uso |
| --- | --- |
| `id` | ID positivo del siniestro |
| `antes` | Opcional: mensajes con ID menor al indicado |
| `despues` | Opcional: mensajes con ID mayor al indicado |

No se admiten `antes` y `despues` positivos a la vez. El servidor devuelve hasta 50 mensajes ordenados de forma ascendente dentro del bloque. Sin cursor devuelve los últimos 50; con `antes` obtiene un bloque anterior y con `despues` permite ponerse al día.

Campos de respuesta: `ok`, `usuario_id` del lector, `mensajes`, `participantes`, `hay_anteriores` y `hay_mas`. Cada mensaje contiene `id`, `usuario_id`, `mensaje`, `fecha`, `autor` y `rol_nombre`. Los participantes muestran el alias/nombre del asegurado y del ajustador actual, si existe.

### Enviar: `chatEnviar`

```http
POST index.php?page=chatEnviar
Accept: application/json
```

El cuerpo del formulario contiene `id_siniestro`, `mensaje` y `csrf_token`. La identidad del emisor procede de la sesión; la hora procede de MySQL. No se acepta un usuario emisor seleccionado por el navegador.

Con `Accept: application/json`, el éxito responde `201` y un objeto con `ok` e `id` del nuevo mensaje. Sin esa cabecera se utiliza el formulario convencional, un aviso de sesión y una redirección `303` al detalle con ancla `#chat`.

| Estado HTTP del chat | Significado general |
| --- | --- |
| `200` | Lectura correcta |
| `201` | Mensaje creado |
| `401` | Falta sesión válida o cuenta activa |
| `403` | Acceso no permitido, caso no disponible o token CSRF rechazado |
| `405` | Método HTTP no admitido |
| `422` | Parámetros o mensaje inválidos |
| `500` | Error inesperado con mensaje controlado |

Cada lectura/envío abre una transacción y consulta el responsable actual, el propietario de la póliza y el rol/estado del actor. El bloqueo inicial de `siniestro` coordina estas operaciones con la reasignación. El chat se guarda como conversación lineal: `mensaje_padre` queda NULL, aunque la columna permite una relación de respuesta a nivel de esquema.

## 6. Tablas de la base de datos

El instalador principal define **11 tablas**. Todas usan `id` como clave primaria autoincremental.

| Tabla | Contenido | Relaciones o reglas relevantes |
| --- | --- | --- |
| `rol` | Nombre y slug del rol | `slug` único; referido por `usuario.rol_id` |
| `usuario` | Datos personales, acceso, alias, foto, estado y rol | `email` único; `foto_perfil` LONGBLOB; FK a `rol` |
| `aseguradoras` | Catálogo de compañías | Referido por `poliza.aseguradora_id` |
| `vehiculo` | Marca, modelo, color, placas y número de serie | `placas` y `num_serie` únicos |
| `poliza` | Número, compañía, propietario y vehículo | FKs a `aseguradoras`, `usuario` y `vehiculo`; único por número y compañía |
| `estatus` | Catálogo de estados del caso | `nombre` único |
| `siniestro` | Fecha, hora, ubicación, descripción y seguimiento | FK obligatoria a `poliza`; ajustador y estatus pueden ser NULL |
| `historialestatus` | Cambios de estatus con fecha | FKs a `siniestro` y `estatus` |
| `multimedia` | Evidencias Foto/Video, binario, ruta y fecha | `archivo` LONGBLOB, `ruta` nullable; FK a `siniestro` |
| `evaluacion` | Diagnóstico, fecha y autor | FK a `siniestro` y usuario ajustador; `siniestro_id` único |
| `chat` | Mensaje, fecha, caso, emisor y padre opcional | FKs a `siniestro`, `usuario` y la misma tabla `chat` |

### Relaciones principales

- Un rol puede relacionarse con muchos usuarios.
- Un usuario puede poseer varias pólizas; cada póliza pertenece a un usuario, una aseguradora y un vehículo.
- Una póliza puede relacionarse con varios siniestros.
- Un siniestro puede estar sin responsable o asignado a un usuario ajustador; la regla del rol se comprueba en la aplicación.
- Un siniestro puede tener muchas evidencias, mensajes y entradas de historial, pero como máximo una evaluación.
- Cada mensaje conserva su autor y su siniestro, independientemente de reasignaciones posteriores.

### Campos de multimedia y chat

| Campo | Tipo SQL | Uso |
| --- | --- | --- |
| `multimedia.tipo` | `ENUM('Foto','Video') NOT NULL` | Clasificación de la evidencia |
| `multimedia.archivo` | `LONGBLOB NULL` | Contenido binario |
| `multimedia.ruta` | `VARCHAR(255) NULL` | Compatibilidad con archivos antiguos |
| `multimedia.siniestro_id` | `INT UNSIGNED NOT NULL` | Expediente relacionado |
| `chat.mensaje` | `TEXT NOT NULL` | Texto; la aplicación limita a 2000 caracteres |
| `chat.fechamensaje` | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP` | Fecha/hora del servidor |
| `chat.siniestro_id` | `INT UNSIGNED NOT NULL` | Conversación a la que pertenece |
| `chat.usuario_id` | `INT UNSIGNED NOT NULL` | Emisor autenticado |
| `chat.mensaje_padre` | `INT UNSIGNED NULL` | Relación opcional, no usada por la conversación lineal actual |


## 7. Procedimientos almacenados

| Procedimiento | Función | Integración actual |
| --- | --- | --- |
| `sp_validar_usuario` | Busca alias/contraseña de una cuenta activa y devuelve su rol | Inicio de sesión |
| `sp_registrar_usuario` | Inserta un usuario con el rol localizado por slug `asegurado` | Registro público |
| `sp_verPoliza` | Consulta pólizas de un usuario mediante `vPolizas` | Listado y formulario de reporte |
| `sp_InsertarPoliza` | Inserta una relación de póliza | Procedimiento heredado; el registro del asegurado ahora usa transacción PDO en `PolizaM` |
| `sp_listar_usuarios` | Consulta datos básicos de usuarios | Definido en SQL; sin pantalla administrativa conectada |
| `sp_eliminar_usuario` | Cambia `estatus` a 0; no borra la fila | Definido en SQL; sin ruta de baja activa |

El chat, las evidencias y la gestión actual de siniestros utilizan consultas preparadas de los modelos, sin procedimientos nuevos dedicados al chat.
_Ya me dolió la mano_
## 8. Vistas SQL

| Vista | Datos que presenta |
| --- | --- |
| `vLogIn` | Usuario y rol; el procedimiento actual de acceso consulta las tablas directamente |
| `vPolizas` | Póliza, propietario, vehículo y aseguradora |
| `vListarSiniestros` | Expediente enriquecido con cliente, vehículo, compañía, ajustador y estatus |
| `vHistorialEstatus` | Cambios de estatus y fechas; el modelo actual también dispone de una consulta directa para el historial |
| `vListarClientes` | Clientes con totales de pólizas y siniestros |
| `vListarAjustadores` | Ajustadores y cantidad de casos asignados |
| `vBusquedaAvanzada` | Datos para búsqueda; sin pantalla activa conectada en esta versión |
| `vDashboardAdmin` | Totales de siniestros por estatus |

## 9. Transacciones y reglas de negocio

| Operación | Regla implementada |
| --- | --- |
| Crear reporte | La póliza pertenece al usuario; reporte y evidencias se confirman juntos |
| Asignar responsable | Caso existente no cerrado; usuario activo con rol ajustador; se bloquea el caso durante el cambio |
| Cambiar estatus | El supervisor administra casos abiertos; el ajustador solo sus casos asignados; rechaza expedientes cerrados y agrega historial solo si cambia |
| Guardar evaluación | Inserta o actualiza por la clave única de siniestro |
| Consultar/enviar chat | Autorización actual en SQL dentro de una transacción coordinada con reasignación |
| Convertir video antiguo | Lee ruta restringida y verifica bytes/huella antes de confirmar |

El instalador retira `T_asignar_Ajust` y `T_historial_estatus`. La asignación ya no es aleatoria y el historial se registra desde PHP. No hay un disparador nuevo que asigne automáticamente un responsable o un estatus inicial.

## 10. Fuentes de esta referencia

Se revisaron `index.php`, `config.php`, los controladores y modelos activos, `lib`, las vistas principales, los scripts del cliente, las pruebas y el instalador SQL contenidos en `RegistroSiniestrosFinal.rar`. Los diagramas y descripciones representan ese código; no se infieren funciones a partir de archivos vacíos o de código comentado.
