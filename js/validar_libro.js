// Valida los campos del formulario de libro antes de enviarlos al servidor PHP
const formLibro = document.getElementById('formLibro');

if (formLibro) {
    formLibro.addEventListener('submit', function (e) {
        let esValido = true;

        const titulo     = document.getElementById('titulo_libro');
        const isbn       = document.getElementById('isbn');
        const categoria  = document.getElementById('id_categoria');
        const editorial  = document.getElementById('id_editorial');
        const idioma     = document.getElementById('id_idioma');
        const autor      = document.getElementById('id_autor');
        const paginas    = document.getElementById('paginas');
        const ejemplares = document.getElementById('ejemplares');

        const campos = [titulo, isbn, categoria, editorial, idioma, autor, paginas, ejemplares];
        campos.forEach(function (campo) {
            if (campo) {
                campo.classList.remove('is-invalid');
            }
        });

        if (titulo.value.trim() === '') {
            marcarError(titulo, 'feedback-titulo', 'Por favor ingresa el título del libro.');
            esValido = false;
        } else if (titulo.value.trim().length < 3) {
            marcarError(titulo, 'feedback-titulo', 'El título debe tener al menos 3 caracteres.');
            esValido = false;
        }

        if (isbn.value.trim() === '') {
            marcarError(isbn, 'feedback-isbn', 'Por favor ingresa el código ISBN.');
            esValido = false;
        }

        if (categoria.value === '0' || categoria.value === '') {
            marcarError(categoria, 'feedback-categoria', 'Selecciona una categoría.');
            esValido = false;
        }

        if (editorial.value === '0' || editorial.value === '') {
            marcarError(editorial, 'feedback-editorial', 'Selecciona una editorial.');
            esValido = false;
        }

        if (idioma.value === '0' || idioma.value === '') {
            marcarError(idioma, 'feedback-idioma', 'Selecciona un idioma.');
            esValido = false;
        }

        if (autor.value === '0' || autor.value === '') {
            marcarError(autor, 'feedback-autor', 'Selecciona el autor del libro.');
            esValido = false;
        }

        if (paginas && paginas.value.trim() !== '') {
            const valPaginas = parseInt(paginas.value, 10);
            if (isNaN(valPaginas) || valPaginas <= 0) {
                marcarError(paginas, 'feedback-paginas', 'El número de páginas debe ser mayor a 0.');
                esValido = false;
            }
        }

        if (ejemplares) {
            const valEjemplares = parseInt(ejemplares.value, 10);
            if (ejemplares.value.trim() === '' || isNaN(valEjemplares) || valEjemplares < 0) {
                marcarError(ejemplares, 'feedback-ejemplares', 'Ingresa una cantidad de ejemplares mayor o igual a 0.');
                esValido = false;
            }
        }

        if (!esValido) {
            e.preventDefault();
        }
    });
}

// Marca un campo en rojo con su mensaje descriptivo usando las clases de Bootstrap
function marcarError(input, idFeedback, mensaje) {
    input.classList.add('is-invalid');
    const contenedor = document.getElementById(idFeedback);
    if (contenedor) {
        contenedor.textContent = mensaje;
    }
}

// Actualiza el contador de caracteres de la sinopsis mientras escribe el bibliotecario
const campoSinopsis = document.getElementById('sinopsis');
const contadorSinopsis = document.getElementById('contador-sinopsis');
if (campoSinopsis && contadorSinopsis) {
    contadorSinopsis.textContent = campoSinopsis.value.length + ' / 1,000 caracteres';
    campoSinopsis.addEventListener('input', function () {
        contadorSinopsis.textContent = campoSinopsis.value.length + ' / 1,000 caracteres';
    });
}