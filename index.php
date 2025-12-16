<?php
/**
 * Listado público de eventos
 *
 * Permite filtrar por fecha, departamento y municipio. Construye la consulta
 * con parámetros seguros y muestra tarjetas por cada evento.
 */
require_once './proceso/db.php';

$fecha = $_GET['fecha'] ?? '';
$departamento = $_GET['departamento'] ?? '';
$municipio = $_GET['municipio'] ?? '';

$sql = "SELECT * FROM eventos WHERE 1=1";
$params = [];
$types = "";

if ($fecha) {
    // Filtro exacto por fecha de inicio
    $sql .= " AND fecha_inicio = ?";
    $params[] = $fecha;
    $types .= "s";
}

if ($departamento) {
    // Búsqueda parcial por departamento
    $sql .= " AND departamento LIKE ?";
    $params[] = "%$departamento%";
    $types .= "s";
}

if ($municipio) {
    // Búsqueda parcial por municipio
    $sql .= " AND municipio LIKE ?";
    $params[] = "%$municipio%";
    $types .= "s";
}

$sql .= " ORDER BY fecha_inicio, hora_inicio";

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$eventos = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eventos Disponibles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Cabecera con navegación mínima -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Eventos Colombia</a>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login/login.html">Mis Compras</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./login/login.html">Iniciar sesión</a>
                    </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">

    <h1 class="mb-4">Eventos disponibles</h1>

    <!-- Filtros de búsqueda -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Fecha</label>
            <input type="date" name="fecha" class="form-control" value="<?= $fecha ?>">
        </div>

        <div class="col-md-3">
            <label>Departamento</label>
            <input type="text" name="departamento" class="form-control" value="<?= $departamento ?>">
        </div>

        <div class="col-md-3">
            <label>Municipio</label>
            <input type="text" name="municipio" class="form-control" value="<?= $municipio ?>">
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100">Filtrar</button>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Quitar filtros
        </a>

    </form>

    <!-- Listado de resultados -->
    <?php while ($evento = $eventos->fetch_assoc()): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h4><?= htmlspecialchars($evento['nombre']) ?></h4>
                <p><?= htmlspecialchars($evento['descripcion']) ?></p>

                <p>
                    📅 <?= $evento['fecha_inicio'] ?> |
                    ⏰ <?= $evento['hora_inicio'] ?> - <?= $evento['hora_fin'] ?>
                </p>

                <p>
                    📍 <?= htmlspecialchars($evento['municipio']) ?>,
                    <?= htmlspecialchars($evento['departamento']) ?><br>
                    🏟️ <?= htmlspecialchars($evento['lugar']) ?>
                </p>

                <!-- Acción de compra condicionada a sesión -->
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="comprar.php?id=<?= $evento['id'] ?>" class="btn btn-success">
                        Comprar boletas
                    </a>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        Inicia sesión para comprar boletas
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>

</div>

</body>
</html>
