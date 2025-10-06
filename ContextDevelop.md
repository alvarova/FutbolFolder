 INSTRUCCIONES GENERALES OBJETIVO: Desarrollar una webapp completa y funcional para gestión de contenido publicitario en transmisiones deportivas con dos tipos de usuarios diferenciados. LENGUAJE DE PROGRAMACIÓN: PHP 8 + MySQL + HTML5/CSS3/JavaScript ARQUITECTURA: MVC (Modelo-Vista-Controlador) PRIORIDAD: Funcionalidad > Estética (pero mantener diseño profesional) - Mantener el color verde #129026 como identificativo de la Marca Jugar con el nombre preferentemente Deportes LT10 - Radio Universidad, como marca.

🎯 ESPECIFICACIONES TÉCNICAS EXACTAS ESTRUCTURA DE BASE DE DATOS REQUERIDA -- Tabla de usuarios CREATE TABLE usuarios ( id INT PRIMARY KEY AUTO_INCREMENT, username VARCHAR(50) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, rol ENUM('gestor', 'locutor') NOT NULL, fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP );

-- Tabla de carpetas principales CREATE TABLE carpetas ( id INT PRIMARY KEY AUTO_INCREMENT, nombre VARCHAR(100) NOT NULL, fecha_reproduccion DATE NOT NULL, fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP, usuario_id INT, FOREIGN KEY (usuario_id) REFERENCES usuarios(id) );

-- Tabla de PNTs (textos publicitarios) CREATE TABLE pnts ( id INT PRIMARY KEY AUTO_INCREMENT, titulo VARCHAR(100) NOT NULL, contenido TEXT NOT NULL, fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP, usuario_id INT, FOREIGN KEY (usuario_id) REFERENCES usuarios(id) );

-- Tabla de asignaciones (relación PNTs-Carpetas-Subcarpetas) CREATE TABLE asignaciones ( id INT PRIMARY KEY AUTO_INCREMENT, pnt_id INT NOT NULL, carpeta_id INT NOT NULL, subcarpeta ENUM('previa', 'primer_tiempo', 'entretiempo', 'segundo_tiempo', 'final') NOT NULL, orden INT DEFAULT 0, FOREIGN KEY (pnt_id) REFERENCES pnts(id) ON DELETE CASCADE, FOREIGN KEY (carpeta_id) REFERENCES carpetas(id) ON DELETE CASCADE );

ESTRUCTURA DE ARCHIVOS OBLIGATORIA /sistema_deportes/ ├── config/ │ ├── database.php # Configuración BD │ └── config.php # Configuraciones generales ├── controllers/ │ ├── AuthController.php # Login/logout │ ├── CarpetaController.php # CRUD carpetas │ ├── PntController.php # CRUD PNTs │ └── AsignacionController.php # Drag & drop ├── models/ │ ├── Usuario.php │ ├── Carpeta.php │ ├── Pnt.php │ └── Asignacion.php ├── views/ │ ├── auth/ │ │ └── login.php │ ├── gestor/ │ │ ├── dashboard.php │ │ ├── carpetas.php │ │ ├── pnts.php │ │ └── asignaciones.php │ └── locutor/ │ │ ├── dashboard.php │ │ └── visualizar.php ├── assets/ │ ├── css/ │ ├── js/ │ └── images/ ├── api/ │ ├── carpetas.php # AJAX endpoints │ ├── pnts.php │ └── asignaciones.php └── index.php # Punto de entrada

🔧 FUNCIONALIDADES ESPECÍFICAS A IMPLEMENTAR

SISTEMA DE AUTENTICACIÓN // REQUERIMIENTOS EXACTOS:
Hash de passwords con password_hash()
Sesiones PHP seguras
Redirección automática según rol
Logout con destrucción completa de sesión
Validación de login en cada página
MÓDULO GESTOR (ROL ADMINISTRADOR) Dashboard Principal Vista general: Carpetas recientes, PNTs totales, estadísticas Acceso rápido: Botones para crear carpeta/PNT Calendario: Vista de carpetas por fechas Gestión de Carpetas // FUNCIONES OBLIGATORIAS:
crear_carpeta($nombre, $fecha_reproduccion)
editar_carpeta($id, $datos)
eliminar_carpeta($id)
listar_carpetas($filtros = [])
buscar_carpetas($termino)
INTERFAZ REQUERIDA: Tabla responsive con columnas: Nombre, Fecha, Acciones Modal para crear/editar Confirmación para eliminar Paginación (10 elementos por página) Filtros por fecha (desde/hasta) Gestión de PNTs // FUNCIONES OBLIGATORIAS:

