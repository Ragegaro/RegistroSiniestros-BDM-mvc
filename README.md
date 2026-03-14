# RegistroSiniestros-BDM-mvc
Pagina de aseguradora para registro de siniestros y subida de multimedia utilizando html,css,js,php,mysql y el Modelo Vista Controlador (MVC)

Las fotos del siniestro se guardan en BLOB por requisitos de evaluacion, pero eso puede generar una BD muy pesada 

 En la Tabla siniestro en el atributo direccion se optó por que un tipo TEXT por simiplicidad académica, pero en produccion se recomienda Latitud y Logitud en DECIMAL via API de google mpasmaps



 Proxima corrección: unificar el contnedor de siniestro y poliza en un solo y cam biar el contenido segun su pagina