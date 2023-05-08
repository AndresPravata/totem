// Obtener los elementos del DOM que necesitamos
const formulario = document.querySelector('form');
const nombreInput = document.querySelector('#nombre');
const emailInput = document.querySelector('#email');
const mensajeInput = document.querySelector('#mensaje');

// Agregar un evento de escucha al formulario para enviar el mensaje
formulario.addEventListener('submit', (event) => {
  event.preventDefault(); // Prevenir el comportamiento por defecto del formulario

  // Validar los campos del formulario
  if (nombreInput.value === '' || emailInput.value === '' || mensajeInput.value === '') {
    alert('Por favor, completa todos los campos del formulario');
    return;
  }

  // Simular el envío del mensaje mediante una alerta
  alert(`Gracias por tu mensaje, ${nombreInput.value}! Nos pondremos en contacto contigo pronto.`);
});
