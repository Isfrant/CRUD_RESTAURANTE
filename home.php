<?php
// Iniciamos la sesión para poder verificar si el usuario está logueado
session_start();

// Si no existe la variable de sesión 'usuario_id', lo devolvemos al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
// Guardamos el nombre del usuario para mostrarlo en la interfaz de forma segura
$usuario = htmlspecialchars($_SESSION['usuario_nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestaurantePRO · Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold:     #C9A84C;
            --gold-lt:  #E8C97A;
            --gold-dk:  #A07832;
            --dark:     #0F0D0B;
            --dark2:    #161310;
            --dark3:    #1E1A16;
            --dark4:    #28231D;
            --cream:    #F5F0E8;
            --cream-lt: #FAF7F2;
            --red:      #C0392B;
            --red-lt:   rgba(192,57,43,0.12);
            --green:    #27AE60;
            --green-lt: rgba(39,174,96,0.12);
            --amber:    #D68910;
            --amber-lt: rgba(214,137,16,0.12);
            --sidebar-w: 260px;
            --header-h:  64px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--dark);
            font-family: 'DM Sans', sans-serif;
            color: var(--cream);
            min-height: 100vh;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-w); height: 100vh;
            background: var(--dark2);
            border-right: 1px solid rgba(201,168,76,0.12);
            display: flex; flex-direction: column;
            z-index: 100;
            transition: transform .3s;
        }
        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(201,168,76,0.1);
        }
        .brand-logo {
            display: flex; align-items: center; gap: 12px;
        }
        .brand-icon-sm {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--gold), var(--gold-lt));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(201,168,76,0.25);
            flex-shrink: 0;
        }
        .brand-text-sm { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; color: var(--cream); }
        .brand-text-sm span { color: var(--gold); }

        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-section-label {
            font-size: 0.65rem; font-weight: 500; letter-spacing: 2px;
            text-transform: uppercase; color: rgba(245,240,232,0.3);
            padding: 12px 8px 6px;
        }
        .nav-item { margin-bottom: 2px; }
        .nav-link-custom {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 8px;
            color: rgba(245,240,232,0.55); font-size: 0.87rem;
            text-decoration: none; transition: all .2s; cursor: pointer;
        }
        .nav-link-custom:hover, .nav-link-custom.active {
            background: rgba(201,168,76,0.1);
            color: var(--gold);
        }
        .nav-link-custom i { font-size: 1rem; width: 20px; text-align: center; }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(201,168,76,0.1);
        }
        .user-info {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 8px;
            background: rgba(201,168,76,0.06);
        }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, var(--gold-dk), var(--gold));
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; font-weight: 600; color: var(--dark); flex-shrink: 0;
        }
        .user-name { font-size: 0.82rem; font-weight: 500; color: var(--cream); }
        .user-role { font-size: 0.72rem; color: rgba(245,240,232,0.4); }
        .btn-logout {
            margin-left: auto;
            background: none; border: none;
            color: rgba(245,240,232,0.35);
            cursor: pointer; padding: 4px;
            transition: color .2s;
        }
        .btn-logout:hover { color: var(--red); }

        /* ── HEADER ── */
        .main-header {
            position: fixed; top: 0; left: var(--sidebar-w); right: 0;
            height: var(--header-h);
            background: rgba(15,13,11,0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(201,168,76,0.1);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px;
            z-index: 90;
        }
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem; font-weight: 700;
            color: var(--cream);
        }
        .header-actions { display: flex; align-items: center; gap: 10px; }

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-left: var(--sidebar-w);
            padding-top: calc(var(--header-h) + 24px);
            padding-bottom: 40px;
            padding-left: 28px; padding-right: 28px;
            min-height: 100vh;
        }

        /* ── STAT CARDS ── */
        .stat-card {
            background: var(--dark2);
            border: 1px solid rgba(201,168,76,0.12);
            border-radius: 14px;
            padding: 20px 22px;
            transition: border-color .25s, transform .25s;
        }
        .stat-card:hover {
            border-color: rgba(201,168,76,0.3);
            transform: translateY(-2px);
        }
        .stat-icon {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; margin-bottom: 14px;
        }
        .stat-icon.gold  { background: rgba(201,168,76,0.15); color: var(--gold); }
        .stat-icon.red   { background: var(--red-lt);          color: #e74c3c; }
        .stat-icon.green { background: var(--green-lt);         color: var(--green); }
        .stat-icon.amber { background: var(--amber-lt);         color: var(--amber); }
        .stat-value { font-size: 2rem; font-weight: 600; color: var(--cream); line-height: 1; }
        .stat-label { font-size: 0.78rem; color: rgba(245,240,232,0.45); margin-top: 4px; letter-spacing: .5px; }

        /* ── FILTER BAR ── */
        .filter-bar {
            background: var(--dark2);
            border: 1px solid rgba(201,168,76,0.12);
            border-radius: 12px;
            padding: 14px 18px;
            display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
        }
        .search-input {
            background: var(--dark3);
            border: 1px solid rgba(201,168,76,0.15);
            border-radius: 8px;
            color: var(--cream);
            padding: 8px 14px 8px 36px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            width: 240px;
            transition: border-color .2s;
        }
        .search-input:focus { outline: none; border-color: var(--gold); }
        .search-input::placeholder { color: rgba(245,240,232,0.25); }
        .search-wrap { position: relative; }
        .search-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: rgba(245,240,232,0.3); font-size: 0.9rem; }

        .filter-select {
            background: var(--dark3);
            border: 1px solid rgba(201,168,76,0.15);
            border-radius: 8px;
            color: var(--cream);
            padding: 8px 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            transition: border-color .2s;
        }
        .filter-select:focus { outline: none; border-color: var(--gold); }
        .filter-select option { background: var(--dark3); }

        /* ── BOTONES ── */
        .btn-gold {
            background: linear-gradient(135deg, var(--gold), var(--gold-lt));
            border: none; border-radius: 8px;
            padding: 9px 18px;
            color: var(--dark); font-family: 'DM Sans', sans-serif;
            font-weight: 500; font-size: 0.85rem;
            cursor: pointer; transition: transform .2s, box-shadow .2s;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-gold:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(201,168,76,0.35); }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(201,168,76,0.3);
            border-radius: 8px; padding: 8px 16px;
            color: var(--gold); font-family: 'DM Sans', sans-serif;
            font-weight: 400; font-size: 0.82rem; cursor: pointer;
            transition: all .2s;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-outline:hover { background: rgba(201,168,76,0.08); border-color: var(--gold); }

        .btn-danger-sm {
            background: rgba(192,57,43,0.1); border: 1px solid rgba(192,57,43,0.3);
            border-radius: 6px; padding: 5px 10px;
            color: #e74c3c; font-size: 0.78rem; cursor: pointer; transition: all .2s;
        }
        .btn-danger-sm:hover { background: rgba(192,57,43,0.2); }
        .btn-edit-sm {
            background: rgba(201,168,76,0.1); border: 1px solid rgba(201,168,76,0.25);
            border-radius: 6px; padding: 5px 10px;
            color: var(--gold); font-size: 0.78rem; cursor: pointer; transition: all .2s;
        }
        .btn-edit-sm:hover { background: rgba(201,168,76,0.2); }

        /* ── TABLA ── */
        .table-card {
            background: var(--dark2);
            border: 1px solid rgba(201,168,76,0.12);
            border-radius: 14px; overflow: hidden;
        }
        .table-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(201,168,76,0.1);
            display: flex; align-items: center; justify-content: space-between;
        }
        .table-card-title { font-size: 0.9rem; font-weight: 500; color: var(--cream); }

        .inv-table { width: 100%; border-collapse: collapse; }
        .inv-table thead th {
            background: var(--dark3);
            padding: 11px 16px;
            font-size: 0.72rem; font-weight: 500; letter-spacing: 1.5px;
            text-transform: uppercase; color: rgba(245,240,232,0.4);
            text-align: left; border-bottom: 1px solid rgba(201,168,76,0.1);
            white-space: nowrap;
        }
        .inv-table tbody tr {
            border-bottom: 1px solid rgba(201,168,76,0.06);
            transition: background .15s;
        }
        .inv-table tbody tr:hover { background: rgba(201,168,76,0.03); }
        .inv-table tbody tr.row-critical { background: rgba(192,57,43,0.07); }
        .inv-table tbody tr.row-critical:hover { background: rgba(192,57,43,0.11); }
        .inv-table td { padding: 12px 16px; font-size: 0.85rem; color: rgba(245,240,232,0.8); vertical-align: middle; }

        .stock-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 500;
        }
        .stock-ok      { background: var(--green-lt); color: var(--green); }
        .stock-low     { background: var(--amber-lt); color: var(--amber); }
        .stock-critical{ background: var(--red-lt);   color: #e74c3c; }

        .cat-chip {
            display: inline-block;
            padding: 2px 9px; border-radius: 20px;
            background: rgba(201,168,76,0.1);
            border: 1px solid rgba(201,168,76,0.2);
            color: var(--gold-lt); font-size: 0.73rem;
        }

        .img-thumb {
            width: 38px; height: 38px; border-radius: 8px;
            object-fit: cover; border: 1px solid rgba(201,168,76,0.2);
        }
        .img-placeholder {
            width: 38px; height: 38px; border-radius: 8px;
            background: var(--dark3); border: 1px solid rgba(201,168,76,0.1);
            display: flex; align-items: center; justify-content: center;
            color: rgba(245,240,232,0.2); font-size: 1rem;
        }

        /* ── MODAL ── */
        .modal-content {
            background: var(--dark2);
            border: 1px solid rgba(201,168,76,0.2);
            border-radius: 16px;
            box-shadow: 0 40px 80px rgba(0,0,0,0.7);
        }
        .modal-header {
            background: var(--dark3);
            border-bottom: 1px solid rgba(201,168,76,0.12);
            border-radius: 16px 16px 0 0;
            padding: 18px 24px;
        }
        .modal-title { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: var(--cream); }
        .modal-body { padding: 24px; }
        .modal-footer { border-top: 1px solid rgba(201,168,76,0.1); padding: 16px 24px; }
        .btn-close { filter: invert(1) opacity(.5); }
        .btn-close:hover { filter: invert(1) opacity(.9); }

        .form-label-modal { font-size: 0.75rem; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; color: rgba(245,240,232,0.45); margin-bottom: 6px; }
        .form-control-dark {
            background: var(--dark3); border: 1px solid rgba(201,168,76,0.18);
            border-radius: 8px; color: var(--cream);
            padding: 9px 14px; font-family: 'DM Sans', sans-serif; font-size: 0.87rem;
            width: 100%; transition: border-color .2s;
        }
        .form-control-dark:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.12); }
        .form-control-dark::placeholder { color: rgba(245,240,232,0.2); }
        .form-control-dark option { background: var(--dark3); }
        .form-group-modal { margin-bottom: 16px; }

        /* ── ALERTS ── */
        .alert-strip {
            background: rgba(192,57,43,0.12);
            border: 1px solid rgba(192,57,43,0.3);
            border-radius: 10px;
            padding: 12px 16px;
            color: #e57373; font-size: 0.85rem;
            display: flex; align-items: center; gap: 8px;
        }

        /* ── TOAST ── */
        .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; }
        .toast-item {
            min-width: 280px;
            background: var(--dark2); border: 1px solid rgba(201,168,76,0.2);
            border-radius: 10px; padding: 14px 18px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
            display: flex; align-items: center; gap: 10px;
            margin-top: 8px;
            animation: slideIn .3s ease both;
        }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .toast-item.success { border-color: rgba(39,174,96,0.4); }
        .toast-item.error   { border-color: rgba(192,57,43,0.4); }
        .toast-icon.success { color: var(--green); font-size: 1.1rem; }
        .toast-icon.error   { color: #e74c3c; font-size: 1.1rem; }
        .toast-msg { font-size: 0.85rem; color: var(--cream); }

        /* ── SPINNER ── */
        .loading-overlay {
            position: fixed; inset: 0;
            background: rgba(15,13,11,0.7);
            display: flex; align-items: center; justify-content: center;
            z-index: 9998; backdrop-filter: blur(4px);
        }
        .spinner-ring {
            width: 48px; height: 48px; border-radius: 50%;
            border: 3px solid rgba(201,168,76,0.15);
            border-top-color: var(--gold);
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── PAGINACIÓN ── */
        .pagination-bar { display: flex; align-items: center; gap: 6px; }
        .page-btn {
            width: 30px; height: 30px; border-radius: 6px;
            background: var(--dark3); border: 1px solid rgba(201,168,76,0.15);
            color: rgba(245,240,232,0.5); font-size: 0.8rem; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all .2s;
        }
        .page-btn:hover, .page-btn.active {
            background: rgba(201,168,76,0.15); border-color: var(--gold); color: var(--gold);
        }
        .page-info { font-size: 0.78rem; color: rgba(245,240,232,0.35); padding: 0 4px; }

        /* ── MOBILE ── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content, .main-header { margin-left: 0; left: 0; }
            .main-header { left: 0; }
        }
    </style>
</head>
<body>

<!-- ══════════════════ APP VUE ══════════════════ -->
<div id="app">

    <!-- Loading -->
    <div class="loading-overlay" v-if="loading">
        <div class="spinner-ring"></div>
    </div>

    <!-- Toasts -->
    <div class="toast-container">
        <div v-for="t in toasts" :key="t.id" class="toast-item" :class="t.type">
            <i class="toast-icon bi" :class="t.type === 'success' ? 'bi-check-circle-fill success' : 'bi-x-circle-fill error'"></i>
            <span class="toast-msg">{{ t.msg }}</span>
        </div>
    </div>

    <!-- SIDEBAR -->
    <aside class="sidebar" :class="{ open: sidebarOpen }">
        <div class="sidebar-brand">
            <div class="brand-logo">
                <div class="brand-icon-sm">🍽️</div>
                <div>
                    <div class="brand-text-sm">Restaurant<span>PRO</span></div>
                </div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Principal</div>
            <div class="nav-item">
                <a class="nav-link-custom active"><i class="bi bi-grid-fill"></i> Dashboard</a>
            </div>
            <div class="nav-section-label">Inventario</div>
            <div class="nav-item">
                <a class="nav-link-custom" @click="openModal(null)"><i class="bi bi-plus-circle"></i> Nuevo Insumo</a>
            </div>
            <div class="nav-item">
                <a class="nav-link-custom" @click="filtroEstado = 'critico'"><i class="bi bi-exclamation-triangle"></i> Stock Crítico</a>
            </div>
            <div class="nav-section-label">Reportes</div>
            <div class="nav-item">
                <a class="nav-link-custom" href="php/exportar_pdf.php" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
            </div>
            <div class="nav-item">
                <a class="nav-link-custom" href="php/exportar_excel.php" target="_blank"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($usuario, 0, 1)) ?></div>
                <div>
                    <!-- Mostramos el nombre del usuario logueado usando la variable PHP -->
                    <div class="user-name"><?= $usuario ?></div>
                    <div class="user-role">Administrador</div>
                </div>
                <!-- Botón de Cerrar Sesión que apunta al script logout.php creado -->
                <a href="logout.php" class="btn-logout" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- HEADER -->
    <header class="main-header">
        <div class="d-flex align-items-center gap-3">
            <button class="btn-outline d-md-none" @click="sidebarOpen = !sidebarOpen" style="padding:6px 10px;">
                <i class="bi bi-list"></i>
            </button>
            <span class="page-title">Gestión de Inventario</span>
        </div>
        <div class="header-actions">
            <a href="php/exportar_pdf.php" target="_blank" class="btn-outline">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
            <a href="php/exportar_excel.php" target="_blank" class="btn-outline">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            <button class="btn-gold" @click="openModal(null)">
                <i class="bi bi-plus-lg"></i> Nuevo Insumo
            </button>
        </div>
    </header>

    <!-- MAIN -->
    <main class="main-content">

        <!-- STAT CARDS -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon gold"><i class="bi bi-box-seam"></i></div>
                    <div class="stat-value">{{ stats.total }}</div>
                    <div class="stat-label">Total Insumos</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon red"><i class="bi bi-exclamation-octagon"></i></div>
                    <div class="stat-value">{{ stats.agotados }}</div>
                    <div class="stat-label">Agotados</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="stat-value">{{ stats.bajoStock }}</div>
                    <div class="stat-label">Bajo Stock</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-currency-dollar"></i></div>
                    <div class="stat-value">{{ formatCOP(stats.valorTotal) }}</div>
                    <div class="stat-label">Valor Total</div>
                </div>
            </div>
        </div>

        <!-- ALERTA CRÍTICA -->
        <div class="alert-strip mb-4" v-if="stats.bajoStock > 0">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>{{ stats.bajoStock }} insumo(s)</strong> por debajo del stock mínimo. Revisa el inventario.
        </div>

        <!-- FILTROS -->
        <div class="filter-bar mb-3">
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" class="search-input" v-model="busqueda" placeholder="Buscar insumo...">
            </div>
            <select class="filter-select" v-model="filtroCategoria">
                <option value="">Todas las categorías</option>
                <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
            </select>
            <select class="filter-select" v-model="filtroEstado">
                <option value="">Todos los estados</option>
                <option value="ok">Stock OK</option>
                <option value="bajo">Bajo Stock</option>
                <option value="critico">Crítico / Agotado</option>
            </select>
            <span style="margin-left:auto; font-size:0.78rem; color:rgba(245,240,232,0.35);">
                {{ insumosFiltrados.length }} registro(s)
            </span>
        </div>

        <!-- TABLA -->
        <div class="table-card">
            <div class="table-card-header">
                <span class="table-card-title"><i class="bi bi-table me-2" style="color:var(--gold)"></i>Inventario de Insumos</span>
                <div class="pagination-bar">
                    <button class="page-btn" @click="pagina > 1 && pagina--" :disabled="pagina === 1"><i class="bi bi-chevron-left"></i></button>
                    <span class="page-info">{{ pagina }} / {{ totalPaginas || 1 }}</span>
                    <button class="page-btn" @click="pagina < totalPaginas && pagina++" :disabled="pagina >= totalPaginas"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Mín.</th>
                            <th>Estado</th>
                            <th>Precio Unit.</th>
                            <th>Vencimiento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- v-if: Oculta la tabla si no hay resultados -->
                        <tr v-if="insumosPaginados.length === 0">
                            <td colspan="9" style="text-align:center; padding:40px; color:rgba(245,240,232,0.3);">
                                <i class="bi bi-inbox" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                                No se encontraron insumos.
                            </td>
                        </tr>
                        <!-- v-for: Ciclo iterativo de Vue para crear las filas HTML automáticamente con cada insumo -->
                        <tr v-for="ins in insumosPaginados" :key="ins.id"
                            :class="{ 'row-critical': parseFloat(ins.stock_actual) < parseFloat(ins.stock_minimo) }">
                            <td>
                                <!-- Mostrar imagen dinámica vinculada a la ruta que guardamos en BD -->
                                <img v-if="ins.imagen_ruta" :src="ins.imagen_ruta" class="img-thumb" :alt="ins.nombre">
                                <div v-else class="img-placeholder"><i class="bi bi-image"></i></div>
                            </td>
                            <td style="font-weight:500; color:var(--cream);">{{ ins.nombre }}</td>
                            <td><span class="cat-chip">{{ ins.categoria_nombre }}</span></td>
                            <td>{{ ins.stock_actual }}</td>
                            <td>{{ ins.stock_minimo }}</td>
                            <td>
                                <span class="stock-badge" :class="getStockClass(ins)">
                                    <i class="bi" :class="getStockIcon(ins)"></i>
                                    {{ getStockLabel(ins) }}
                                </span>
                            </td>
                            <td>{{ formatCOP(ins.precio_unitario) }}</td>
                            <td :style="{ color: isVencido(ins.fecha_vencimiento) ? '#e74c3c' : '' }">
                                {{ ins.fecha_vencimiento ? formatFecha(ins.fecha_vencimiento) : '—' }}
                                <i v-if="isVencido(ins.fecha_vencimiento)" class="bi bi-exclamation-circle-fill" style="color:#e74c3c; margin-left:4px;"></i>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn-edit-sm" @click="openModal(ins)" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn-danger-sm" @click="confirmarEliminar(ins)" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- ══ MODAL CRUD ══ -->
    <div class="modal fade" id="modalInsumo" tabindex="-1" ref="modalInsumo">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi me-2" :class="editando ? 'bi-pencil-square' : 'bi-plus-circle'"></i>
                        {{ editando ? 'Editar Insumo' : 'Nuevo Insumo' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group-modal">
                                <label class="form-label-modal">Nombre del Insumo *</label>
                                <input type="text" class="form-control-dark" v-model="form.nombre" placeholder="Ej: Pollo Entero">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- v-model: Enlaza (bind) el input bidireccionalmente con la variable 'form.categoria_id' en Vue -->
                            <div class="form-group-modal">
                                <label class="form-label-modal">Categoría *</label>
                                <select class="form-control-dark" v-model="form.categoria_id">
                                    <option value="">— Selecciona —</option>
                                    <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group-modal">
                                <label class="form-label-modal">Stock Actual *</label>
                                <input type="number" class="form-control-dark" v-model="form.stock_actual" min="0" step="0.01" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group-modal">
                                <label class="form-label-modal">Stock Mínimo *</label>
                                <input type="number" class="form-control-dark" v-model="form.stock_minimo" min="0" step="0.01" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group-modal">
                                <label class="form-label-modal">Precio Unitario (COP) *</label>
                                <input type="number" class="form-control-dark" v-model="form.precio_unitario" min="0" step="0.01" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modal">
                                <label class="form-label-modal">Fecha de Vencimiento</label>
                                <input type="date" class="form-control-dark" v-model="form.fecha_vencimiento">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modal">
                                <label class="form-label-modal">Imagen del Insumo</label>
                                <input type="file" class="form-control-dark" ref="fileInput"
                                       @change="onFileChange" accept="image/jpeg,image/png,image/webp,image/gif"
                                       style="padding:6px 14px;">
                                <small style="color:rgba(245,240,232,0.3); font-size:0.72rem; margin-top:4px; display:block;">
                                    JPG, PNG, WEBP · Máx. 2MB
                                </small>
                            </div>
                        </div>
                        <div class="col-12" v-if="previewImg || form.imagen_ruta">
                            <div style="display:flex; align-items:center; gap:12px; padding:10px; background:var(--dark3); border-radius:8px; border:1px solid rgba(201,168,76,0.1);">
                                <img :src="previewImg || form.imagen_ruta" style="width:60px;height:60px;border-radius:8px;object-fit:cover;">
                                <span style="font-size:0.8rem; color:rgba(245,240,232,0.5);">Vista previa de imagen</span>
                            </div>
                        </div>
                        <div class="col-12" v-if="formError">
                            <div class="alert-strip"><i class="bi bi-exclamation-triangle-fill"></i> {{ formError }}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn-gold" @click="guardarInsumo" :disabled="guardando">
                        <span v-if="guardando"><i class="bi bi-hourglass-split"></i> Guardando...</span>
                        <span v-else><i class="bi bi-check-lg"></i> {{ editando ? 'Actualizar' : 'Guardar' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ MODAL CONFIRMAR ELIMINAR ══ -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" ref="modalEliminar">
        <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color:#e74c3c;"><i class="bi bi-trash3-fill me-2"></i>Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="text-align:center; padding:30px 24px;">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:2.5rem; color:#e74c3c; margin-bottom:16px; display:block;"></i>
                    <p style="font-size:0.9rem; color:rgba(245,240,232,0.7);">
                        ¿Estás seguro de eliminar <strong style="color:var(--cream);">{{ eliminando?.nombre }}</strong>?
                        <br><small style="color:rgba(245,240,232,0.4);">Esta acción no se puede deshacer.</small>
                    </p>
                </div>
                <div class="modal-footer" style="justify-content:center; gap:12px;">
                    <button class="btn-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn-danger-sm" style="padding:9px 20px; font-size:0.85rem;" @click="eliminarInsumo">
                        <i class="bi bi-trash-fill me-1"></i> Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

</div><!-- /#app -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.7.16/dist/vue.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>