crear_pnt($titulo, $contenido)
editar_pnt($id, $datos)
eliminar_pnt($id)
listar_pnts($filtros = [])
buscar_pnts($termino)
INTERFAZ REQUERIDA: Cards responsivas mostrando título y preview del contenido Editor de texto enriquecido (básico) Búsqueda en tiempo real Paginación Modal de vista previa Sistema de Asignaciones (DRAG & DROP) // REQUERIMIENTOS JAVASCRIPT:

Librería: SortableJS o implementación nativa
Drag source: Lista de PNTs disponibles
Drop zones: 5 subcarpetas (previa, primer_tiempo, etc.)
Validación visual: highlight en hover
Persistencia: AJAX automático al soltar
Feedback visual: success/error messages
INTERFAZ OBLIGATORIA: Layout de 2 columnas: PNTs disponibles | Subcarpetas 5 áreas de drop claramente diferenciadas Indicadores visuales durante drag Orden personalizable dentro de cada subcarpeta 3. MÓDULO LOCUTOR (SOLO LECTURA) Lista de Carpetas // LÓGICA OBLIGATORIA:

Mostrar solo carpetas con fecha <= HOY
Ordenar por fecha DESC
Sin opciones de edición/eliminación
Acceso directo a subcarpetas
Visualización de PNTs

<!-- DISEÑO OBLIGATORIO PARA TARJETAS: --> <div class="pnt-card fullscreen"> <div class="pnt-header"> <h2>Título del PNT</h2> <span class="subcarpeta-badge">PRIMER TIEMPO</span> </div> <div class="pnt-content"> <!-- Texto grande y legible --> </div> <div class="pnt-navigation"> <button id="anterior">← Anterior</button> <span class="contador">1 de 5</span> <button id="siguiente">Siguiente →</button> </div> </div>
CSS OBLIGATORIO: .pnt-card { height: 100vh; padding: 40px; font-size: 2rem; display: flex; flex-direction: column; justify-content: center; }

.pnt-content { flex: 1; display: flex; align-items: center; text-align: center; }

/* Responsive obligatorio */ @media (max-width: 768px) { .pnt-card { font-size: 1.5rem; padding: 20px; } }

