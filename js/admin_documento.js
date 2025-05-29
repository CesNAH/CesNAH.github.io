document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formDocumento");
  const tabla = document.getElementById("documentosTable");
  const ordenarSelect = document.getElementById("ordenar");

  // Función para crear fila en tabla
  function crearFila(documento) {
    const tr = document.createElement("tr");

    // Nombre
    const tdNombre = document.createElement("td");
    tdNombre.textContent = documento.titulo;
    tr.appendChild(tdNombre);

    // Fecha
    const tdFecha = document.createElement("td");
    tdFecha.textContent = documento.fecha;
    tr.appendChild(tdFecha);

    // Archivo (enlace)
    const tdArchivo = document.createElement("td");
    const enlace = document.createElement("a");
    enlace.href = documento.url;
    enlace.textContent = documento.archivoNombre;
    enlace.target = "_blank";
    enlace.rel = "noopener noreferrer";
    tdArchivo.appendChild(enlace);
    tr.appendChild(tdArchivo);

    // Acciones (imprimir y eliminar)
    const tdAcciones = document.createElement("td");

    // Botón imprimir
    const btnImprimir = document.createElement("button");
    btnImprimir.classList.add("action-btn");
    btnImprimir.textContent = "Imprimir";
    btnImprimir.addEventListener("click", () => {
      imprimirDocumento(documento.url);
    });
    tdAcciones.appendChild(btnImprimir);

    // Botón eliminar
    const btnEliminar = document.createElement("button");
    btnEliminar.classList.add("action-btn", "delete-btn");
    btnEliminar.textContent = "Eliminar";
    btnEliminar.addEventListener("click", () => {
      if (confirm(`¿Eliminar el documento "${documento.titulo}"?`)) {
        tr.remove();
      }
    });
    tdAcciones.appendChild(btnEliminar);

    tr.appendChild(tdAcciones);

    return tr;
  }

  // Función para imprimir documento PDF
  function imprimirDocumento(url) {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <html>
        <head>
          <title>Imprimir Documento</title>
          <style>
            body, html { margin:0; padding:0; height:100%; }
            iframe { border:none; width:100%; height:100vh; }
          </style>
        </head>
        <body>
          <iframe src="${url}" onload="this.contentWindow.focus(); this.contentWindow.print();"></iframe>
        </body>
      </html>
    `);
    printWindow.document.close();
  }

  // Enviar formulario
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const titulo = form.titulo.value.trim();
    const fecha = form.fecha.value;
    const archivoInput = form.archivo;

    if (!titulo || !fecha || archivoInput.files.length === 0) {
      alert("Por favor completa todos los campos y selecciona un archivo.");
      return;
    }

    const archivo = archivoInput.files[0];
    const archivoNombre = archivo.name;

    // Crear URL temporal para el archivo
    const archivoURL = URL.createObjectURL(archivo);

    // Crear objeto documento
    const nuevoDocumento = {
      titulo,
      fecha,
      archivoNombre,
      url: archivoURL,
    };

    // Añadir fila a la tabla
    const nuevaFila = crearFila(nuevoDocumento);
    tabla.appendChild(nuevaFila);

    // Resetear formulario
    form.reset();
  });

  // Ordenar tabla
  function ordenarTabla(criterio) {
    const filas = Array.from(tabla.querySelectorAll("tr"));

    filas.sort((a, b) => {
      const nombreA = a.children[0].textContent.toLowerCase();
      const nombreB = b.children[0].textContent.toLowerCase();
      const fechaA = a.children[1].textContent;
      const fechaB = b.children[1].textContent;

      switch (criterio) {
        case "fechaAsc":
          return new Date(fechaA) - new Date(fechaB);
        case "fechaDesc":
          return new Date(fechaB) - new Date(fechaA);
        case "nombreAsc":
          return nombreA.localeCompare(nombreB);
        case "nombreDesc":
          return nombreB.localeCompare(nombreA);
        default:
          return 0;
      }
    });

    filas.forEach(fila => tabla.appendChild(fila));
  }

  ordenarSelect.addEventListener("change", (e) => {
    ordenarTabla(e.target.value);
  });
});
