<?php
session_start();
require_once 'conexion.php';

// Validar que el usuario esté logueado y sea docente (2) o administrador (3)
if (!isset($_SESSION["usuario"]["nUsuario"]) || !isset($_SESSION["usuario"]["nRol"])) {
    header("Location: login.php");
    exit();
}

$usuarioId = intval($_SESSION["usuario"]["nUsuario"]);
$usuarioRol = intval($_SESSION["usuario"]["nRol"]);
if ($usuarioId <= 0 || ($usuarioRol !== 2 && $usuarioRol !== 3)) {
    die("Acceso no autorizado: sólo docentes y administradores pueden acceder.");
}

$usuarioNombre = $_SESSION["usuario"]["cNombres"] ?? "Docente";

// Obtener listado de hongos
try {
    $sql = "SELECT 
                m.mCodigo, m.mNombreComun, m.mNombreCientifico, m.mMuestra, m.mColoracion, m.mFoto,
                cDom.cConstDescripcion AS cDominio,
                cRei.cConstDescripcion AS cReino,
                cFil.cConstDescripcion AS cFilo,
                cSubf.cConstDescripcion AS cSubfilo,
                cSupc.cConstDescripcion AS cSuperclase,
                cCla.cConstDescripcion AS cClase,
                cSubc.cConstDescripcion AS cSubclase,
                cOrd.cConstDescripcion AS cOrden,
                cFam.cConstDescripcion AS cFamilia,
                cGen.cConstDescripcion AS cGenero,
                cEsp.cConstDescripcion AS cEspecie
            FROM microorganismo m
            LEFT JOIN constante cDom ON m.fkDominio = cDom.tCodigo
            LEFT JOIN constante cRei ON m.fkReino = cRei.tCodigo
            LEFT JOIN constante cFil ON m.fkFilo = cFil.tCodigo
            LEFT JOIN constante cSubf ON m.fkSubfilo = cSubf.tCodigo
            LEFT JOIN constante cSupc ON m.fkSuperclase = cSupc.tCodigo
            LEFT JOIN constante cCla ON m.fkClase = cCla.tCodigo
            LEFT JOIN constante cSubc ON m.fkSubclase = cSubc.tCodigo
            LEFT JOIN constante cOrd ON m.fkOrden = cOrd.tCodigo
            LEFT JOIN constante cFam ON m.fkFamilia = cFam.tCodigo
            LEFT JOIN constante cGen ON m.fkGenero = cGen.tCodigo
            LEFT JOIN constante cEsp ON m.fkEspecie = cEsp.tCodigo
            WHERE m.mTipo = 'Hongo'
            ORDER BY m.mCodigo DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $hongos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar hongos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Hongos | Laboratorio Clínico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/docente.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Estilos específicos para la sección Hongo */
        .hongos-container {
            margin-top: 20px;
        }
        .page-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .hongo-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .hongo-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hongo-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }
        .hongo-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #f1f5f9;
        }
        .hongo-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .hongo-title {
            font-size: 1.2rem;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .hongo-scientific {
            font-size: 0.95rem;
            font-style: italic;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        .hongo-tax-badge-container {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
        }
        .tax-badge {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 2px 8px;
            font-size: 0.75rem;
            border-radius: 9999px;
            font-weight: 500;
        }
        .tax-badge.secondary {
            background: #f0f9ff;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        
        /* Estilos del Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            padding: 20px;
        }
        .modal-content-box {
            background: #ffffff;
            width: 100%;
            max-width: 750px;
            max-height: 90vh;
            border-radius: var(--radius-lg);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            animation: modalFadeIn 0.3s ease;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border-top-left-radius: var(--radius-lg);
            border-top-right-radius: var(--radius-lg);
        }
        .modal-header h3 {
            margin: 0;
            font-size: 1.3rem;
            color: var(--text-dark);
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s;
        }
        .close-btn:hover {
            color: #ef4444;
        }
        .modal-body {
            padding: 24px;
            overflow-y: auto;
            flex-grow: 1;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media(max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        .form-group-full {
            grid-column: 1 / -1;
        }
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-dark);
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        .btn-submit-hongo {
            background: var(--emerald-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-submit-hongo:hover {
            background: #059669;
        }
        .collapsible-trigger {
            background: #f1f5f9;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
            padding: 10px;
            border-radius: var(--radius-md);
            cursor: pointer;
            margin-top: 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            font-size: 0.9rem;
            user-select: none;
        }
        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0, 1, 0, 1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .collapsible-content.expanded {
            max-height: 1000px;
            transition: max-height 0.3s cubic-bezier(1, 0, 1, 0);
            padding: 10px 0;
        }
        @media(max-width: 600px) {
            .collapsible-content {
                grid-template-columns: 1fr;
            }
        }
        .input-new-tax {
            border: 1px dashed var(--emerald-color);
            background: #f0fdf4;
        }
        .input-new-tax::placeholder {
            color: #86efac;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <a href="index.php" class="logo" title="Laboratorio Clínico IESTP Trujillo">
                <img src="imagenes/logo.jpg" alt="Logo Laboratorio">
            </a>
            <nav class="nav-menu" id="nav-menu">
                <ul class="nav-list">
                    <li class="nav-item"><a href="index.php" class="nav-link">Inicio</a></li>
                    <li class="nav-item"><a href="muestras.php" class="nav-link">Gestión de Muestras</a></li>
                    <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="contactanos.php" class="nav-link">Contáctanos</a></li>
                </ul>
                <div class="header-user-actions">
                    <span class="user-pill-tag"><i class="fas fa-chalkboard-teacher"></i> Docente: <?php echo htmlspecialchars($usuarioNombre); ?></span>
                    <a href="logout.php" class="btn-logout-nav" title="Cerrar Sesión"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container portal-container">
        <div class="management-wrapper">
            <!-- Sidebar Docente -->
            <aside class="management-sidebar">
                <div class="sidebar-teacher-profile">
                    <div class="profile-avatar"><i class="fas fa-microscope"></i></div>
                    <div class="profile-info">
                        <h4><?php echo htmlspecialchars($usuarioNombre); ?></h4>
                        <span>Docente de Laboratorio</span>
                    </div>
                </div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="muestras.php#panel-dashboard" class="sidebar-btn">
                            <i class="fas fa-chart-line sidebar-icon"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="hongos.php" class="sidebar-btn active">
                            <i class="fas fa-mushroom sidebar-icon" style="font-family: 'Font Awesome 6 Free'; font-weight: 900;">🍄</i>
                            <span>Hongos</span>
                        </a>
                    </li>
                    <li>
                        <a href="muestras.php#panel-familias" class="sidebar-btn">
                            <i class="fas fa-sitemap sidebar-icon"></i>
                            <span>Familias</span>
                        </a>
                    </li>
                    <li>
                        <a href="muestras.php#panel-generos" class="sidebar-btn">
                            <i class="fas fa-folder-tree sidebar-icon"></i>
                            <span>Géneros</span>
                        </a>
                    </li>
                    <li>
                        <a href="muestras.php#panel-especies" class="sidebar-btn">
                            <i class="fas fa-dna sidebar-icon"></i>
                            <span>Especies</span>
                        </a>
                    </li>
                    <li>
                        <a href="muestras.php#panel-pruebas" class="sidebar-btn">
                            <i class="fas fa-vial sidebar-icon"></i>
                            <span>Pruebas y Cultivos</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Contenido Principal -->
            <section class="management-content hongos-container">
                <div class="page-header-row">
                    <div>
                        <h2>Catálogo de Hongos 🍄</h2>
                        <p style="color: var(--text-muted);">Administra la clasificación taxonómica y características de los especímenes de hongos registrados.</p>
                    </div>
                    <button class="btn-submit-hongo" id="btn-abrir-modal">
                        <i class="fas fa-plus"></i> Añadir nuevo Hongo
                    </button>
                </div>

                <!-- Listado de Hongos -->
                <?php if (empty($hongos)): ?>
                    <div class="dashboard-welcome-box" style="text-align: center; padding: 40px;">
                        <h3>No hay hongos registrados</h3>
                        <p>Haz clic en "Añadir nuevo Hongo" para ingresar el primero al catálogo.</p>
                    </div>
                <?php else: ?>
                    <div class="hongo-card-grid">
                        <?php foreach ($hongos as $hongo): ?>
                            <div class="hongo-card">
                                <?php if (!empty($hongo['mFoto'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($hongo['mFoto']); ?>" alt="Foto hongo" class="hongo-img">
                                <?php else: ?>
                                    <div class="hongo-img" style="display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 3rem;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="hongo-info">
                                    <h3 class="hongo-title">
                                        <span><?php echo htmlspecialchars($hongo['mNombreComun'] ?: 'Sin nombre común'); ?></span>
                                        <small style="color: var(--text-muted); font-size: 0.8rem;">#<?php echo $hongo['mCodigo']; ?></small>
                                    </h3>
                                    <span class="hongo-scientific"><?php echo htmlspecialchars($hongo['mNombreCientifico'] ?: 'Sin nombre científico'); ?></span>
                                    
                                    <div style="font-size: 0.85rem; margin-top: 5px;">
                                        <strong>Muestra:</strong> <?php echo htmlspecialchars($hongo['mMuestra'] ?: 'No especificada'); ?><br>
                                        <strong>Coloración:</strong> <?php echo htmlspecialchars($hongo['mColoracion'] ?: 'No especificada'); ?>
                                    </div>

                                    <div class="hongo-tax-badge-container">
                                        <?php if ($hongo['cDominio']): ?><span class="tax-badge" title="Dominio">Dom: <?php echo htmlspecialchars($hongo['cDominio']); ?></span><?php endif; ?>
                                        <?php if ($hongo['cReino']): ?><span class="tax-badge" title="Reino">Rei: <?php echo htmlspecialchars($hongo['cReino']); ?></span><?php endif; ?>
                                        <?php if ($hongo['cFilo']): ?><span class="tax-badge" title="Filo">Fil: <?php echo htmlspecialchars($hongo['cFilo']); ?></span><?php endif; ?>
                                        <?php if ($hongo['cSubfilo']): ?><span class="tax-badge secondary" title="Subfilo">Subf: <?php echo htmlspecialchars($hongo['cSubfilo']); ?></span><?php endif; ?>
                                        <?php if ($hongo['cSuperclase']): ?><span class="tax-badge secondary" title="Superclase">Supc: <?php echo htmlspecialchars($hongo['cSuperclase']); ?></span><?php endif; ?>
                                        <?php if ($hongo['cClase']): ?><span class="tax-badge" title="Clase">Cla: <?php echo htmlspecialchars($hongo['cClase']); ?></span><?php endif; ?>
                                        <?php if ($hongo['cSubclase']): ?><span class="tax-badge secondary" title="Subclase">Subc: <?php echo htmlspecialchars($hongo['cSubclase']); ?></span><?php endif; ?>
                                        <?php if ($hongo['cOrden']): ?><span class="tax-badge" title="Orden">Ord: <?php echo htmlspecialchars($hongo['cOrden']); ?></span><?php endif; ?>
                                        <?php if ($hongo['cFamilia']): ?><span class="tax-badge" title="Familia">Fam: <?php echo htmlspecialchars($hongo['cFamilia']); ?></span><?php endif; ?>
                                        <?php if ($hongo['cGenero']): ?><span class="tax-badge" title="Género">Gen: <?php echo htmlspecialchars($hongo['cGenero']); ?></span><?php endif; ?>
                                        <?php if ($hongo['cEspecie']): ?><span class="tax-badge" title="Especie">Esp: <?php echo htmlspecialchars($hongo['cEspecie']); ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Modal Formulario Hongo -->
    <div class="modal-overlay" id="modal-hongo">
        <div class="modal-content-box">
            <div class="modal-header">
                <h3>Añadir Nuevo Hongo 🔬</h3>
                <button type="button" class="close-btn" id="btn-cerrar-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-nuevo-hongo" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="mNombreComun">Nombre común</label>
                            <input class="form-control" type="text" name="mNombreComun" id="mNombreComun" placeholder="Ej: Champiñón, Moho de pan">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="mNombreCientifico">Nombre científico</label>
                            <input class="form-control" style="font-style: italic;" type="text" name="mNombreCientifico" id="mNombreCientifico" placeholder="Ej: Saccharomyces cerevisiae">
                        </div>
                        <div class="form-group-full">
                            <label class="form-label" for="mFoto">Imagen / Foto de cultivo</label>
                            <input class="form-control" type="file" name="mFoto" id="mFoto" accept="image/*">
                        </div>

                        <!-- Niveles Taxonómicos Principales -->
                        <div class="form-group">
                            <label class="form-label" for="fkDominio">Dominio</label>
                            <select class="form-control select-tax" name="fkDominio" id="fkDominio" data-grupo="2">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input class="form-control input-new-tax" type="text" name="new_fkDominio" id="new_fkDominio" placeholder="Escribir nuevo Dominio..." style="display: none; margin-top: 8px;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="fkReino">Reino</label>
                            <select class="form-control select-tax" name="fkReino" id="fkReino" data-grupo="3">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input class="form-control input-new-tax" type="text" name="new_fkReino" id="new_fkReino" placeholder="Escribir nuevo Reino..." style="display: none; margin-top: 8px;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="fkFilo">Filo</label>
                            <select class="form-control select-tax" name="fkFilo" id="fkFilo" data-grupo="4">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input class="form-control input-new-tax" type="text" name="new_fkFilo" id="new_fkFilo" placeholder="Escribir nuevo Filo..." style="display: none; margin-top: 8px;">
                        </div>

                        <!-- Muestra y Coloración -->
                        <div class="form-group">
                            <label class="form-label" for="mMuestra">Muestra</label>
                            <select class="form-control select-tax" name="mMuestra" id="mMuestra" data-grupo="1">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input class="form-control input-new-tax" type="text" name="new_mMuestra" id="new_mMuestra" placeholder="Escribir nueva Muestra..." style="display: none; margin-top: 8px;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="mColoracion">Coloración</label>
                            <select class="form-control select-tax" name="mColoracion" id="mColoracion" data-grupo="15">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input class="form-control input-new-tax" type="text" name="new_mColoracion" id="new_mColoracion" placeholder="Escribir nueva Coloración..." style="display: none; margin-top: 8px;">
                        </div>

                        <!-- Sección colapsable para otros niveles taxonómicos -->
                        <div class="form-group-full">
                            <div class="collapsible-trigger" id="btn-toggle-extra">
                                <span><i class="fas fa-sliders"></i> Mostrar más niveles taxonómicos (Subfilo, Superclase, Subclase)</span>
                                <i class="fas fa-chevron-down" id="chevron-extra"></i>
                            </div>
                            
                            <div class="collapsible-content" id="extra-taxonomias">
                                <div class="form-group" style="margin-top: 10px;">
                                    <label class="form-label" for="fkSubfilo">Subfilo</label>
                                    <select class="form-control select-tax" name="fkSubfilo" id="fkSubfilo" data-grupo="5">
                                        <option value="">-- Seleccionar --</option>
                                    </select>
                                    <input class="form-control input-new-tax" type="text" name="new_fkSubfilo" id="new_fkSubfilo" placeholder="Escribir nuevo Subfilo..." style="display: none; margin-top: 8px;">
                                </div>
                                <div class="form-group" style="margin-top: 10px;">
                                    <label class="form-label" for="fkSuperclase">Superclase</label>
                                    <select class="form-control select-tax" name="fkSuperclase" id="fkSuperclase" data-grupo="6">
                                        <option value="">-- Seleccionar --</option>
                                    </select>
                                    <input class="form-control input-new-tax" type="text" name="new_fkSuperclase" id="new_fkSuperclase" placeholder="Escribir nueva Superclase..." style="display: none; margin-top: 8px;">
                                </div>
                                <div class="form-group" style="margin-top: 10px; grid-column: 1 / -1;">
                                    <label class="form-label" for="fkSubclase">Subclase</label>
                                    <select class="form-control select-tax" name="fkSubclase" id="fkSubclase" data-grupo="8">
                                        <option value="">-- Seleccionar --</option>
                                    </select>
                                    <input class="form-control input-new-tax" type="text" name="new_fkSubclase" id="new_fkSubclase" placeholder="Escribir nueva Subclase..." style="display: none; margin-top: 8px;">
                                </div>
                            </div>
                        </div>

                        <!-- Resto de niveles taxonómicos estándar -->
                        <div class="form-group">
                            <label class="form-label" for="fkClase">Clase</label>
                            <select class="form-control select-tax" name="fkClase" id="fkClase" data-grupo="7">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input class="form-control input-new-tax" type="text" name="new_fkClase" id="new_fkClase" placeholder="Escribir nueva Clase..." style="display: none; margin-top: 8px;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="fkOrden">Orden</label>
                            <select class="form-control select-tax" name="fkOrden" id="fkOrden" data-grupo="9">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input class="form-control input-new-tax" type="text" name="new_fkOrden" id="new_fkOrden" placeholder="Escribir nuevo Orden..." style="display: none; margin-top: 8px;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="fkFamilia">Familia</label>
                            <select class="form-control select-tax" name="fkFamilia" id="fkFamilia" data-grupo="10">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input class="form-control input-new-tax" type="text" name="new_fkFamilia" id="new_fkFamilia" placeholder="Escribir nueva Familia..." style="display: none; margin-top: 8px;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="fkGenero">Género</label>
                            <select class="form-control select-tax" name="fkGenero" id="fkGenero" data-grupo="11">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input class="form-control input-new-tax" type="text" name="new_fkGenero" id="new_fkGenero" placeholder="Escribir nuevo Género..." style="display: none; margin-top: 8px;">
                        </div>

                        <div class="form-group-full">
                            <label class="form-label" for="fkEspecie">Especie</label>
                            <select class="form-control select-tax" name="fkEspecie" id="fkEspecie" data-grupo="12">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input class="form-control input-new-tax" type="text" name="new_fkEspecie" id="new_fkEspecie" placeholder="Escribir nueva Especie..." style="display: none; margin-top: 8px;">
                        </div>
                    </div>

                    <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 15px;">
                        <button type="button" class="form-control" style="width: auto; background: #e2e8f0; border: none; font-weight: 600;" id="btn-cancelar">Cancelar</button>
                        <button type="submit" class="btn-submit-hongo">Guardar Hongo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modal-hongo');
            const btnAbrir = document.getElementById('btn-abrir-modal');
            const btnCerrar = document.getElementById('btn-cerrar-modal');
            const btnCancelar = document.getElementById('btn-cancelar');
            const form = document.getElementById('form-nuevo-hongo');
            const btnToggleExtra = document.getElementById('btn-toggle-extra');
            const extraTaxonomias = document.getElementById('extra-taxonomias');
            const chevronExtra = document.getElementById('chevron-extra');

            // Abrir y cerrar modal
            btnAbrir.addEventListener('click', () => {
                modal.style.display = 'flex';
                cargarSelects();
            });
            const cerrarModal = () => {
                modal.style.display = 'none';
                form.reset();
                document.querySelectorAll('.input-new-tax').forEach(inp => inp.style.display = 'none');
            };
            btnCerrar.addEventListener('click', cerrarModal);
            btnCancelar.addEventListener('click', cerrarModal);

            // Toggle colapsable
            btnToggleExtra.addEventListener('click', () => {
                const isExpanded = extraTaxonomias.classList.toggle('expanded');
                chevronExtra.style.transform = isExpanded ? 'rotate(180deg)' : 'rotate(0deg)';
            });

            // Función para cargar los <select> vía AJAX
            function cargarSelects() {
                document.querySelectorAll('.select-tax').forEach(select => {
                    const grupo = select.dataset.grupo;
                    // Guardar el valor seleccionado actual si existe
                    const currentValue = select.value;

                    // Limpiar pero mantener el placeholder inicial
                    select.innerHTML = '<option value="">-- Seleccionar --</option>';

                    fetch(`controladores/get_constante.php?grupo=${grupo}`)
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === 'ok') {
                                res.data.forEach(item => {
                                    const opt = document.createElement('option');
                                    opt.value = item.tCodigo;
                                    opt.textContent = item.cConstDescripcion;
                                    select.appendChild(opt);
                                });

                                // Opción especial para agregar nuevo
                                const optNew = document.createElement('option');
                                optNew.value = '__NEW__';
                                optNew.textContent = '+ Agregar nuevo valor';
                                optNew.style.fontWeight = 'bold';
                                optNew.style.color = '#10b981';
                                select.appendChild(optNew);

                                // Restaurar selección previa si aplica
                                if (currentValue) {
                                    select.value = currentValue;
                                }
                            }
                        })
                        .catch(err => console.error("Error al cargar constantes del grupo " + grupo, err));
                });
            }

            // Escuchar cambios en los selects para desplegar input de nuevo valor
            document.querySelectorAll('.select-tax').forEach(select => {
                select.addEventListener('change', function() {
                    const inputNew = document.getElementById('new_' + this.id);
                    if (this.value === '__NEW__') {
                        inputNew.style.display = 'block';
                        inputNew.required = true;
                        inputNew.focus();
                    } else {
                        inputNew.style.display = 'none';
                        inputNew.required = false;
                        inputNew.value = '';
                    }
                });
            });

            // Guardar Formulario
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);

                Swal.fire({
                    title: 'Guardando...',
                    text: 'Por favor espere mientras registramos el hongo.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('controladores/guardar_hongo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'ok') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Red',
                        text: 'No se pudo conectar con el servidor.'
                    });
                });
            });
        });
    </script>
</body>
</html>