🎨 ESPECIFICACIONES DE DISEÑO PALETA DE COLORES OBLIGATORIA :root { --primary: #129026; /* Verde principal / --secondary: #64748b; / Gris secundario / --success: #16a34a; / Verde éxito / --danger: #dc2626; / Rojo peligro / --warning: #ea580c; / Naranja advertencia / --bg-light: #f8fafc; / Fondo claro / --bg-dark: #1e293b; / Fondo oscuro / --text-dark: #0f172a; / Texto oscuro / --text-light: #64748b; / Texto claro */ }

COMPONENTES UI OBLIGATORIOS

<!-- Botón primario -->
<button class="btn btn-primary">Texto</button>

<!-- Modal estándar --> <div class="modal" id="modalId"> <div class="modal-content"> <div class="modal-header"> <h3>Título</h3> <span class="close">&times;</span> </div> <div class="modal-body">Contenido</div> <div class="modal-footer"> <button class="btn btn-secondary">Cancelar</button> <button class="btn btn-primary">Guardar</button> </div> </div> </div> <!-- Cards responsivas --> <div class="card"> <div class="card-header">Header</div> <div class="card-body">Body</div> <div class="card-footer">Footer</div> </div>
🔍 FUNCIONES DE BÚSQUEDA OBLIGATORIAS Búsqueda de PNTs function buscar_pnts($termino, $filtros = []) { $sql = "SELECT * FROM pnts WHERE (titulo LIKE ? OR contenido LIKE ?)";

if (!empty($filtros['fecha_desde'])) {
    $sql .= " AND fecha_creacion >= ?";
}

if (!empty($filtros['fecha_hasta'])) {
    $sql .= " AND fecha_creacion <= ?";
}

$sql .= " ORDER BY fecha_creacion DESC";

// Implementar consulta preparada

}

Búsqueda de Carpetas function buscar_carpetas($termino, $filtros = []) { $sql = "SELECT c.*, COUNT(a.id) as total_pnts FROM carpetas c LEFT JOIN asignaciones a ON c.id = a.carpeta_id WHERE c.nombre LIKE ?";

if (!empty($filtros['fecha_reproduccion'])) {
    $sql .= " AND c.fecha_reproduccion = ?";
}

$sql .= " GROUP BY c.id ORDER BY c.fecha_reproduccion DESC";

// Implementar consulta preparada

}

📱 RESPONSIVIDAD OBLIGATORIA Breakpoints Requeridos /* Mobile First Approach */ .container { max-width: 100%; }

@media (min-width: 576px) { .container { max-width: 540px; } }

@media (min-width: 768px) { .container { max-width: 720px; } }

@media (min-width: 992px) { .container { max-width: 960px; } }

@media (min-width: 1200px) { .container { max-width: 1140px; } }

Grid System Obligatorio .row { display: flex; flex-wrap: wrap; } .col-1 { flex: 0 0 8.333333%; } .col-2 { flex: 0 0 16.666667%; } .col-3 { flex: 0 0 25%; } .col-4 { flex: 0 0 33.333333%; } .col-6 { flex: 0 0 50%; } .col-8 { flex: 0 0 66.666667%; } .col-12 { flex: 0 0 100%; }

🔐 SEGURIDAD OBLIGATORIA Validaciones PHP // Sanitización obligatoria function sanitizar_entrada($data) { return htmlspecialchars(strip_tags(trim($data))); }

// Validación de sesión obligatoria function verificar_sesion() { if (!isset($_SESSION['usuario_id'])) { header('Location: /login.php'); exit(); } }

// Validación de rol obligatoria function verificar_rol($rol_requerido) { if ($_SESSION['rol'] !== $rol_requerido) { header('HTTP/1.0 403 Forbidden'); exit('Acceso denegado'); } }

Configuración de Seguridad // config/config.php session_start(); session_regenerate_id(true);

// Headers de seguridad header('X-Content-Type-Options: nosniff'); header('X-Frame-Options: DENY'); header('X-XSS-Protection: 1; mode=block');

// Configuración de errores (producción) error_reporting(0); ini_set('display_errors', 0);

🚨 VALIDACIONES Y ERRORES OBLIGATORIOS Validaciones Frontend (JavaScript) // Validación de formularios obligatoria function validarFormularioCarpeta() { const nombre = document.getElementById('nombre').value; const fecha = document.getElementById('fecha_reproduccion').value;

if (nombre.length < 3) {
    mostrarError('El nombre debe tener al menos 3 caracteres');
    return false;
}

if (!fecha || new Date(fecha) < new Date()) {
    mostrarError('La fecha debe ser futura');
    return false;
}

return true;

}

// Sistema de notificaciones obligatorio function mostrarNotificacion(mensaje, tipo = 'info') { const notification = document.createElement('div'); notification.className = notification notification-${tipo}; notification.textContent = mensaje; document.body.appendChild(notification);

setTimeout(() => {
    notification.remove();
}, 5000);

}

📊 DATOS DE PRUEBA OBLIGATORIOS -- Insertar datos de prueba INSERT INTO usuarios (username, password, rol) VALUES ('admin', '$2y$10$ejemplo_hash_password', 'gestor'), ('locutor1', '$2y$10$ejemplo_hash_password', 'locutor');

INSERT INTO carpetas (nombre, fecha_reproduccion, usuario_id) VALUES ('Partido River vs Boca', '2025-06-15', 1), ('Clasificatorias Mundial', '2025-06-12', 1), ('Liga Profesional - Fecha 10', '2025-06-10', 1);

