<?php
/**
 * ============================================================================
 * VERIFICAR SESIÓN ACTIVA
 * ============================================================================
 */
session_start();

// Redirigir a login si no está logueado
if (!isset($_SESSION['logueado']) || !$_SESSION['logueado']) {
    header('Location: login.html');
    exit;
}

$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="imagenes/phone.svg" type="image/svg+xml">
        <link rel="stylesheet" type="text/css" href="estilos/styles_index.css"> 
        <script src="https://unpkg.com/feather-icons"></script>
        <title>Contactos</title>
    </head>
    <body>
        <div id="toast-container"></div>
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Menú</h3>
            </div>
            <br>
            <!---------------------------------------------SIDEBAR------------------------------------------------------>
            <!---------------------------------------------------------------------------------------------------------->
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <button id="crear-usuario-menu-btn" class="boton-sidebar"><i data-feather="user-plus"></i></button>
                        <div id="form-crear-usuario-panel" class="sidebar-submenu">
                            <br><h3 style="text-align: center;">Crear Nuevo Contacto</h3><br>
                            <form id="form-crear-usuario">
                                <div class="filtro-campo">
                                    <label for="nombre">Nombre:</label>
                                    <input type="text" id="nombre" name="nombre" required>
                                </div>
                                <div class="filtro-campo">
                                    <label for="telefono">Teléfono:</label>
                                    <input type="number" id="telefono" name="telefono">
                                </div>
                                <div class="filtro-campo">
                                    <label for="email">Email:</label>
                                    <input type="email" id="email" name="email">
                                </div>
                                <div class="filtro-campo">
                                    <label for="fecha_cumple">Fecha de Cumpleaños:</label>
                                    <input type="date" id="fecha_cumple" name="fecha_cumple"> 
                                </div>
                                <button type="submit">Crear Usuario</button>
                            </form>
                        </div>
                    </li>
                    <li>
                        <button id="filtrar-menu-btn" class="boton-sidebar"><i data-feather="filter"></i></button>
                        <div id="filtrar-opciones" class="sidebar-submenu">
                            <div class="filtro-campo">
                                <br>
                                <label for="filtro-dia-menu">Día:</label>
                                <input type="number" id="filtro-dia-menu" min="1" max="31" placeholder="DD">
                            </div>
                            <div class="filtro-campo">
                                <label for="filtro-mes-menu">Mes:</label>
                                <select id="filtro-mes-menu">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Ene">Ene</option>
                                    <option value="Feb">Feb</option>
                                    <option value="Mar">Mar</option>
                                    <option value="Abr">Abr</option>
                                    <option value="May">May</option>
                                    <option value="Jun">Jun</option>
                                    <option value="Jul">Jul</option>
                                    <option value="Ago">Ago</option>
                                    <option value="Sep">Sep</option>
                                    <option value="Oct">Oct</option>
                                    <option value="Nov">Nov</option>
                                    <option value="Dic">Dic</option>
                                </select>
                            </div>
                            <div class="filtro-campo">
                                <label for="filtro-anio-menu">Año:</label>
                                <input type="number" id="filtro-anio-menu" placeholder="AAAA">
                            </div>
                            <br>
                            <button id="limpiar-filtro-fecha" class="boton-limpiar">Limpiar Filtros</button>
                        </div>
                    </li>
                    <li>
                        <button id="ordenar-menu-btn" class="boton-sidebar"><i data-feather="list"></i></button>
                        <div id="ordenar-opciones" class="sidebar-submenu">
                            <br><h3 style="text-align: center;">Ordenar Por</h3><br>
                            <button id="ordenar-nombre-btn">Nombre (A-Z)</button>
                            <div><br></div>
                            <button id="ordenar-fecha-btn">Fecha de Cumpleaños</button>
                        </div>
                    </li>
                    <li>
                        <button id="exportar-menu-btn" class="boton-sidebar"><i data-feather="upload"></i></button>
                    </li>
                    <li>
                    <a href="php/logout.php" class="boton-sidebar" title="Cerrar Sesión">
                        <i data-feather="log-out"></i>
                    </a>
                </li>
                </ul>
            </nav>
        </aside>

        <!---------------------------------------------BUSCADOR Y CARDS------------------------------------------------------>
        <!---------------------------------------------------------------------------------------------------------->
        <main>
            <div class="search-wrapper">
                <div class="input-holder">
                    <input type="text" class="search-input" placeholder="Buscar contactos..." id="buscador-menu" />
                    <button class="search-icon" aria-label="Buscar / Cerrar" id="search-toggle">
                        <span id="search-icon-wrapper"></span>
                        <i data-feather="search" id="search-icon"></i>
                    </button>
                </div>
            </div>
            <div id="lista-usuarios"></div>    
        </main>

        <script src="script.js"></script>
        <script>
            // Inicializar Feather Icons después de cargar el DOM
            feather.replace();
        </script>
    </body>
</html>