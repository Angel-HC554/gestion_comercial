document.addEventListener("DOMContentLoaded", function () {

    // Aplica máscara de kilometraje a todos los inputs con clase .mask-km
    var kilometrajeInputs = document.querySelectorAll('.mask-km');
    for (var i = 0; i < kilometrajeInputs.length; i++) {
        IMask(kilometrajeInputs[i], {
            mask: Number,// sin decimales
            thousandsSeparator: ',',     // ← cambia a '.' si quieres 32.145
            signed: false,               // no permite signo negativo
            min: 0,
        });
    }
});