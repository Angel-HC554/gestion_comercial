{{-- resources/views/components/vehicle-form.blade.php --}}

<style>
    /* Estilos generales y para el contenedor principal */
    .form-container {
        padding: 25px;
        background-color: #ffffff;
    }

    .form-container h2 {
        text-align: center;
        color: #333;
        margin-bottom: 25px;
        font-weight: bold;
    }

    /* Contenedor de la cuadrícula para las fotos */
    .photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    /* Estilo para cada campo de subida de archivo */
    .upload-box {
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.3s, background-color 0.3s;
        height: 150px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .upload-box:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }

    .upload-box label {
        color: #555;
        font-size: 14px;
        font-weight: bold;
    }

    .upload-box input[type="file"] {
        display: none;
    }

    .upload-box .thumbnail {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
        border-radius: 6px;
    }

    .summary-section textarea {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        font-size: 16px;
        min-height: 120px;
        resize: vertical;
        box-sizing: border-box;
    }

    .submit-btn {
        margin-left: auto;
        margin-right: auto;
        display: block;
        width: 50%;
        padding: 15px;
        background-color: rgb(5 150 105);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 25px;
        transition: background-color 0.3s;
    }

    .submit-btn:hover {
        background-color: rgb(4 120 87);
    }
</style>

<div class="form-container">
    <div class="form-group" style="margin-bottom: 25px;">
        <label style="font-weight: bold; color: #016f2b; display: block; margin-bottom: 8px;">Vehiculo: <span
                style="font-weight: bold; color: black;">{{ $no_economico}}</span></label>
    </div>
    <h2 style="color: #016f2b;"> Ingresa las fotos del vehiculo:</h2>


    <form id="vehicleForm" action="/supervision-semanal" method="post"
        enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="no_eco" value="{{ $no_economico }}">
        <input type="hidden" name="vehiculo_id" value="{{ $vehiculo_id }}">
        <div class="photo-grid">
            <div class="upload-box" onclick="document.getElementById('delantera').click()"><label>FOTO VEHÍCULO
                    DELANTERO</label><input type="file" id="delantera" name="foto_del" accept="image/*"
                    onchange="previewImage(event, 'thumbnail-delantera')"><img class="thumbnail"
                    id="thumbnail-delantera"></div>
            <div class="upload-box" onclick="document.getElementById('trasera').click()"><label>FOTO VEHÍCULO
                    TRASERO</label><input type="file" id="trasera" name="foto_tra" accept="image/*"
                    onchange="previewImage(event, 'thumbnail-trasera')"><img class="thumbnail" id="thumbnail-trasera">
            </div>
            <div class="upload-box" onclick="document.getElementById('lado_izquierdo').click()"><label>FOTO VEHÍCULO
                    LADO IZQUIERDO</label><input type="file" id="lado_izquierdo" name="foto_lado_izq"
                    accept="image/*" onchange="previewImage(event, 'thumbnail-izquierdo')"><img class="thumbnail"
                    id="thumbnail-izquierdo"></div>
            <div class="upload-box" onclick="document.getElementById('lado_derecho').click()"><label>FOTO VEHÍCULO LADO
                    DERECHO</label><input type="file" id="lado_derecho" name="foto_lado_der" accept="image/*"
                    onchange="previewImage(event, 'thumbnail-derecho')"><img class="thumbnail" id="thumbnail-derecho">
            </div>
            <div class="upload-box" onclick="document.getElementById('poliza').click()"><label>FOTO PÓLIZA
                    SEGURO</label><input type="file" id="poliza" name="foto_poliza" accept="image/*"
                    onchange="previewImage(event, 'thumbnail-poliza')"><img class="thumbnail" id="thumbnail-poliza">
            </div>
            <div class="upload-box" onclick="document.getElementById('circulacion').click()"><label>FOTO TARJETA
                    CIRCULACIÓN</label><input type="file" id="circulacion" name="foto_tar_circ" accept="image/*"
                    onchange="previewImage(event, 'thumbnail-circulacion')"><img class="thumbnail"
                    id="thumbnail-circulacion"></div>
            <div class="upload-box" onclick="document.getElementById('kit').click()"><label>FOTO DEL KIT</label><input
                    type="file" id="kit" name="foto_kit" accept="image/*"
                    onchange="previewImage(event, 'thumbnail-kit')"><img class="thumbnail" id="thumbnail-kit"></div>
            <div class="upload-box" onclick="document.getElementById('atentado').click()"><label>FOTO SI CUENTA CON
                    ATENTADO</label><input type="file" id="atentado" name="foto_atent" accept="image/*"
                    onchange="previewImage(event, 'thumbnail-atentado')"><img class="thumbnail" id="thumbnail-atentado">
            </div>
                <div class="upload-box" onclick="document.getElementById('llanta_refaccion').click()"><label>FOTO LLANTA
                    REFACCION</label><input type="file" id="llanta_refaccion" name="foto_llanta_ref" accept="image/*"
                    onchange="previewImage(event, 'thumbnail-llanta-ref')"><img class="thumbnail" id="thumbnail-llanta-ref">
                </div>
        </div>

        <div class="summary-section">
            <label for="vehicle_summary" style="font-weight:bold; color:#016f2b;">Resumen del estado del
                vehiculo:</label>
            <textarea id="vehicle_summary" name="resumen_est" rows="5" class="focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 border-2 border-zinc-300"
                placeholder="Describe aquí el estado general del vehículo..."></textarea>
        </div>
        <div id="progreso-subida" style="display: none; margin-top: 15px;">
            <p style="font-weight: bold; color: #333;">Subiendo... <span id="porcentaje">0</span>%</p>
            <progress id="barra-progreso" value="0" max="100"
                style="width: 100%; height: 20px;"></progress>
        </div>
        <button type="submit" class="submit-btn shadow-sm">Guardar</button>
    </form>
