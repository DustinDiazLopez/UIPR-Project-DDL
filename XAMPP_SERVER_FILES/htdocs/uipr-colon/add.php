<?php


include_once('templates/header.php');



// is-valid class for green
// <div class="valid-feedback">Looks good!</div>
// is-invalid class for red
//<div class="invalid-feedback">Please choose a username.</div>
?>

<link rel="stylesheet" href="css/autocomplete.css">

<form autocomplete="off" style="color:black;" action="#" method="POST">
    <!-- TITLE AND TYPE -->
    <div class="form-row">
        <div class="col-md-6 mb-3">
            <label for="validationServer02">Título</label>
            <input type="text" id="title" name="title" placeholder="Don Quijote de la Mancha" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3 autocomplete">
            <label for="type">Tipo de Documento
                <a style="color:blue;" data-toggle="tooltip" data-placement="right" title="Los tipos disponibles son: <?php foreach (query(SQL_GET_DOC_TYPES) as $type) echo htmlspecialchars($type['type']) . ', '; ?>. Si no existe uno cual describe su artículo lo puede añadir y luego estará como opción en el sistema.">
                    ?
                </a>

            </label>
            <input type="text" id="type" name="type" placeholder="Documento, Libro, etc." class="form-control">

        </div>
    </div>


    <!-- PUB DATE -->
    <div class="form-row">
        <label for="example-date-input" class="col-5 col-form-label">Fecha de Publicación:</label>
        <div class="col-7">
            <input type="date" name="published_date" id="published_date" class="form-control" name="published_date" value="1940-12-25" required>
        </div>
    </div>

    <hr />
    <!-- AUTHORS -->
    <div class="form-row">
        <label for="authors">Autores <a style="color:blue;" data-toggle="tooltip" data-placement="right" title="Favor no utilizar commas, especificar el nombre completo sin commas. Si se detectan commas, se eliminarán.">?</a></label>
        <div class="input-group mb-3">
            <input class="form-control" type="text" placeholder="" id="authors" name="authors" readonly required>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="deleteReadonly('authors')" title="Borrar todos los autores."><i class="far fa-trash-alt"></i></button>
                <button class="btn btn-outline-secondary" type="button" onclick="deleteLastReadonly('authors')" title="Borrar el último autor/a entrado/a."><i class="fas fa-backspace"></i></button>
            </div>
        </div>
        <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="Miguel de Cervante" aria-label="Nombre del autor" aria-describedby="basic-addon2" id="authorInput">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="addToReadonly('authorInput', 'authors')" title="Añadir sujeto"><i class="fas fa-plus"></i></button>
            </div>
        </div>
    </div>
    <hr />

    <!-- SUBJECTS -->
    <div class="form-row">
        <label for="subjects">Sujetos <a style="color:blue;" data-toggle="tooltip" data-placement="right" title="Favor no utilizar commas, especificar el sujeto sin commas. Si se detectan commas, se eliminarán.">?</a></label>
        <div class="input-group mb-3">
            <input class="form-control" type="text" placeholder="" id="subjects" name="subjects" readonly required>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="deleteReadonly('subjects')" title="Borrar todos los sujetos."><i class="far fa-trash-alt"></i></button>
                <button class="btn btn-outline-secondary" type="button" onclick="deleteLastReadonly('subjects')" title="Borrar el último sujeto entrado."><i class="fas fa-backspace"></i></button>
            </div>
        </div>
        <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="Caballerias" aria-label="Sujetos del articulo" aria-describedby="basic-addon2" id="subjectsInput">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="addToReadonly('subjectsInput', 'subjects')" title="Añadir sujeto"><i class="fas fa-plus"></i></button>
            </div>
        </div>
    </div>
    <hr />

    <!-- DESCRIPTION -->
    <div class="form-group">
        <label for="exampleFormControlTextarea1">Descripción del artículo</label>
        <textarea class="form-control" id="description" rows="3"></textarea>
    </div>

    <!-- METADATA -->
    <div class="form-group">
        <label for="exampleFormControlTextarea1">Metadata
            <a style="color:blue;" data-toggle="tooltip" data-placement="right" title="Información que no será visible, pero que se utilizará en la búsqueda (por ejemplo, texto completo del artículo, otros títulos, etc.). En otras palabras, cualquier información relacionada con el artículo.">
                ?
            </a>
        </label>
        <textarea class="form-control" id="metadata" rows="3"></textarea>
    </div>

    <hr />
    <!-- IMAGE -->
    <div class="form-row">
        <label for="image">Subir una Imagen <small>(MAX: 16 MB)</small> <a style="color:blue;" data-toggle="tooltip" data-placement="right" title="El máximo tamaño para la imagen es de 16 megabytes.">?</a>
        </label>
        <div class="col-xs-1 text-center">
            <input type="file" class="btn" id="image" name="image" required>
        </div>
    </div>
    <hr />
    <!-- FILES -->
    <div class="form-row">
        <label for="files">Seleccionar los Archivos <small>(MAX: 40 MB por archivo)</small> <a style="color:blue;" data-toggle="tooltip" data-placement="right" title="Puede seleccionar más de un archivo. El máximo tamaño para cada archivo es de 40 megabytes. Un archivo con un tamaño más grande podrá funcionar, pero NO SE RECOMIENDA.">?</a>
        </label>
        <div class="col-xs-1 text-center">
            <input type="file" class="btn" id="files" name="files" multiple="multiple" required>
        </div>
    </div>
    <hr />


    <button class="btn btn-primary" type="submit">Submit form</button>
</form>

<script charset="utf-8" src="js/jquery-3.2.1.slim.min.js"></script>
<script src="js/autocomplete.js"></script>

<script>
    /*An array containing all the country names in the world:*/
    const types = [
        <?php foreach (query(SQL_GET_DOC_TYPES) as $type) echo '"' . htmlspecialchars($type['type']) . '",'; ?>
    ];

    /*initiate the autocomplete function on the "myInput" element, and pass along the countries array as possible autocomplete values:*/
    autocomplete(document.getElementById("type"), types);

    function deleteLastReadonly(output) {
        const readonly = document.getElementById(output);
        if (readonly.value.trim() === "") return;
        let arr = readonly.value.trim().split(",");
        arr.pop()
        readonly.value = (arr + "").trim();
    }

    function deleteReadonly(output) {
        document.getElementById(output).value = "";
    }

    function addToReadonly(input, output) {
        const field = document.getElementById(input);
        const readonly = document.getElementById(output);
        let fieldVal = field.value.trim();
        if (fieldVal === "") {
            field.value = "";
            return;
        } else if (fieldVal.includes(',')) {
            field.value = field.value.replace(",", "");
            return;
        }

        if (readonly.value === "") {
            readonly.value += `${fieldVal}`
        } else {
            readonly.value += `, ${fieldVal}`
        }

        readonly.value = titleCase(readonly.value.trim());
        field.value = "";

        function titleCase(str) {
            let splitStr = str.toLowerCase().split(' ');
            for (i = 0; i < splitStr.length; i++)
                splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);

            return splitStr.join(' ');
        }
    }

    $(document).ready(function() {
        $(window).keydown(function(event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });
    });
</script>

<?php include_once('templates/footer.php'); ?>