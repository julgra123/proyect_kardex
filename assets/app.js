document.querySelectorAll('[data-print]').forEach(button => button.addEventListener('click', () => window.print()));
// El docente evaluador se propone a partir de la materia seleccionada.
const materia = document.querySelector('[data-materia-docente]');
const docente = document.getElementById('id_docente');
if (materia && docente) materia.addEventListener('change', () => {
    const option = materia.selectedOptions[0];
    if (option && option.dataset.docente) docente.value = option.dataset.docente;
});
