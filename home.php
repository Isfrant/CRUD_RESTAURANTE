<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="bg-light">

<!-- ══════════════════ APP VUE ══════════════════ -->
<div id="app" class="container-fluid">

    <div v-if="loading" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white" style="z-index: 1050; opacity: 0.8;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        <div v-for="t in toasts" :key="t.id" class="toast align-items-center text-bg-dark border-0 show mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi" :class="t.type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger'"></i>
                    {{ t.msg }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close" @click="toasts = toasts.filter(toast => toast.id !== t.id)"></button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- SIDEBAR -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-dark text-white sidebar min-vh-100">
            <div class="position-sticky pt-3">
                <h4 class="text-center py-3 border-bottom border-secondary">
                    <i class="bi bi-shop"></i> Restaurante
                </h4>
                
                <ul class="nav flex-column mb-auto mt-3">
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white" href="#" @click.prevent="filtroEstado = ''">
                            <i class="bi bi-grid-fill me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white" href="#" @click.prevent="openModal(null)">
                            <i class="bi bi-plus-circle me-2"></i> Nuevo Insumo
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white" href="#" @click.prevent="filtroEstado = 'critico'">
                            <i class="bi bi-exclamation-triangle me-2"></i> Stock Crítico
                        </a>
                    </li>
                </ul>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-2 text-secondary text-uppercase">
                    <span>Reportes</span>
                </h6>
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white" href="#" @click.prevent="generarPDF()">
                            <i class="bi bi-file-earmark-pdf me-2"></i> Exportar PDF
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white" href="php/exportar_excel.php" target="_blank">
                            <i class="bi bi-file-earmark-excel me-2"></i> Exportar Excel
                        </a>
                    </li>
                </ul>

                <hr class="border-secondary mt-5">
                <div class="px-3 pb-3 d-flex align-items-center justify-content-between">
                    <div>
                        <strong><?= $usuario ?></strong><br>
                        <small class="text-secondary">Administrador</small>
                    </div>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm" title="Cerrar sesión">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>
        </nav>

        <!-- MAIN CONTENT -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <!-- HEADER -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
                <h1 class="h2">Gestión de Inventario</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button class="btn btn-sm btn-outline-secondary" @click="generarPDF()">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </button>
                        <a href="php/exportar_excel.php" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-earmark-excel"></i> Excel
                        </a>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" @click="openModal(null)">
                        <i class="bi bi-plus-lg"></i> Nuevo Insumo
                    </button>
                </div>
            </div>

            <!-- STAT CARDS -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-primary"><i class="bi bi-box-seam"></i> {{ stats.total }}</h3>
                            <p class="card-text text-muted">Total Insumos</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-danger"><i class="bi bi-exclamation-octagon"></i> {{ stats.agotados }}</h3>
                            <p class="card-text text-muted">Agotados</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-warning"><i class="bi bi-exclamation-triangle"></i> {{ stats.bajoStock }}</h3>
                            <p class="card-text text-muted">Bajo Stock</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-success"><i></i> {{ formatCOP(stats.valorTotal) }}</h3>
                            <p class="card-text text-muted">Valor Total</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ALERTA CRÍTICA -->
            <div class="alert alert-danger d-flex align-items-center" role="alert" v-if="stats.bajoStock > 0">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                <div>
                    <strong>{{ stats.bajoStock }} insumo(s)</strong> por debajo del stock mínimo. Revisa el inventario.
                </div>
            </div>

            <!-- FILTROS -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" v-model="busqueda" placeholder="Buscar insumo...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" v-model="filtroCategoria">
                                <option value="">Todas las categorías</option>
                                <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" v-model="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="ok">Stock OK</option>
                                <option value="bajo">Bajo Stock</option>
                                <option value="critico">Crítico / Agotado</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end text-muted small">
                            {{ insumosFiltrados.length }} registro(s)
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0"><i class="bi bi-table text-primary me-2"></i>Inventario</h5>
                    
                    <!-- Paginación -->
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary" @click="pagina > 1 && pagina--" :disabled="pagina === 1"><i class="bi bi-chevron-left"></i></button>
                        <span class="btn btn-sm btn-outline-secondary disabled">{{ pagina }} / {{ totalPaginas || 1 }}</span>
                        <button class="btn btn-sm btn-outline-secondary" @click="pagina < totalPaginas && pagina++" :disabled="pagina >= totalPaginas"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center">Imagen</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Stock Actual</th>
                                    <th>Stock Mín.</th>
                                    <th>Estado</th>
                                    <th>Precio Unit.</th>
                                    <th>Vencimiento</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="insumosPaginados.length === 0">
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No se encontraron insumos.
                                    </td>
                                </tr>
                                <tr v-for="ins in insumosPaginados" :key="ins.id" :class="{'table-danger': parseFloat(ins.stock_actual) < parseFloat(ins.stock_minimo)}">
                                    <td class="text-center">
                                        <img v-if="ins.imagen_ruta" :src="ins.imagen_ruta" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;" :alt="ins.nombre">
                                        <div v-else class="bg-secondary text-white d-flex justify-content-center align-items-center rounded" style="width: 50px; height: 50px; margin: 0 auto;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    </td>
                                    <td class="fw-bold">{{ ins.nombre }}</td>
                                    <td><span class="badge bg-secondary">{{ ins.categoria_nombre }}</span></td>
                                    <td>{{ ins.stock_actual }}</td>
                                    <td>{{ ins.stock_minimo }}</td>
                                    <td>
                                        <span class="badge" :class="{'bg-success': parseFloat(ins.stock_actual) >= parseFloat(ins.stock_minimo), 'bg-warning text-dark': parseFloat(ins.stock_actual) < parseFloat(ins.stock_minimo) && parseFloat(ins.stock_actual) > 0, 'bg-danger': parseFloat(ins.stock_actual) === 0}">
                                            <i class="bi" :class="{'bi-check-circle-fill': parseFloat(ins.stock_actual) >= parseFloat(ins.stock_minimo), 'bi-exclamation-triangle-fill': parseFloat(ins.stock_actual) < parseFloat(ins.stock_minimo) && parseFloat(ins.stock_actual) > 0, 'bi-x-circle-fill': parseFloat(ins.stock_actual) === 0}"></i>
                                            {{ getStockLabel(ins) }}
                                        </span>
                                    </td>
                                    <td>{{ formatCOP(ins.precio_unitario) }}</td>
                                    <td :class="{'text-danger fw-bold': isVencido(ins.fecha_vencimiento)}">
                                        {{ ins.fecha_vencimiento ? formatFecha(ins.fecha_vencimiento) : '—' }}
                                        <i v-if="isVencido(ins.fecha_vencimiento)" class="bi bi-exclamation-circle-fill"></i>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning me-1" @click="openModal(ins)" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" @click="confirmarEliminar(ins)" title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </main>
    </div>

    <!-- ══ MODAL CRUD ══ -->
    <div class="modal fade" id="modalInsumo" tabindex="-1" ref="modalInsumo">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="bi me-2" :class="editando ? 'bi-pencil-square' : 'bi-plus-circle'"></i>
                        {{ editando ? 'Editar Insumo' : 'Nuevo Insumo' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del Insumo *</label>
                            <input type="text" class="form-control" v-model="form.nombre" placeholder="Ej: Pollo Entero">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Categoría *</label>
                            <select class="form-select" v-model="form.categoria_id">
                                <option value="">— Selecciona —</option>
                                <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Stock Actual *</label>
                            <input type="number" class="form-control" v-model="form.stock_actual" min="0" step="0.01" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Stock Mínimo *</label>
                            <input type="number" class="form-control" v-model="form.stock_minimo" min="0" step="0.01" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Precio Unitario (COP) *</label>
                            <input type="number" class="form-control" v-model="form.precio_unitario" min="0" step="0.01" placeholder="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha de Vencimiento</label>
                            <input type="date" class="form-control" v-model="form.fecha_vencimiento">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Imagen del Insumo</label>
                            <input type="file" class="form-control" ref="fileInput" @change="onFileChange" accept="image/jpeg,image/png,image/webp,image/gif">
                            <div class="form-text">JPG, PNG, WEBP · Máx. 2MB</div>
                        </div>
                        <div class="col-12" v-if="previewImg || form.imagen_ruta">
                            <div class="d-flex align-items-center gap-3 p-2 border rounded bg-light">
                                <img :src="previewImg || form.imagen_ruta" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                <span class="text-muted small">Vista previa de imagen</span>
                            </div>
                        </div>
                        <div class="col-12" v-if="formError">
                            <div class="alert alert-danger d-flex align-items-center mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ formError }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" @click="guardarInsumo" :disabled="guardando">
                        <span v-if="guardando"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Guardando...</span>
                        <span v-else><i class="bi bi-check-lg"></i> {{ editando ? 'Actualizar' : 'Guardar' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ MODAL CONFIRMAR ELIMINAR ══ -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" ref="modalEliminar">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash3-fill me-2"></i>Confirmar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:3rem; margin-bottom: 1rem; display: block;"></i>
                    <p class="mb-1">¿Estás seguro de eliminar <strong class="text-dark">{{ eliminando ? eliminando.nombre : '' }}</strong>?</p>
                    <small class="text-muted">Esta acción no se puede deshacer.</small>
                </div>
                <div class="modal-footer justify-content-center bg-light">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger" @click="eliminarInsumo">
                        <i class="bi bi-trash-fill me-1"></i> Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ PLANTILLA OCULTA PARA EL PDF ══ -->
    <div style="display: none;">
        <div id="pdf-reporte" class="bg-white text-dark p-4" style="width: 1000px;">
            <div class="text-center mb-4 pb-3 border-bottom border-2 border-dark">
                <h2><i class="bi bi-shop"></i> Restaurante</h2>
                <h4>Reporte Completo de Inventario</h4>
                <p class="mb-0 text-muted">Generado el: {{ fechaActual }}</p>
            </div>
            
            <div class="row mb-4 text-center">
                <div class="col-3 border p-2 bg-light"><strong>Total Insumos:</strong><br>{{ stats.total }}</div>
                <div class="col-3 border p-2 bg-light"><strong>Agotados:</strong><br>{{ stats.agotados }}</div>
                <div class="col-3 border p-2 bg-light"><strong>Bajo Stock:</strong><br>{{ stats.bajoStock }}</div>
                <div class="col-3 border p-2 bg-light"><strong>Valor Total:</strong><br>{{ formatCOP(stats.valorTotal) }}</div>
            </div>

            <table class="table table-bordered table-sm align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Insumo</th>
                        <th>Categoría</th>
                        <th>Stock Actual</th>
                        <th>Stock Mín.</th>
                        <th>Estado</th>
                        <th>Precio Unit.</th>
                        <th>Vencimiento</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="ins in insumos" :key="ins.id" :class="{'table-danger': parseFloat(ins.stock_actual) < parseFloat(ins.stock_minimo)}">
                        <td class="fw-bold">{{ ins.nombre }}</td>
                        <td>{{ ins.categoria_nombre }}</td>
                        <td>{{ ins.stock_actual }}</td>
                        <td>{{ ins.stock_minimo }}</td>
                        <td>{{ getStockLabel(ins) }}</td>
                        <td>{{ formatCOP(ins.precio_unitario) }}</td>
                        <td>{{ ins.fecha_vencimiento ? formatFecha(ins.fecha_vencimiento) : '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /#app -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.7.16/dist/vue.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>
