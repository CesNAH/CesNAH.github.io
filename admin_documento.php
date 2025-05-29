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
    /* Tamaño de letra uniforme para botones y enlace eliminar */
    button, 
    a[style*="background-color: #a94a4a"] {
      font-size: 14px;
      cursor: pointer;
    }

    /* Estilos para los botones superiores */
    .top-buttons {
      position: absolute;
      top: 10px;
      right: 10px;
      display: flex;
      gap: 10px;
      z-index: 999;
    }

    .top-buttons a {
      background-color: #3498db;
      color: white;
      padding: 6px 12px;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 14px;
      font-family: Arial, sans-serif;
    }

    .top-buttons a.logout {
      background-color: #e74c3c;
    }

    .top-buttons a:hover {
      opacity: 0.9;
    }

    /* Tamaño iconos */
    .top-buttons svg, 
    button svg {
      width: 16px;
      height: 16px;
      fill: white;
    }

    /* Botones en la tabla */
    td button {
      background-color: #3498db;
      border: none;
      color: white;
      padding: 5px 10px;
      margin-right: 4px;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 13px;
      font-family: Arial, sans-serif;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    td button:hover {
      background-color: #2980b9;
    }

    /* Botón eliminar con fondo rojo */
    td a.eliminar {
      background-color: #a94a4a;
      color: white !important;
      padding: 5px 10px;
      border-radius: 4px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 13px;
      font-family: Arial, sans-serif;
      cursor: pointer;
      border: none;
      transition: background-color 0.2s ease;
    }
    td a.eliminar:hover {
      background-color: #7f3232;
    }

    /* Iconos en botones tabla */
    td svg {
      width: 14px;
      height: 14px;
      fill: white;
    }
  </style>
</head>
<body>

  <!-- Botones arriba a la derecha -->
  <div class="top-buttons">
    <a href="index_admin.php" title="Inicio">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
      Inicio
    </a>
    <a href="admin_usuario.php" title="Usuarios">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5s-3 1.34-3 3 1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C14 14.17 9.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
      Usuarios
    </a>
    <a href="login.php" class="logout" title="Cerrar sesión">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 13v-2H7V8l-5 4 5 4v-3zM19 3h-7v2h7v14h-7v2h7a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
      Cerrar sesión
    </a>
  </div>

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
      <tbody>
        <?php
        $result = $conn->query("SELECT * FROM documentos ORDER BY $orderSQL");
        if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
            echo "<td>" . htmlspecialchars($row['fecha']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre_archivo']) . "</td>";
            echo "<td>";
            // Botón Ver
            echo '<form style="display:inline;" method="get" action="ver_documento.php">';
            echo '<input type="hidden" name="id" value="'. $row['id'] .'">';
            echo '<button type="submit" title="Ver">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 6a9.77 9.77 0 0 0-9.97 9.44 10 10 0 0 0 19.94 0A9.77 9.77 0 0 0 12 6zm0 14a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9zm0-7a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/></svg>';
            echo 'Ver</button>';
            echo '</form> ';

            // Botón Imprimir
            echo '<form style="display:inline;" method="get" action="imprimir_documento.php" target="_blank">';
            echo '<input type="hidden" name="id" value="'. $row['id'] .'">';
            echo '<button type="submit" title="Imprimir">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 8h-1V3H6v5H5a2 2 0 0 0-2 2v6h4v4h8v-4h4v-6a2 2 0 0 0-2-2zM8 5h8v3H8V5zm8 12H8v-5h8v5z"/></svg>';
            echo 'Imprimir</button>';
            echo '</form> ';

            // Botón Eliminar
            echo '<a href="admin_documento.php?eliminar='. $row['id'] .'" onclick="return confirm(\'¿Está seguro de eliminar este documento?\');" class="eliminar" title="Eliminar">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M16 9v10H8V9h8m-1.5-6h-5l-1 1H5v2h14V4h-4.5l-1-1z"/></svg>';
            echo 'Eliminar</a>';

            echo "</td>";
            echo "</tr>";
          }
        } else {
          echo '<tr><td colspan="4">No hay documentos disponibles.</td></tr>';
        }
        ?>
      </tbody>
    </table>
  </div>
</body>
</html>