INSERT INTO pnts (titulo, contenido, usuario_id) VALUES ('Promoción Banco Nación', 'El Banco Nación te acompaña en cada gol. Préstamos personales al 15% anual.', 1), ('Cerveza Quilmes', 'Quilmes, el sabor del fútbol argentino. Disponible en todos los kioscos del estadio.', 1), ('Telecom Sponsor', 'Con Telecom, conectate con la pasión del fútbol. Fibra óptica en tu hogar.', 1);

✅ CHECKLIST DE ENTREGA OBLIGATORIO Funcionalidades Core [ ] Sistema de login funcional con dos roles [ ] CRUD completo de carpetas (solo gestor) [ ] CRUD completo de PNTs (solo gestor) [ ] Sistema drag & drop para asignaciones [ ] Búsqueda en tiempo real de PNTs y carpetas [ ] Visualización fullscreen para locutor [ ] Navegación anterior/siguiente en PNTs [ ] Filtrado temporal (ocultar carpetas futuras al locutor) Aspectos Técnicos [ ] Base de datos optimizada con índices [ ] Consultas preparadas en todas las queries [ ] Validación frontend y backend [ ] Manejo de errores robusto [ ] Responsive design completo [ ] Carga rápida (< 3 segundos) Seguridad [ ] Hashing seguro de passwords [ ] Validación de sesiones [ ] Sanitización de entradas [ ] Protección CSRF [ ] Headers de seguridad UX/UI [ ] Interfaz intuitiva y limpia [ ] Feedback visual en todas las acciones [ ] Loading states en operaciones AJAX [ ] Confirmaciones para acciones destructivas [ ] Navegación clara y lógica

🎯 INSTRUCCIONES FINALES PARA LA IA PRIORIZA LA FUNCIONALIDAD: El sistema debe funcionar perfectamente antes que verse bonito USA CÓDIGO LIMPIO: Comentarios, indentación, nombres descriptivos IMPLEMENTA TODA LA SEGURIDAD: No omitas validaciones ni sanitización TESTING OBLIGATORIO: Prueba cada funcionalidad antes de entregar DOCUMENTA TODO: Código comentado y README detallado RESPONSIVE FIRST: Mobile-first approach obligatorio PERFORMANCE: Optimiza consultas y carga de assets ENTREGA FINAL ESPERADA: Código fuente completo y funcional Base de datos con estructura y datos de prueba Documentación de instalación Manual de usuario básico Listado de credenciales de acceso

Este brochure contiene TODAS las especificaciones necesarias para desarrollar la webapp completa. No omitas ningún aspecto mencionado.


App Blueprint
User Authentication — User authentication system with 'gestor' (manager) and 'locutor' (announcer) roles, securing access based on roles.
Gestor Dashboard — Dashboard for 'gestor' role with an overview of recent folders, total PNTs, and quick access to create folders/PNTs. Calendar view for scheduling content.
Folder Management — Management of 'carpetas' (folders) for organizing content, allowing creation, editing, deletion, listing, and searching of folders.
PNT Management — Management of 'PNTs' (advertising texts) with CRUD operations, including a basic rich text editor and real-time search.
PNT Assignment — Drag-and-drop interface for assigning PNTs to different subfolders (previa, primer_tiempo, entretiempo, segundo_tiempo, final) using SortableJS.
Locutor View — Display of folders with PNTs available only to 'locutor' (announcer) role, showing only current or past dated content, optimized for quick access during broadcasts.
AI PNT Suggestion
 — AI-powered tool that suggests PNT content ideas based on the sports event or time of broadcast using a LLM.
Color
Layout
Responsive grid layout that adapts to different screen sizes, ensuring optimal content display on both desktop and mobile devices.
Typography
Font pairing: 'Space Grotesk' (sans-serif) for headlines and 'Inter' (sans-serif) for body text.
Iconography
Minimalist icons representing different sports and advertising concepts, using the primary color as the fill and the background color for the outline.
Animation
Subtle transitions and animations on content loading and user interactions to provide a smooth and engaging user experience.

UI
TypeScript, NextJS, Tailwind CSS

Prototype this App
