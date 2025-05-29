<?php include("conexion.php"); ?>

<?php
// Subida del documento
if (filter_has_var(INPUT_POST, 'titulo')) {
    $titulo = $_POST['titulo'];
    $fecha = $_POST['fecha'];
    $archivo = $_FILES['archivo'];

    if ($archivo['error'] === UPLOAD_ERR_OK) {
        $nombreOriginal = basename($archivo['name']);
        $tipoArchivo = $archivo['type'];
        $rutaDestino = "documentos/" . $nombreOriginal;

        if (!is_dir("documentos")) {
            mkdir("documentos", 0777, true);
        }

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            $stmt = $conn->prepare("INSERT INTO documentos (titulo, fecha, nombre_archivo, tipo_archivo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $titulo, $fecha, $nombreOriginal, $tipoArchivo);
            $stmt->execute();
            $stmt->close();
            echo "<script>alert('Documento agregado exitosamente');</script>";
        } else {
            echo "<script>alert('Error al subir el archivo.');</script>";
        }
    } else {
        echo "<script>alert('Error con el archivo subido.');</script>";
    }
}

// Eliminación del documento
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $res = $conn->query("SELECT nombre_archivo FROM documentos WHERE id = $id");
    if ($res && $doc = $res->fetch_assoc()) {
        $archivoPath = "documentos/" . $doc['nombre_archivo'];
        if (file_exists($archivoPath)) {
            unlink($archivoPath);
        }
    }
    $conn->query("DELETE FROM documentos WHERE id = $id");
    echo "<script>alert('Documento eliminado correctamente'); window.location='admin_documento.php';</script>";
}

// Determinar orden para la consulta
$orderSQL = "fecha DESC"; // orden por defecto

if (isset($_GET['orden'])) {
    switch ($_GET['orden']) {
        case 'fechaAsc':
            $orderSQL = "fecha ASC";
            break;
        case 'fechaDesc':
            $orderSQL = "fecha DESC";
            break;
        case 'nombreAsc':
            $orderSQL = "titulo ASC";
            break;
        case 'nombreDesc':
            $orderSQL = "titulo DESC";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Administración de Documentos</title>
  <link rel="stylesheet" href="css/admin_documentos.css" />
  <style>
    button, 
    a[style*="background-color: #a94a4a"] {
      font-size: 14px;
    }

    .btn-logout {
      position: absolute;
      top: 15px;
      right: 15px;
      background-color: #c0392b;
      color: white;
      padding: 8px 16px;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      z-index: 999;
    }

    .btn-logout:hover {
      background-color: #a93226;
    }
  </style>
</head>
<body>

  <!-- Botón cerrar sesión fijo arriba a la derecha -->
  <a href="logout.php" class="btn-logout">Cerrar sesión</a>

  <div class="admin-container">
    <h2>Administración de Documentos</h2>

    <form id="formDocumento" method="POST" enctype="multipart/form-data">
      <input type="text" name="titulo" placeholder="Nombre del documento" required />
      <input type="date" name="fecha" required />
      <input 
        type="file" 
        name="archivo" 
        accept=".pdf,.doc,.docx,.xls,.xlsx" 
        required 
      />
      <button type="submit">Agregar Documento</button>
    </form>

    <div class="filtros">
      <label for="ordenar">Ordenar por:</label>
      <select id="ordenar" name="ordenar" onchange="location = '?orden=' + this.value;">
        <option value="fechaAsc" <?php if(isset($_GET['orden']) && $_GET['orden']=='fechaAsc') echo 'selected'; ?>>Fecha Ascendente</option>
        <option value="fechaDesc" <?php if(isset($_GET['orden']) && $_GET['orden']=='fechaDesc') echo 'selected'; ?>>Fecha Descendente</option>
        <option value="nombreAsc" <?php if(isset($_GET['orden']) && $_GET['orden']=='nombreAsc') echo 'selected'; ?>>Nombre A-Z</option>
        <option value="nombreDesc" <?php if(isset($_GET['orden']) && $_GET['orden']=='nombreDesc') echo 'selected'; ?>>Nombre Z-A</option>
      </select>
    </div>

    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Fecha</th>
          <th>Archivo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="documentosTable">
        <?php
        $resultado = $conn->query("SELECT * FROM documentos ORDER BY $orderSQL");

        while ($doc = $resultado->fetch_assoc()) {
            $urlArchivo = "documentos/" . $doc['nombre_archivo'];
            $titulo = htmlspecialchars($doc['titulo'], ENT_QUOTES);
            $fecha = htmlspecialchars($doc['fecha'], ENT_QUOTES);
            $archivoNombre = htmlspecialchars($doc['nombre_archivo'], ENT_QUOTES);

            echo "<tr>
                    <td>{$titulo}</td>
                    <td>{$fecha}</td>
                    <td><a href='{$urlArchivo}' target='_blank' rel='noopener noreferrer' style='background-color: transparent; color: black; text-decoration: underline;'>{$archivoNombre}</a></td>
                    <td>
                      <button onclick=\"window.open('{$urlArchivo}', '_blank')\">Ver</button>
                      <button onclick=\"imprimirDocumento('{$urlArchivo}')\">Imprimir</button>
                      <a href='?eliminar={$doc['id']}'
                         onclick='return confirm(\"¿Deseas eliminar este documento?\")'
                         style='background-color: #a94a4a; color: white; padding: 6px 12px; border-radius: 5px; text-decoration: none;'>Eliminar</a>
                    </td>
                  </tr>";
        }

        $conn->close();
        ?>
      </tbody>
    </table>
  </div>

  <script>
    function imprimirDocumento(url) {
      const printWindow = window.open(url, '_blank');
      if (printWindow) {
        printWindow.focus();
        printWindow.print();
      } else {
        alert('Por favor, permite ventanas emergentes para imprimir.');
      }
    }
  </script>
</body>
</html>
