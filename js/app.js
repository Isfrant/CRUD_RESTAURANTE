
new Vue({
    el: '#app',

    data: {
        // --- ESTADO GENERAL ---
      
        loading:     false,
        sidebarOpen: false,
        toasts:      [],
        toastId:     0,

        // --- DATOS DEL SERVIDOR ---
        insumos:    [],
        categorias: [],

        // --- FILTROS Y PAGINACIÓN ---
        busqueda:        '',
        filtroCategoria: '',
        filtroEstado:    '',
        pagina:          1,
        porPagina:       12,
        
        // Fecha para el PDF
        fechaActual: new Date().toLocaleString(),

        // --- FORMULARIO MODAL (CRUD) ---
        form: {
            id:               null,
            nombre:           '',
            stock_actual:     '',
            stock_minimo:     '',
            precio_unitario:  '',
            fecha_vencimiento:'',
            categoria_id:     '',
            imagen_ruta:      ''
        },
        archivoImagen: null,
        previewImg:    null,
        formError:     '',
        guardando:     false,
        editando:      false,

        // Modal eliminar
        eliminando: null,

        // Instancias Bootstrap Modal
        _modalInsumo:   null,
        _modalEliminar: null,
    },

    /* ── LIFECYCLE ── */
    mounted() {
        this._modalInsumo   = new bootstrap.Modal(this.$refs.modalInsumo);
        this._modalEliminar = new bootstrap.Modal(this.$refs.modalEliminar);
        this.cargarDatos();
    },

    /* ── COMPUTED ── */
    computed: {
        insumosFiltrados() {
            let lista = this.insumos;

            if (this.busqueda.trim()) {
                const q = this.busqueda.toLowerCase();
                lista = lista.filter(i => i.nombre.toLowerCase().includes(q) ||
                                         i.categoria_nombre.toLowerCase().includes(q));
            }
            if (this.filtroCategoria) {
                lista = lista.filter(i => String(i.categoria_id) === String(this.filtroCategoria));
            }
            if (this.filtroEstado) {
                if (this.filtroEstado === 'ok')      lista = lista.filter(i => parseFloat(i.stock_actual) >= parseFloat(i.stock_minimo) && parseFloat(i.stock_actual) > 0);
                if (this.filtroEstado === 'bajo')    lista = lista.filter(i => parseFloat(i.stock_actual) < parseFloat(i.stock_minimo) && parseFloat(i.stock_actual) > 0);
                if (this.filtroEstado === 'critico') lista = lista.filter(i => parseFloat(i.stock_actual) === 0 || parseFloat(i.stock_actual) < parseFloat(i.stock_minimo));
            }
            return lista;
        },

        totalPaginas() {
            return Math.ceil(this.insumosFiltrados.length / this.porPagina);
        },

        insumosPaginados() {
            const ini = (this.pagina - 1) * this.porPagina;
            return this.insumosFiltrados.slice(ini, ini + this.porPagina);
        },

        stats() {
            const t = this.insumos;
            return {
                total:      t.length,
                agotados:   t.filter(i => parseFloat(i.stock_actual) === 0).length,
                bajoStock:  t.filter(i => parseFloat(i.stock_actual) < parseFloat(i.stock_minimo)).length,
                valorTotal: t.reduce((s, i) => s + parseFloat(i.stock_actual) * parseFloat(i.precio_unitario), 0)
            };
        }
    },

    watch: {
        busqueda()        { this.pagina = 1; },
        filtroCategoria() { this.pagina = 1; },
        filtroEstado()    { this.pagina = 1; }
    },

    /* ── METHODS ── */
    methods: {

        /* ── CARGA INICIAL DE DATOS (READ) ── */
        async cargarDatos() {
            this.loading = true; 
            try {
                const [rIns, rCat] = await Promise.all([
                    fetch('php/api_insumos.php?action=list'),
                    fetch('php/api_insumos.php?action=categorias')
                ]);
                
                const dIns = await rIns.json();
                const dCat = await rCat.json();
                
                if (dIns.ok)  this.insumos    = dIns.data;
                if (dCat.ok)  this.categorias = dCat.data;
            } catch (e) {
                this.toast('Error al cargar datos del servidor.', 'error');
            } finally {
                this.loading = false;
            }
        },

        /* ── Modal formulario ── */
        openModal(insumo) {
            this.formError    = '';
            this.previewImg   = null;
            this.archivoImagen = null;
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';

            if (insumo) {
                this.editando = true;
                this.form = {
                    id:                insumo.id,
                    nombre:            insumo.nombre,
                    stock_actual:      insumo.stock_actual,
                    stock_minimo:      insumo.stock_minimo,
                    precio_unitario:   insumo.precio_unitario,
                    fecha_vencimiento: insumo.fecha_vencimiento || '',
                    categoria_id:      insumo.categoria_id,
                    imagen_ruta:       insumo.imagen_ruta || ''
                };
            } else {
                this.editando = false;
                this.form = { id: null, nombre: '', stock_actual: '', stock_minimo: '',
                              precio_unitario: '', fecha_vencimiento: '', categoria_id: '', imagen_ruta: '' };
            }
            this._modalInsumo.show();
        },

        onFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                this.formError = 'La imagen no puede superar 2 MB.';
                e.target.value = '';
                return;
            }
            this.archivoImagen = file;
            const reader = new FileReader();
            reader.onload = ev => { this.previewImg = ev.target.result; };
            reader.readAsDataURL(file);
        },

        validarForm() {
            if (!this.form.nombre.trim())           return 'El nombre es obligatorio.';
            if (!this.form.categoria_id)            return 'Selecciona una categoría.';
            if (this.form.stock_actual === '')      return 'Ingresa el stock actual.';
            if (this.form.stock_minimo === '')      return 'Ingresa el stock mínimo.';
            if (this.form.precio_unitario === '')   return 'Ingresa el precio unitario.';
            return '';
        },

        /* ── GUARDAR INSUMO (CREATE / UPDATE) ── */
        async guardarInsumo() {
            this.formError = this.validarForm();
            if (this.formError) return;

            this.guardando = true;
            
            const fd = new FormData();
            
            // la acción dependiendo si se esta creando o editando
            fd.append('action', this.editando ? 'update' : 'create');
            if (this.editando) fd.append('id', this.form.id);
            
            // Agregamos todos los campos de texto
            fd.append('nombre',            this.form.nombre.trim());
            fd.append('stock_actual',      this.form.stock_actual);
            fd.append('stock_minimo',      this.form.stock_minimo);
            fd.append('precio_unitario',   this.form.precio_unitario);
            fd.append('fecha_vencimiento', this.form.fecha_vencimiento || '');
            fd.append('categoria_id',      this.form.categoria_id);
            
            if (this.archivoImagen) fd.append('imagen', this.archivoImagen);

                const res  = await fetch('php/api_insumos.php', { method: 'POST', body: fd });
                const data = await res.json();
                
                if (data.ok) {
                    this.toast(this.editando ? 'Insumo actualizado correctamente.' : 'Insumo creado correctamente.', 'success');
                    this._modalInsumo.hide();
                    
                    await this.cargarDatos();
                } else {
                    this.formError = data.error || 'Error al guardar.';
                }
            } catch (e) {
                this.formError = 'Error de comunicación con el servidor.';
            } finally {
                this.guardando = false;
            }
        },

        /* ── ELIMINAR (DELETE) ── */
        confirmarEliminar(ins) {
            this.eliminando = ins;
            this._modalEliminar.show();
        },

        async eliminarInsumo() {
            if (!this.eliminando) return;
            this.loading = true;
            try {
                const fd = new FormData();
                fd.append('action', 'delete');
                fd.append('id', this.eliminando.id);
                const res  = await fetch('php/api_insumos.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.ok) {
                    this.toast('Insumo eliminado.', 'success');
                    this._modalEliminar.hide();
                    await this.cargarDatos();
                } else {
                    this.toast(data.error || 'Error al eliminar.', 'error');
                }
            } catch (e) {
                this.toast('Error de comunicación.', 'error');
            } finally {
                this.loading  = false;
                this.eliminando = null;
            }
        },

        /* ── EXPORTAR PDF CON VUE ── */
        generarPDF() {
            this.toast('Generando PDF, por favor espera...', 'success');
            this.fechaActual = new Date().toLocaleString();
            
            const element = document.getElementById('pdf-reporte');
            
            // Opciones de html2pdf
            const opt = {
                margin:       10,
                filename:     'Reporte_Inventario_Restaurante.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // Generamos el PDF (html2pdf lee el DOM y lo descarga automáticamente)
            html2pdf().set(opt).from(element).save().then(() => {
                this.toast('PDF descargado exitosamente.', 'success');
            });
        },

        /* ── Helpers ── */
        getStockClass(ins) {
            const act = parseFloat(ins.stock_actual);
            const min = parseFloat(ins.stock_minimo);
            if (act === 0)      return 'stock-critical';
            if (act < min)      return 'stock-low';
            return 'stock-ok';
        },
        getStockIcon(ins) {
            const act = parseFloat(ins.stock_actual);
            const min = parseFloat(ins.stock_minimo);
            if (act === 0)  return 'bi-x-circle-fill';
            if (act < min)  return 'bi-exclamation-triangle-fill';
            return 'bi-check-circle-fill';
        },
        getStockLabel(ins) {
            const act = parseFloat(ins.stock_actual);
            const min = parseFloat(ins.stock_minimo);
            if (act === 0)  return 'Agotado';
            if (act < min)  return 'Bajo';
            return 'OK';
        },

        formatCOP(v) {
            const n = parseFloat(v) || 0;
            if (n >= 1000000) return '$' + (n / 1000000).toFixed(1) + 'M';
            if (n >= 1000)    return '$' + (n / 1000).toFixed(0) + 'K';
            return '$' + n.toLocaleString('es-CO');
        },

        formatFecha(f) {
            if (!f) return '—';
            const [y, m, d] = f.split('-');
            return `${d}/${m}/${y}`;
        },

        isVencido(f) {
            if (!f) return false;
            return new Date(f) < new Date();
        },

        toast(msg, type = 'success') {
            const id = ++this.toastId;
            this.toasts.push({ id, msg, type });
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 3500);
        }
    }
});
