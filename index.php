<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./CSS/style.css?v=<?php echo time(); ?>">
    <title>Formulario de Confirmación</title>

</head>

<body>
    <div class="fondoImg">
    </div>
    <div class="imagenFondo">
        <img src="./assets/FONDO FORMULARIO DESKTOP.png" alt="">

    </div>

    <div class="contenedor">


        <div>
            <h1 class="textoC tituloh1">MAYRA Y PANCHO</h1>
            <h4 class="textoC rosaFont subtituloh4">FORMULARIO DE ASISTENCIA /<br> ATTENDANCE FORM</h4>
        </div>


        <form id="confirmacionForm" action="procesar.php" method="POST" class="formatoinvi">
            <div class="headtext">
                <h4 class="textoC">POR FAVOR, CONFIRMA TU ASISTENCIA SELECCIONANDO<br> UNA DE LAS SIGUIENTES OPCIONES</h4>
                <h4 class="textoC azulFont">PLEASE CONFIRM YOU ATTENDANCE AT THE EVENT BY<br> SELECTING ONE OF THE FOLLOW OPTIONS.</h4>
                <h4 class="textoC">¡GRACIAS!</h4>
                <h4 class="textoC azulFont">THANK YOU!</h4>
            </div>
            <div class="camposForm">
                <div class="nombreTelefono">
                    <div class="form-group l50">
                        <label for="nombre" class="imputText">NOMBRE Y APELLIDOS:</label>
                        <input class="imp50 " type="text" id="nombre" name="nombre" required placeholder="FULL NAME">
                    </div>

                    <div class="form-group l50">
                        <label for="telefono_movil" class="imputText">TELÉFONO</label><br>
                        <input class="imp50"
                            type="tel"
                            id="telefono"
                            name="telefono"
                            pattern="(\+34|0034|34)?[ -]*(6|7|8|9)[ -]*([0-9][ -]*){8}"
                            placeholder="PHONE NUMBER"
                            title="Introduce un número de teléfono válido. Puede incluir +34, espacios y guiones"
                            required>
                    </div>
                </div>

                <div class="form-group emailinvi">
                    <label for="email" class="imputText">CORREO ELECTRÓNICO</label>
                    <input type="email" id="email" name="email" required placeholder="EMAIL">
                </div>
            </div>
            <div class="form-group">
                <div class="radio-group">
                    <input type="radio" id="asistire" name="asistencia" value="asistire" required>
                    <label for="asistire"><strong>ALLÍ ESTARÉ /</strong><strong class="azulFont"> I'LL BE THERE</strong> </label>
                </div>

                <div id="personasOpciones" class="personas-opciones">
                    <div class="radio-group">
                        <input type="radio" id="persona1" name="personas" value="1">
                        <label for="persona1"><strong>1 PERSONA /</strong><strong class="azulFont"> 1 PERSON</strong></label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="persona2" name="personas" value="2">
                        <label for="persona2"><strong>2 PERSONAS /</strong><strong class="azulFont"> 2 PEOPLE</strong></label>
                    </div>
                    <div class="error-message" id="errorPersonas">
                        Por favor, selecciona el número de personas
                    </div>
                </div>

                <div class="radio-group">
                    <input type="radio" id="no_asistire" name="asistencia" value="no_asistire">
                    <label for="no_asistire"><strong>LO SIENTO, NO PODRÉ ACUDIR /</strong><strong class="azulFont"> SORRY, I WON'T BE<br> ABLE TO ATTEND</strong></label>
                </div>

                <div class="radio-group">
                    <input type="radio" id="quizas" name="asistencia" value="quizas">
                    <label for="quizas"><strong>LO INTENTARÉ, PERO AÚN NO ESTOY SEGURO DE<br> PODER ASISTIR /</strong><strong class="azulFont"> I'LL TRY TO ATTEND, BUT I'M NOT SURE<br> YET.</strong></label>
                </div>
                <div class="error-message" id="errorAsistencia">
                    Por favor, selecciona una opción de asistencia
                </div>
            </div>

            <button type="submit">Enviar</button>
    </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('confirmacionForm');
            const asistireRadio = document.getElementById('asistire');
            const personasOpciones = document.getElementById('personasOpciones');
            const errorPersonas = document.getElementById('errorPersonas');
            const errorAsistencia = document.getElementById('errorAsistencia');

            // Función para mostrar/ocultar opciones de personas
            function togglePersonasOpciones() {
                personasOpciones.style.display = asistireRadio.checked ? 'block' : 'none';
                // Si no está seleccionado "Allí estaré", deseleccionar las opciones de personas
                if (!asistireRadio.checked) {
                    document.querySelectorAll('input[name="personas"]').forEach(radio => {
                        radio.checked = false;
                    });
                }
                errorPersonas.style.display = 'none';
            }

            // Escuchar cambios en los radio buttons de asistencia
            document.querySelectorAll('input[name="asistencia"]').forEach(radio => {
                radio.addEventListener('change', togglePersonasOpciones);
            });

            // Validación del formulario
            form.addEventListener('submit', function(event) {
                let isValid = true;

                // Validar que se haya seleccionado una opción de asistencia
                const asistenciaSeleccionada = document.querySelector('input[name="asistencia"]:checked');
                if (!asistenciaSeleccionada) {
                    errorAsistencia.style.display = 'block';
                    isValid = false;
                } else {
                    errorAsistencia.style.display = 'none';
                }

                // Si seleccionó "Allí estaré", validar que haya seleccionado número de personas
                if (asistireRadio.checked) {
                    const personasSeleccionadas = document.querySelector('input[name="personas"]:checked');
                    if (!personasSeleccionadas) {
                        errorPersonas.style.display = 'block';
                        isValid = false;
                    } else {
                        errorPersonas.style.display = 'none';
                    }
                }

                if (!isValid) {
                    event.preventDefault(); // Evita que el formulario se envíe
                }
            });
        });
    </script>
</body>

</html>