</div>

<script>
    function previewImage(event, thumbnailId) {
        const input = event.target;
        const thumbnail = document.getElementById(thumbnailId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                thumbnail.src = e.target.result;
                thumbnail.style.display = 'block';
                input.parentElement.querySelector('label').style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // 1. Selecciona los elementos (USA LOS IDs DE TU FORMULARIO)
    const formulario = document.getElementById('vehicleForm');
    const barraProgreso = document.getElementById('barra-progreso');
    const porcentajeTexto = document.getElementById('porcentaje');
    const divProgreso = document.getElementById('progreso-subida');
    const botonEnviar = formulario.querySelector('button.submit-btn');

    // 2. "Escucha" el evento 'submit'
    formulario.addEventListener('submit', function(event) {

        // 3. ¡Evita el envío normal!
        event.preventDefault();

        // 4. Muestra la barra y deshabilita el botón
        divProgreso.style.display = 'block';
        botonEnviar.disabled = true;
        botonEnviar.innerText = 'Enviando...';

        // 5. Prepara los datos del formulario (funciona con tu @csrf y todo)
        const datosFormulario = new FormData(formulario);

        // 6. Crea la petición AJAX
        const xhr = new XMLHttpRequest();

        // 7. Configura el evento de PROGRESO (la magia)
        xhr.upload.onprogress = function(event) {
            if (event.lengthComputable) {
                // Calcula el porcentaje
                const porcentaje = Math.round((event.loaded / event.total) * 100);

                // Actualiza la barra y el texto
                barraProgreso.value = porcentaje;
                porcentajeTexto.innerText = porcentaje;
            }
        };

        // 8. Configura el evento de ÉXITO (cuando termina)
        xhr.onload = function() {

            let response;
            try {
                // Intenta "leer" la respuesta de texto como un objeto JSON
                response = JSON.parse(xhr.responseText);
            } catch (e) {
                // Si la respuesta no es JSON (ej. un error 500 de PHP)
                Swal.fire('Error', 'Respuesta inesperada del servidor.', 'error');
                botonEnviar.disabled = false;
                botonEnviar.innerText = 'Guardar';
                divProgreso.style.display = 'none';
                return;
            }

            // CASO DE ÉXITO (status 200-299)
            if (xhr.status >= 200 && xhr.status < 300) {

                // Oculta la barra de progreso
                divProgreso.style.display = 'none';

                // ¡AQUÍ ESTÁ LA MAGIA!
                // Llama a SweetAlert desde JavaScript
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: response.message, // Usa el mensaje que vino del controlador
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // 👇 ESTE CÓDIGO SE EJECUTA DESPUÉS DE QUE EL TIMER TERMINA
                    window.location.reload();
                });

                // Reactiva el botón
                botonEnviar.disabled = false;
                botonEnviar.innerText = 'Guardar';

                // Resetea el formulario
                formulario.reset();

                // Limpia las miniaturas de las fotos (bonus)
                document.querySelectorAll('.thumbnail').forEach(img => {
                    img.style.display = 'none';
                    img.src = '';
                    // Muestra la etiqueta de nuevo
                    img.parentElement.querySelector('label').style.display = 'block';
                });

            } else {
                // CASO DE ERROR (ej. 422 Validación o 500 Error)

                // Oculta la barra de progreso
                divProgreso.style.display = 'none';
                // Reactiva el botón
                botonEnviar.disabled = false;
                botonEnviar.innerText = 'Guardar';

                // 'message' es el error principal
                let errorMessage = response.message || 'Hubo un error.';

                // Si es un error de validación 422, 'errors' tendrá los detalles
                if (response.errors) {
                    errorMessage = Object.values(response.errors).flat().join('\n');
                }

                Swal.fire('Error', errorMessage, 'error');
            }
        };

        // 9. Configura el evento de ERROR (de red)
        xhr.onerror = function() {
            botonEnviar.disabled = false;
            botonEnviar.innerText = 'Guardar';
            divProgreso.style.display = 'none'; // Oculta la barra
            alert('Error de red. Revisa tu conexión.');
        };

        // 10. Abre y envía la petición (usa el 'action' y 'method' de tu form)
        xhr.open(formulario.method, formulario.action);
        xhr.send(datosFormulario);
    });
</script>
