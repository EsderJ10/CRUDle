# GESTIÓN BÁSICA DE USUARIOS

## OBJETIVO

Desarrollar una aplicación web sencilla que permita realizar las operaciones CRUD sobre un recurso llamado usuario.

## DESCRIPCIÓN GENERAL

Crear un conjunto de scripts PHP que gestionen un listado de usuarios almacenados en un archivo CSV (no se usará base de datos). 
Cada usuario tendrá al menos los siguientes campos:

- id (numérico incremental)
- nombre
- email
- rol (por ejemplo: “administrador”, “editor”, “visitante”)
- fecha de alta

El sistema debe permitir crear, listar, ver, editar y eliminar usuarios mediante los siguientes archivos:

- user_index.php --> Muestra la lista de usuarios con opciones para ver, editar o eliminar.
- user_create.php --> Formulario para crear un nuevo usuario y guardarlo en el CSV.
- user_info.php --> Muestra los datos detallados de un usuario concreto.
- user_edit.php --> Permite modificar los datos de un usuario existente.
- user_delete.php --> Elimina un usuario del archivo CSV.

## REQUISITOS FUNCIONALES

- El listado (user_index.php) debe mostrar los usuarios en una tabla HTML.
    -   Cada fila incluirá botones o enlaces para “Ver”, “Editar” y “Eliminar”.
- Los formularios (create y edit) deben validar los datos mínimos:
    - El email debe tener formato válido.
    - Ningún campo puede estar vacío.
- Las operaciones deben actualizar el archivo usuarios.csv de forma persistente.
- Se debe mantener un estilo visual claro y ordenado.
    - Se permite el uso de frameworks CSS como Materialize, Bootstrap o Tailwind.

- (Opcional) Añadir confirmaciones en las operaciones sensibles, como la eliminación.

### EXTRAS OPCIONALES

- Añadir un campo de avatar (imagen) con subida de ficheros.



