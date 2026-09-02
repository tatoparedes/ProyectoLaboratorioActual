<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexion.php';

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Hongos | Cepario Virtual - IESTP Trujillo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/docente.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Estilos específicos para las Tarjetas del Catálogo y Modal Taxonómico */
        .catalog-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .catalog-title-box h2 {
            font-size: 1.6rem;
            color: #0f172a;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .catalog-title-box p {
            color: #64748b;
            font-size: 0.95rem;
        }
        .btn-add-primary {
            background-color: #10b981;
            color: #ffffff;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        .btn-add-primary:hover {
            background-color: #059669;
            transform: translateY(-2px);
        }
        
        /* Grid de Tarjetas de Hongos */
        .specimens-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 22px;
        }
        .specimen-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .specimen-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }
        .specimen-img-container {
            width: 100%;
            height: 180px;
            background-color: #f1f5f9;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .specimen-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .specimen-placeholder-icon {
            font-size: 3rem;
            color: #94a3b8;
        }
        .specimen-body {
            padding: 18px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .specimen-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }
        .specimen-common-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
        }
        .specimen-id-badge {
            color: #64748b;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .specimen-scientific-name {
            font-style: italic;
            color: #0284c7;
            font-size: 0.92rem;
            margin-bottom: 12px;
        }
        .specimen-details-list {
            font-size: 0.88rem;
            color: #334155;
            margin-bottom: 14px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .specimen-details-list strong {
            color: #1e293b;
        }
        .taxo-pills-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: auto;
            padding-top: 10px;
        }
        .taxo-pill {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 12px;
        }
        .specimen-actions-bar {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
        }

        /* Modal Personalizado */
        .modal-custom-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .modal-custom-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .modal-custom-card {
            background: #ffffff;
            border-radius: 20px;
            width: 100%;
            max-width: 680px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
        }
        .modal-custom-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-custom-header h3 {
            font-size: 1.3rem;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .modal-close-btn {
            background: transparent;
            border: none;
            font-size: 1.4rem;
            color: #64748b;
            cursor: pointer;
            transition: color 0.2s;
        }
        .modal-close-btn:hover { color: #0f172a; }
        .modal-custom-body {
            padding: 25px;
        }
        .form-grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-group-full {
            grid-column: 1 / -1;
        }
        .form-group-custom label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-group-custom input,
        .form-group-custom select {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        .form-group-custom input:focus,
        .form-group-custom select:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        .input-new-text {
            margin-top: 6px;
            display: none;
        }
        .accordion-toggle-btn {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0 15px 0;
        }
        .accordion-content {
            display: none;
        }
        .accordion-content.active {
            display: grid;
        }
        .modal-custom-footer {
            padding: 15px 25px 20px 25px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-top: 1px solid #e2e8f0;
        }
        .btn-cancel {
            background-color: #e2e8f0;
            color: #475569;
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-save {
            background-color: #10b981;
            color: #ffffff;
            padding: 10px 22px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-save:hover { background-color: #059669; }
    </style>
</head>
<body>
    <!-- Encabezado Principal -->
    <header class="header">
        <div class="container header-container">
            <a href="index.php" class="logo" title="Laboratorio Clínico IESTP Trujillo">
                <img src="imagenes/logo.jpg" alt="Logo Laboratorio">
            </a>
            <nav class="nav-menu" id="nav-menu">
                <ul class="nav-list">
                    <li class="nav-item"><a href="index.php" class="nav-link">Inicio</a></li>
                    <li class="nav-item"><a href="muestras.php" class="nav-link active">Gestión de Muestras</a></li>
                    <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="contactanos.php" class="nav-link">Contáctanos</a></li>
                </ul>
                <div class="header-user-actions">
                    <span class="user-pill-tag"><i class="fas fa-chalkboard-teacher"></i> Docente: <?php echo htmlspecialchars($usuarioNombre); ?></span>
                    <a href="logout.php" class="btn-logout-nav" title="Cerrar Sesión"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </nav>
            <div class="hamburger" id="hamburger" aria-label="Menú">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
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
                        <a href="vista_docente.php#panel-dashboard" class="sidebar-btn">
                            <i class="fas fa-chart-line sidebar-icon"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="hongos.php" class="sidebar-btn active">
                            <i class="fas fa-mushroom sidebar-icon" style="font-family: 'Font Awesome 6 Free'; font-weight: 900; font-style: normal;">🍄</i>
                            <span>Hongos</span>
                        </a>
                    </li>
                    <li>
                        <a href="parasitos.php" class="sidebar-btn">
                            <i class="fas fa-bug sidebar-icon"></i>
                            <span>Parásitos</span>
                        </a>
                    </li>
                    <li>
                        <a href="vista_docente.php#panel-familias" class="sidebar-btn">
                            <i class="fas fa-sitemap sidebar-icon"></i>
                            <span>Familias</span>
                        </a>
                    </li>
                    <li>
                        <a href="vista_docente.php#panel-generos" class="sidebar-btn">
                            <i class="fas fa-folder-tree sidebar-icon"></i>
                            <span>Géneros</span>
                        </a>
                    </li>
                    <li>
                        <a href="vista_docente.php#panel-especies" class="sidebar-btn">
                            <i class="fas fa-dna sidebar-icon"></i>
                            <span>Especies</span>
                        </a>
                    </li>
                    <li>
                        <a href="vista_docente.php#panel-pruebas" class="sidebar-btn">
                            <i class="fas fa-vial sidebar-icon"></i>
                            <span>Pruebas y Cultivos</span>
                        </a>
                    </li>
                    <li>
                        <a href="vista_docente.php#panel-examenes" class="sidebar-btn">
                            <i class="fas fa-file-signature sidebar-icon"></i>
                            <span>Crear Exámenes</span>
                        </a>
                    </li>
                    <li>
                        <a href="vista_docente.php#panel-revision-examenes" class="sidebar-btn">
                            <i class="fas fa-clipboard-check sidebar-icon"></i>
                            <span>Revisión de Exámenes</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Área de Contenido Principal -->
            <section class="management-content">
                <div class="catalog-header-flex">
                    <div class="catalog-title-box">
                        <h2>Catálogo de Hongos 🍄</h2>
                        <p>Administra la clasificación taxonómica y características de los especímenes de hongos registrados.</p>
                    </div>
                    <button type="button" class="btn-add-primary" onclick="abrirModalHongo()">
                        <i class="fas fa-plus"></i> Añadir nuevo Hongo
                    </button>
                </div>

                <!-- Grid de Especímenes (Cards) -->
                <div class="specimens-grid" id="grid-hongos">
                    <div style="grid-column: 1/-1; text-align:center; padding: 40px; color:#64748b;">
                        <i class="fas fa-spinner fa-spin fa-2x"></i><br><br>Cargando catálogo de hongos...
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Modal Flotante: Añadir / Editar Hongo -->
    <div class="modal-custom-overlay" id="modal-hongo">
        <div class="modal-custom-card">
            <div class="modal-custom-header">
                <h3 id="modal-titulo"><i class="fas fa-microscope"></i> Añadir Nuevo Hongo 🔬</h3>
                <button type="button" class="modal-close-btn" onclick="cerrarModalHongo()">&times;</button>
            </div>
            <form id="form-hongo" enctype="multipart/form-data">
                <input type="hidden" id="mCodigo" name="mCodigo" value="0">
                <div class="modal-custom-body">
                    <div class="form-grid-2col">
                        <!-- Subir Archivo Imagen -->
                        <div class="form-group-custom form-group-full">
                            <label for="mFoto">Fotografía Microscópica / Cultivo:</label>
                            <input type="file" id="mFoto" name="mFoto" accept="image/*">
                        </div>

                        <!-- Nombres -->
                        <div class="form-group-custom">
                            <label for="mNombreComun">Nombre Común:</label>
                            <input type="text" id="mNombreComun" name="mNombreComun" placeholder="Ej. Moho negro / Levadura de pan">
                        </div>
                        <div class="form-group-custom">
                            <label for="mNombreCientifico">Nombre Científico:</label>
                            <input type="text" id="mNombreCientifico" name="mNombreCientifico" placeholder="Ej. Aspergillus niger / Candida albicans">
                        </div>

                        <!-- Dominio y Reino -->
                        <div class="form-group-custom">
                            <label for="fkDominio">Dominio:</label>
                            <select id="fkDominio" name="fkDominio" onchange="checkNewOption(this, 'new_fkDominio')">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input type="text" id="new_fkDominio" name="new_fkDominio" class="input-new-text" placeholder="Escribe el nuevo Dominio...">
                        </div>

                        <div class="form-group-custom">
                            <label for="fkReino">Reino:</label>
                            <select id="fkReino" name="fkReino" onchange="checkNewOption(this, 'new_fkReino')">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input type="text" id="new_fkReino" name="new_fkReino" class="input-new-text" placeholder="Escribe el nuevo Reino...">
                        </div>

                        <!-- Filo y Muestra -->
                        <div class="form-group-custom">
                            <label for="fkFilo">Filo (Phylum):</label>
                            <select id="fkFilo" name="fkFilo" onchange="checkNewOption(this, 'new_fkFilo')">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input type="text" id="new_fkFilo" name="new_fkFilo" class="input-new-text" placeholder="Escribe el nuevo Filo...">
                        </div>

                        <div class="form-group-custom">
                            <label for="mMuestra">Origen de la Muestra:</label>
                            <select id="mMuestra" name="mMuestra" onchange="checkNewOption(this, 'new_mMuestra')">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input type="text" id="new_mMuestra" name="new_mMuestra" class="input-new-text" placeholder="Ej. Raspado de piel / Uñas / Esputo...">
                        </div>

                        <!-- Coloración / Método -->
                        <div class="form-group-custom form-group-full">
                            <label for="mColoracion">Coloración / Examen:</label>
                            <select id="mColoracion" name="mColoracion" onchange="checkNewOption(this, 'new_mColoracion')">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input type="text" id="new_mColoracion" name="new_mColoracion" class="input-new-text" placeholder="Ej. KOH 10%, Azul de Lactofenol, Gram...">
                        </div>

                        <!-- Botón Acordeón Taxonomía Intermedia -->
                        <div class="form-group-full">
                            <button type="button" class="accordion-toggle-btn" onclick="toggleAccordion()">
                                <span>⚙️ Mostrar más niveles taxonómicos (Subfilo, Superclase, Subclase)</span>
                                <i class="fas fa-chevron-down" id="accordion-icon"></i>
                            </button>
                        </div>

                        <div class="accordion-content form-grid-2col form-group-full" id="accordion-taxo">
                            <div class="form-group-custom">
                                <label for="fkSubfilo">Subfilo:</label>
                                <select id="fkSubfilo" name="fkSubfilo" onchange="checkNewOption(this, 'new_fkSubfilo')">
                                    <option value="">-- Seleccionar --</option>
                                </select>
                                <input type="text" id="new_fkSubfilo" name="new_fkSubfilo" class="input-new-text" placeholder="Nuevo Subfilo...">
                            </div>

                            <div class="form-group-custom">
                                <label for="fkSuperclase">Superclase:</label>
                                <select id="fkSuperclase" name="fkSuperclase" onchange="checkNewOption(this, 'new_fkSuperclase')">
                                    <option value="">-- Seleccionar --</option>
                                </select>
                                <input type="text" id="new_fkSuperclase" name="new_fkSuperclase" class="input-new-text" placeholder="Nueva Superclase...">
                            </div>

                            <div class="form-group-custom form-group-full">
                                <label for="fkSubclase">Subclase:</label>
                                <select id="fkSubclase" name="fkSubclase" onchange="checkNewOption(this, 'new_fkSubclase')">
                                    <option value="">-- Seleccionar --</option>
                                </select>
                                <input type="text" id="new_fkSubclase" name="new_fkSubclase" class="input-new-text" placeholder="Nueva Subclase...">
                            </div>
                        </div>

                        <!-- Clase y Orden -->
                        <div class="form-group-custom">
                            <label for="fkClase">Clase:</label>
                            <select id="fkClase" name="fkClase" onchange="checkNewOption(this, 'new_fkClase')">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input type="text" id="new_fkClase" name="new_fkClase" class="input-new-text" placeholder="Escribe nueva Clase...">
                        </div>

                        <div class="form-group-custom">
                            <label for="fkOrden">Orden:</label>
                            <select id="fkOrden" name="fkOrden" onchange="checkNewOption(this, 'new_fkOrden')">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input type="text" id="new_fkOrden" name="new_fkOrden" class="input-new-text" placeholder="Escribe nuevo Orden...">
                        </div>

                        <!-- Familia y Género -->
                        <div class="form-group-custom">
                            <label for="fkFamilia">Familia:</label>
                            <select id="fkFamilia" name="fkFamilia" onchange="checkNewOption(this, 'new_fkFamilia')">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input type="text" id="new_fkFamilia" name="new_fkFamilia" class="input-new-text" placeholder="Escribe nueva Familia...">
                        </div>

                        <div class="form-group-custom">
                            <label for="fkGenero">Género:</label>
                            <select id="fkGenero" name="fkGenero" onchange="checkNewOption(this, 'new_fkGenero')">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input type="text" id="new_fkGenero" name="new_fkGenero" class="input-new-text" placeholder="Escribe nuevo Género...">
                        </div>

                        <!-- Especie -->
                        <div class="form-group-custom form-group-full">
                            <label for="fkEspecie">Especie:</label>
                            <select id="fkEspecie" name="fkEspecie" onchange="checkNewOption(this, 'new_fkEspecie')">
                                <option value="">-- Seleccionar --</option>
                            </select>
                            <input type="text" id="new_fkEspecie" name="new_fkEspecie" class="input-new-text" placeholder="Escribe nueva Especie...">
                        </div>
                    </div>
                </div>

                <div class="modal-custom-footer">
                    <button type="button" class="btn-cancel" onclick="cerrarModalHongo()">Cancelar</button>
                    <button type="submit" class="btn-save" id="btn-submit-hongo">Guardar Hongo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts del Sistema -->
    <script src="JS/barradenavegacion.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            cargarCatalogosConstantes();
            cargarHongos();

            document.getElementById("form-hongo").addEventListener("submit", function(e) {
                e.preventDefault();
                guardarHongo();
            });
        });

        // Configuración de los grupos de constante
        const gruposTaxonomicos = [
            { id: 'mMuestra', grupo: 1 },
            { id: 'fkDominio', grupo: 2 },
            { id: 'fkReino', grupo: 3 },
            { id: 'fkFilo', grupo: 4 },
            { id: 'fkSubfilo', grupo: 5 },
            { id: 'fkSuperclase', grupo: 6 },
            { id: 'fkClase', grupo: 7 },
            { id: 'fkSubclase', grupo: 8 },
            { id: 'fkOrden', grupo: 9 },
            { id: 'fkFamilia', grupo: 10 },
            { id: 'fkGenero', grupo: 11 },
            { id: 'fkEspecie', grupo: 12 },
            { id: 'mColoracion', grupo: 15 }
        ];

        function cargarCatalogosConstantes() {
            gruposTaxonomicos.forEach(item => {
                const select = document.getElementById(item.id);
                if (!select) return;

                fetch(`controladores/get_constante.php?grupo=${item.grupo}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'ok') {
                            select.innerHTML = '<option value="">-- Seleccionar --</option>';
                            res.data.forEach(constante => {
                                const opt = document.createElement('option');
                                opt.value = constante.tCodigo;
                                opt.textContent = constante.cConstDescripcion;
                                select.appendChild(opt);
                            });
                            const newOpt = document.createElement('option');
                            newOpt.value = '__NEW__';
                            newOpt.textContent = '➕ Agregar Nuevo...';
                            newOpt.style.fontWeight = 'bold';
                            newOpt.style.color = '#10b981';
                            select.appendChild(newOpt);
                        }
                    })
                    .catch(err => console.error(`Error al cargar grupo ${item.grupo}:`, err));
            });
        }

        function checkNewOption(selectElem, inputId) {
            const inputNew = document.getElementById(inputId);
            if (!inputNew) return;
            if (selectElem.value === '__NEW__') {
                inputNew.style.display = 'block';
                inputNew.required = true;
                inputNew.focus();
            } else {
                inputNew.style.display = 'none';
                inputNew.required = false;
                inputNew.value = '';
            }
        }

        function toggleAccordion() {
            const content = document.getElementById('accordion-taxo');
            const icon = document.getElementById('accordion-icon');
            if (content.classList.contains('active')) {
                content.classList.remove('active');
                icon.className = 'fas fa-chevron-down';
            } else {
                content.classList.add('active');
                icon.className = 'fas fa-chevron-up';
            }
        }

        function abrirModalHongo(datos = null) {
            const modal = document.getElementById("modal-hongo");
            const form = document.getElementById("form-hongo");
            form.reset();
            
            // Ocultar inputs __NEW__
            document.querySelectorAll('.input-new-text').forEach(inpt => {
                inpt.style.display = 'none';
                inpt.required = false;
            });

            if (datos) {
                document.getElementById("modal-titulo").innerHTML = '<i class="fas fa-edit"></i> Editar Hongo 🔬';
                document.getElementById("mCodigo").value = datos.mCodigo;
                document.getElementById("mNombreComun").value = datos.mNombreComun || '';
                document.getElementById("mNombreCientifico").value = datos.mNombreCientifico || '';
                
                // Seleccionar llaves taxonómicas
                ['fkDominio','fkReino','fkFilo','fkSubfilo','fkSuperclase','fkClase','fkSubclase','fkOrden','fkFamilia','fkGenero','fkEspecie'].forEach(key => {
                    const sel = document.getElementById(key);
                    if (sel && datos[key]) sel.value = datos[key];
                });

                // Seleccionar muestra y coloración si coinciden con los selects
                ['mMuestra', 'mColoracion'].forEach(key => {
                    const sel = document.getElementById(key);
                    if (sel && datos[key]) {
                        for (let i = 0; i < sel.options.length; i++) {
                            if (sel.options[i].textContent.trim().toLowerCase() === datos[key].trim().toLowerCase() || sel.options[i].value == datos[key]) {
                                sel.selectedIndex = i;
                                break;
                            }
                        }
                    }
                });
            } else {
                document.getElementById("modal-titulo").innerHTML = '<i class="fas fa-microscope"></i> Añadir Nuevo Hongo 🔬';
                document.getElementById("mCodigo").value = '0';
            }

            modal.classList.add("active");
        }

        function cerrarModalHongo() {
            document.getElementById("modal-hongo").classList.remove("active");
        }

        function cargarHongos() {
            const grid = document.getElementById("grid-hongos");
            fetch("controladores/listar_hongos.php")
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'ok') {
                        grid.innerHTML = '';
                        if (res.data.length === 0) {
                            grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:50px; color:#64748b;">No hay especímenes de hongos registrados. Haz clic en "Añadir nuevo Hongo" para agregar uno.</div>';
                            return;
                        }

                        res.data.forEach(item => {
                            const fotoSrc = item.mFoto ? `uploads/${item.mFoto}` : null;
                            const imgHtml = fotoSrc 
                                ? `<img src="${fotoSrc}" alt="${item.mNombreComun || 'Hongo'}">`
                                : `<i class="fas fa-image specimen-placeholder-icon"></i>`;

                            let pillsHtml = '';
                            if (item.cDominio) pillsHtml += `<span class="taxo-pill">Dom: ${item.cDominio}</span>`;
                            if (item.cReino)   pillsHtml += `<span class="taxo-pill">Rei: ${item.cReino}</span>`;
                            if (item.cFilo)    pillsHtml += `<span class="taxo-pill">Fil: ${item.cFilo}</span>`;
                            if (item.cClase)   pillsHtml += `<span class="taxo-pill">Cla: ${item.cClase}</span>`;
                            if (item.cOrden)   pillsHtml += `<span class="taxo-pill">Ord: ${item.cOrden}</span>`;
                            if (item.cFamilia) pillsHtml += `<span class="taxo-pill">Fam: ${item.cFamilia}</span>`;
                            if (item.cGenero)  pillsHtml += `<span class="taxo-pill">Gen: ${item.cGenero}</span>`;
                            if (item.cEspecie) pillsHtml += `<span class="taxo-pill">Esp: ${item.cEspecie}</span>`;

                            const card = document.createElement("div");
                            card.className = "specimen-card";
                            card.innerHTML = `
                                <div class="specimen-img-container">
                                    ${imgHtml}
                                </div>
                                <div class="specimen-body">
                                    <div class="specimen-header-row">
                                        <h4 class="specimen-common-name">${item.mNombreComun || 'Sin nombre común'}</h4>
                                        <span class="specimen-id-badge">#${item.mCodigo}</span>
                                    </div>
                                    <div class="specimen-scientific-name">${item.mNombreCientifico || 'Sin nombre científico'}</div>
                                    <div class="specimen-details-list">
                                        <div><strong>Muestra:</strong> ${item.mMuestra || 'No especificada'}</div>
                                        <div><strong>Coloración:</strong> ${item.mColoracion || 'No especificada'}</div>
                                    </div>
                                    <div class="taxo-pills-wrap">
                                        ${pillsHtml}
                                    </div>
                                    <div class="specimen-actions-bar">
                                        <button type="button" class="btn-action btn-edit-table" onclick='abrirModalHongo(${JSON.stringify(item)})'>✏️ Editar</button>
                                        <button type="button" class="btn-action btn-delete-table" onclick="eliminarHongo(${item.mCodigo})">🗑️ Eliminar</button>
                                    </div>
                                </div>
                            `;
                            grid.appendChild(card);
                        });
                    } else {
                        grid.innerHTML = `<div style="grid-column:1/-1; color:#ef4444; text-align:center; padding:30px;">Error: ${res.message}</div>`;
                    }
                })
                .catch(err => {
                    grid.innerHTML = `<div style="grid-column:1/-1; color:#ef4444; text-align:center; padding:30px;">Error de red al cargar hongos.</div>`;
                });
        }

        function guardarHongo() {
            const form = document.getElementById("form-hongo");
            const formData = new FormData(form);

            fetch("controladores/guardar_hongo.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'ok') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: res.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                    cerrarModalHongo();
                    cargarCatalogosConstantes();
                    cargarHongos();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Red',
                    text: 'No se pudo guardar el hongo.'
                });
            });
        }

        function eliminarHongo(id) {
            Swal.fire({
                title: '¿Eliminar Hongo?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmColor: '#ef4444',
                cancelColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("controladores/eliminar_hongo.php", {
                        method: "POST",
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: new URLSearchParams({ id: id })
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'ok') {
                            Swal.fire('¡Eliminado!', res.message, 'success');
                            cargarHongos();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
