// Sistema de Administración de Kurunta Yoga - VERSIÓN CORREGIDA

// Crear página de administración automáticamente
function create_yoga_admin_page() {
    $admin_page = get_page_by_path('yoga-admin');
    
    if (!$admin_page) {
        $page_data = array(
            'post_title'   => 'Administración Kurunta Yoga',
            'post_content' => '[yoga_admin_panel]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_name'    => 'yoga-admin'
        );
        
        wp_insert_post($page_data);
    }
}
add_action('init', 'create_yoga_admin_page');

// Verificar y crear tablas si no existen
function ensure_yoga_tables_exist() {
    global $wpdb;
    
    $classes_table = $wpdb->prefix . 'yoga_classes';
    $reservations_table = $wpdb->prefix . 'yoga_reservations';
    
    // Verificar si la tabla de clases existe
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$classes_table'");
    
    if ($table_exists != $classes_table) {
        // Crear tabla de clases
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql_classes = "CREATE TABLE $classes_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            class_id varchar(255) NOT NULL UNIQUE,
            name varchar(255) NOT NULL,
            instructor varchar(255) NOT NULL,
            date date NOT NULL,
            time time NOT NULL,
            duration int(11) NOT NULL,
            max_spots int(11) NOT NULL,
            available_spots int(11) NOT NULL,
            period varchar(20) NOT NULL,
            is_recurring tinyint(1) DEFAULT 0,
            recurring_frequency varchar(20) DEFAULT NULL,
            recurring_end_date date DEFAULT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_classes);
    }
    
    // Verificar si la tabla de reservas existe
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$reservations_table'");
    
    if ($table_exists != $reservations_table) {
        // Crear tabla de reservas
        $sql_reservations = "CREATE TABLE $reservations_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            class_id varchar(255) NOT NULL,
            user_name varchar(255) NOT NULL,
            user_email varchar(255) NOT NULL,
            user_phone varchar(20),
            status varchar(20) DEFAULT 'confirmed',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        dbDelta($sql_reservations);
    }
}

// Limpiar clases antigas o duplicadas
function clean_old_yoga_classes() {
    global $wpdb;
    
    // Eliminar clases pasadas (más de 7 días)
    $wpdb->query("
        DELETE FROM {$wpdb->prefix}yoga_classes 
        WHERE date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    
    // Eliminar reservas huérfanas
    $wpdb->query("
        DELETE r FROM {$wpdb->prefix}yoga_reservations r
        LEFT JOIN {$wpdb->prefix}yoga_classes c ON r.class_id = c.class_id
        WHERE c.class_id IS NULL
    ");
}

// Ejecutar verificaciones al cargar
add_action('init', 'ensure_yoga_tables_exist');
add_action('wp_loaded', 'clean_old_yoga_classes');

// Cargar scripts necesarios para el admin
function yoga_admin_enqueue_scripts() {
    if (is_page('yoga-admin')) {
        wp_enqueue_script('jquery');
        wp_enqueue_style('dashicons');
        
        // Crear nonce para AJAX
        wp_localize_script('jquery', 'yoga_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yoga_admin_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'yoga_admin_enqueue_scripts');

// Shortcode para el panel de administración
function yoga_admin_panel_shortcode($atts) {
    // Verificar permisos (solo administradores)
    if (!current_user_can('manage_options')) {
        return '<div class="yoga-admin-error">
                    <h3>Acceso Denegado</h3>
                    <p>Solo los administradores pueden acceder a esta página.</p>
                    <a href="' . wp_login_url() . '">Iniciar Sesión</a>
                </div>';
    }
    
    ob_start();
    yoga_render_admin_interface();
    return ob_get_clean();
}
add_shortcode('yoga_admin_panel', 'yoga_admin_panel_shortcode');

// ========== HANDLERS AJAX ==========

// Registrar las acciones AJAX
add_action('wp_ajax_yoga_admin_action', 'yoga_handle_admin_ajax');
add_action('wp_ajax_nopriv_yoga_admin_action', 'yoga_handle_admin_ajax');

// Función principal que maneja todas las acciones AJAX
function yoga_handle_admin_ajax() {
    // Verificar nonce
    if (!wp_verify_nonce($_POST['nonce'], 'yoga_admin_nonce')) {
        wp_send_json_error('Error de seguridad');
        return;
    }
    
	    // DEBUGGING TEMPORAL - QUITAR DESPUÉS DE PROBAR
    error_log('YOGA ADMIN AJAX: ' . $_POST['action_type']);
    error_log('POST DATA: ' . print_r($_POST, true));
	
    $action_type = sanitize_text_field($_POST['action_type']);
    
    switch ($action_type) {
        case 'get_class_reservations':
            yoga_get_class_reservations();
            break;
        case 'save_settings':
            yoga_save_settings();
            break;
        case 'cleanup_old_classes':
            yoga_cleanup_old_classes();
            break;
        case 'create_recurring_classes':
            yoga_create_recurring_classes();
            break;
        case 'save_class':
            handle_save_class();
            break;
        case 'save_recurring_classes':
            handle_save_recurring_classes();
            break;
        case 'get_class_data':
            handle_get_class_data();
            break;
        case 'delete_class':
            handle_delete_class();
            break;
        case 'delete_all_classes':
            handle_delete_all_classes();
            break;
        case 'duplicate_class':
            handle_duplicate_class();
            break;
        case 'change_reservation_status':
            handle_change_reservation_status();
            break;
        default:
            wp_send_json_error('Acción no válida');
    }
}

// Handler específico para obtener reservas de una clase
function yoga_get_class_reservations() {
    global $wpdb;
    $class_id = sanitize_text_field($_POST['class_id']);
    
    // Obtener información de la clase
    $class_info = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}yoga_classes 
        WHERE class_id = %s
    ", $class_id));
    
    if (!$class_info) {
        wp_send_json_error('Clase no encontrada');
        return;
    }
    
    // Obtener reservas de la clase
    $reservations = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}yoga_reservations 
        WHERE class_id = %s AND status = 'confirmed'
        ORDER BY created_at ASC
    ", $class_id));
    
    wp_send_json_success([
        'class_info' => [
            'name' => $class_info->name,
            'date' => date('d/m/Y', strtotime($class_info->date)),
            'time' => date('H:i', strtotime($class_info->time)),
            'instructor' => $class_info->instructor,
            'max_spots' => $class_info->max_spots
        ],
        'reservations' => $reservations
    ]);
}

// Handler para guardar configuración
function yoga_save_settings() {
    $settings = [
        'default_instructor' => sanitize_text_field($_POST['default_instructor']),
        'default_max_spots' => intval($_POST['default_max_spots']),
        'email_from' => sanitize_email($_POST['email_from']),
        'email_from_name' => sanitize_text_field($_POST['email_from_name']),
        'admin_notifications' => isset($_POST['admin_notifications']) ? 1 : 0,
        'studio_address' => sanitize_textarea_field($_POST['studio_address']),
        'studio_phone' => sanitize_text_field($_POST['studio_phone']),
        'studio_instagram' => sanitize_text_field($_POST['studio_instagram'])
    ];
    
    foreach ($settings as $key => $value) {
        update_option('yoga_' . $key, $value);
    }
    
    wp_send_json_success('Configuración guardada exitosamente');
}

// Handler para limpiar clases antiguas
function yoga_cleanup_old_classes() {
    global $wpdb;
    
    // Eliminar clases pasadas (más de 7 días)
    $deleted_classes = $wpdb->query("
        DELETE FROM {$wpdb->prefix}yoga_classes 
        WHERE date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    
    // Eliminar reservas huérfanas
    $deleted_reservations = $wpdb->query("
        DELETE r FROM {$wpdb->prefix}yoga_reservations r
        LEFT JOIN {$wpdb->prefix}yoga_classes c ON r.class_id = c.class_id
        WHERE c.class_id IS NULL
    ");
    
    wp_send_json_success("Limpieza completada. $deleted_classes clases y $deleted_reservations reservas eliminadas.");
}

// Handler para crear clases recurrentes
function yoga_create_recurring_classes() {
    global $wpdb;
    
    // Sanitizar datos
    $name = sanitize_text_field($_POST['name']);
    $instructor = sanitize_text_field($_POST['instructor']);
    $duration = intval($_POST['duration']);
    $max_spots = intval($_POST['max_spots']);
    $time = sanitize_text_field($_POST['time']);
    $frequency = sanitize_text_field($_POST['frequency']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);
    $selected_days = isset($_POST['selected_days']) ? $_POST['selected_days'] : [];
    
    // Validar datos
    if (empty($name) || empty($instructor) || empty($time) || empty($start_date) || empty($end_date)) {
        wp_send_json_error('Todos los campos son obligatorios');
        return;
    }
    
    $created_classes = 0;
    $current_date = new DateTime($start_date);
    $end_date_obj = new DateTime($end_date);
    
    // Mapear días de la semana
    $day_map = [
        'Lunes' => 1,
        'Martes' => 2,
        'Miércoles' => 3,
        'Jueves' => 4,
        'Viernes' => 5,
        'Sábado' => 6,
        'Domingo' => 0
    ];
    
    $selected_day_numbers = [];
    foreach ($selected_days as $day) {
        if (isset($day_map[$day])) {
            $selected_day_numbers[] = $day_map[$day];
        }
    }
    
    // Crear clases
    while ($current_date <= $end_date_obj) {
        $day_of_week = $current_date->format('w');
        
        if (in_array($day_of_week, $selected_day_numbers)) {
            $result = $wpdb->insert(
                $wpdb->prefix . 'yoga_classes',
                [
                    'name' => $name,
                    'instructor' => $instructor,
                    'date' => $current_date->format('Y-m-d'),
                    'time' => $time,
                    'duration' => $duration,
                    'max_spots' => $max_spots,
                    'available_spots' => $max_spots,
                    'is_recurring' => 1,
                    'created_at' => current_time('mysql')
                ]
            );
            
            if ($result) {
                $created_classes++;
            }
        }
        
        // Avanzar según la frecuencia
        $current_date->add(new DateInterval('P1D'));
    }
    
    wp_send_json_success("Se crearon $created_classes clases recurrentes exitosamente");
}

// Interfaz principal de administración
function yoga_render_admin_interface() {
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
    ?>
    
    <div class="yoga-admin-container">
        <!-- Header de la administración -->
        <div class="yoga-admin-header">
            <div class="admin-logo">
                <div class="logo-image">
                    <img src="https://kuruntayoga.com.mx/wp-content/uploads/2025/06/cropped-cropped-logoVerde.png" alt="Kurunta Yoga" />
                </div>
                <div class="logo-info">
                    <h1>Panel de Administración</h1>
                    <p>Bienvenida Ana</p>
                </div>
            </div>
            <div class="admin-user-info">
                <span>Bienvenido, <?php echo wp_get_current_user()->display_name; ?></span>
                <a href="<?php echo wp_logout_url(); ?>" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>

        <!-- Navegación por tabs -->
        <div class="yoga-admin-nav">
            <button class="nav-tab <?php echo $current_tab == 'dashboard' ? 'active' : ''; ?>" 
                    onclick="switchTab('dashboard')">
                <span class="dashicons dashicons-dashboard"></span>
                Dashboard
            </button>
            <button class="nav-tab <?php echo $current_tab == 'classes' ? 'active' : ''; ?>" 
                    onclick="switchTab('classes')">
                <span class="dashicons dashicons-calendar-alt"></span>
                Gestionar Clases
            </button>
            <button class="nav-tab <?php echo $current_tab == 'reservations' ? 'active' : ''; ?>" 
                    onclick="switchTab('reservations')">
                <span class="dashicons dashicons-groups"></span>
                Reservas
            </button>
            <button class="nav-tab <?php echo $current_tab == 'settings' ? 'active' : ''; ?>" 
                    onclick="switchTab('settings')">
                <span class="dashicons dashicons-admin-settings"></span>
                Configuración
            </button>
        </div>

        <!-- Contenido principal con pestañas -->
        <div class="yoga-admin-content">
            <!-- Dashboard Tab -->
            <div id="dashboard-tab" class="tab-content <?php echo $current_tab == 'dashboard' ? 'active' : ''; ?>">
                <?php if ($current_tab == 'dashboard') yoga_render_dashboard(); ?>
            </div>

            <!-- Classes Tab -->
            <div id="classes-tab" class="tab-content <?php echo $current_tab == 'classes' ? 'active' : ''; ?>">
                <?php if ($current_tab == 'classes') yoga_render_classes_management(); ?>
            </div>

            <!-- Reservations Tab -->
            <div id="reservations-tab" class="tab-content <?php echo $current_tab == 'reservations' ? 'active' : ''; ?>">
                <?php if ($current_tab == 'reservations') yoga_render_reservations_management(); ?>
            </div>

            <!-- Settings Tab -->
            <div id="settings-tab" class="tab-content <?php echo $current_tab == 'settings' ? 'active' : ''; ?>">
                <?php if ($current_tab == 'settings') yoga_render_settings(); ?>
            </div>
        </div>
    </div>

    <script>
    function switchTab(tabName) {
        // Redirigir con el parámetro tab para que PHP se ejecute
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        
        // Mostrar loading visual mientras redirige
        const currentTab = document.querySelector('.tab-content.active');
        if (currentTab) {
            currentTab.innerHTML = '<div style="text-align: center; padding: 40px;"><span class="dashicons dashicons-update spin" style="font-size: 30px;"></span><br>Cargando...</div>';
        }
        
        // Redirigir para que PHP se ejecute correctamente
        window.location.href = url.toString();
    }

    // Función para mostrar notificaciones
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    </script>
    
    <!-- CSS LIMPIO Y ORGANIZADO -->
    <style>
    /* ===== ESTILOS PRINCIPALES ===== */

    /* Header y Logo - Escritorio */
    @media (min-width: 769px) {
        .yoga-admin-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e9ecef;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            min-height: 120px;
        }

        .admin-logo {
            display: flex;
            align-items: center;
            gap: 25px;
            flex: 1;
        }

        .logo-image {
            width: 140px;
            height: 140px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            background: white;
            padding: 12px;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        .logo-image:hover {
            transform: scale(1.05);
        }

        .logo-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 8px;
            content: url('https://kuruntayoga.com.mx/wp-content/uploads/2025/06/cropped-cropped-logoVerde.png') !important;
        }

        .logo-info h1 {
            margin: 0;
            font-size: 28px;
            color: #2d3748;
            font-weight: 600;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .logo-info p {
            margin: 6px 0 0 0;
            color: #718096;
            font-size: 16px;
            font-weight: 400;
        }

        .admin-user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
            color: #4a5568;
            font-size: 14px;
        }

        .logout-btn {
            background: linear-gradient(135deg, #8fbc8f 0%, #7ab87a 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(143, 188, 143, 0.3);
            font-size: 14px;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #7ab87a 0%, #6ba56b 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(143, 188, 143, 0.4);
            color: white;
            text-decoration: none;
        }

        /* BOTONES COMPACTOS PARA DESKTOP */
      .classes-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 25px !important;
    padding: 20px 25px !important;
    border-bottom: 1px solid #e2e8f0 !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}
        
        .classes-header h2 {
            font-size: 22px !important;
            margin: 0 !important;
            color: #2d3748 !important;
            font-weight: 600 !important;
        }
        
       .header-actions {
    display: flex !important;
    gap: 15px !important;
    flex-wrap: wrap !important;
    justify-content: flex-end !important;
    align-items: center !important;
}
        
        .action-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 8px 12px !important;
            border-radius: 6px !important;
            border: none !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            white-space: nowrap !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
        }
        
        .action-btn .dashicons {
            font-size: 16px !important;
            margin-right: 6px !important;
            line-height: 1 !important;
        }
        
        .action-btn.primary {
            background: linear-gradient(135deg, #8fbc8f 0%, #7ab87a 100%) !important;
            color: white !important;
        }
        
        .action-btn.primary:hover {
            background: linear-gradient(135deg, #7ab87a 0%, #6ba66b 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 6px rgba(143, 188, 143, 0.3) !important;
        }
        
        .action-btn.secondary {
            background: #f8fafc !important;
            color: #4a5568 !important;
            border: 1px solid #e2e8f0 !important;
        }
        
        .action-btn.secondary:hover {
            background: #e2e8f0 !important;
            border-color: #cbd5e0 !important;
            transform: translateY(-1px) !important;
        }
        
        .action-btn.danger {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%) !important;
            color: white !important;
        }
        
        .action-btn.danger:hover {
            background: linear-gradient(135deg, #c53030 0%, #9c2626 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 6px rgba(229, 62, 62, 0.3) !important;
        }
    }

    /* ===== ESTILOS MÓVIL ===== */
    @media (max-width: 768px) {
        /* Logo verde para móvil */
        .logo-image img {
            content: url('https://kuruntayoga.com.mx/wp-content/uploads/2025/06/logo-fondo-verde.png') !important;
        }
        
        .logo-image {
            background: transparent !important;
            padding: 0 !important;
        }

        /* Contenedor principal con márgenes balanceados */
        .classes-management {
            padding: 10px !important;
            margin: 0 auto !important;
            width: 100% !important;
            max-width: 400px !important;
            text-align: center !important;
            background: #f8f9fa !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
        }
        
        /* HEADER MÓVIL CORREGIDO */
.classes-header {
    width: 100% !important;
    margin-bottom: 20px !important;
    text-align: center !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    gap: 15px !important;
    padding: 15px 15px !important;
    box-sizing: border-box !important;
}

.classes-header h2 {
    font-size: 18px !important;
    margin: 0 !important;
    color: #2d3748 !important;
    text-align: center !important;
}

/* BOTONES EN COLUMNA VERTICAL PARA MÓVIL */
.header-actions {
    display: flex !important;
    flex-direction: column !important;
    gap: 10px !important;
    width: 100% !important;
    max-width: 280px !important;
    margin: 0 auto !important;
}

.action-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 12px 16px !important;
    border-radius: 8px !important;
    border: none !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    min-height: 45px !important;
    width: 100% !important;
}

        .action-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 8px 12px !important;
            border-radius: 6px !important;
            border: none !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            white-space: nowrap !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
            min-height: 40px !important;
        }

        .action-btn .dashicons {
            font-size: 16px !important;
            margin-right: 6px !important;
            line-height: 1 !important;
        }

        .action-btn.primary {
            background: linear-gradient(135deg, #8fbc8f 0%, #7ab87a 100%) !important;
            color: white !important;
        }

        .action-btn.primary:hover {
            background: linear-gradient(135deg, #7ab87a 0%, #6ba66b 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 6px rgba(143, 188, 143, 0.3) !important;
        }

        .action-btn.secondary {
            background: #f8fafc !important;
            color: #4a5568 !important;
            border: 1px solid #e2e8f0 !important;
        }

        .action-btn.secondary:hover {
            background: #e2e8f0 !important;
            border-color: #cbd5e0 !important;
            transform: translateY(-1px) !important;
        }

        .action-btn.danger {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%) !important;
            color: white !important;
            grid-column: 1 / 3 !important;
            max-width: 200px !important;
            margin: 0 auto !important;
        }

        .action-btn.danger:hover {
            background: linear-gradient(135deg, #c53030 0%, #9c2626 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 6px rgba(229, 62, 62, 0.3) !important;
        }
		
        /* FILTROS ÚNICOS Y FUNCIONALES */
        .classes-filters {
            width: 100% !important;
            max-width: 400px !important;
            margin: 0 auto 20px auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            padding: 0 10px !important;
            box-sizing: border-box !important;
        }

       .filter-group {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    grid-template-rows: auto auto !important;
    gap: 8px !important;
    margin-bottom: 15px !important;
    width: 100% !important;
}

        .filter-btn[data-filter="all"] {
            grid-column: 1 / 2 !important;
            grid-row: 1 !important;
        }

        .filter-btn[data-filter="today"] {
            grid-column: 2 / 3 !important;
            grid-row: 1 !important;
        }

        .filter-btn[data-filter="month"] {
            grid-column: 3 / 4 !important;
            grid-row: 1 !important;
        }

        .filter-btn[data-filter="week"] {
            grid-column: 1 / 3 !important;
            grid-row: 2 !important;
        }

        .filter-btn[data-filter="recurring"] {
            grid-column: 3 / 4 !important;
            grid-row: 2 !important;
        }

        .filter-btn {
    padding: 12px 6px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    text-align: center !important;
    border-radius: 8px !important;
    border: 2px solid #e2e8f0 !important;
    background: white !important;
    color: #4a5568 !important;
    min-height: 45px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1) !important;
    -webkit-tap-highlight-color: transparent !important;
    user-select: none !important;
}

/* MÁRGENES PARA LOS FILTROS */
.classes-filters {
    width: 100% !important;
    max-width: 400px !important;
    margin: 15px auto 20px auto !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    padding: 0 10px !important;
    box-sizing: border-box !important;
}

.filter-group {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    grid-template-rows: auto auto !important;
    gap: 8px !important;
    margin-bottom: 15px !important;
    width: 100% !important;
}

.search-group {
    position: relative !important;
    width: 100% !important;
    max-width: 350px !important;
    margin: 0 auto !important;
}

.search-group input {
    width: 100% !important;
    padding: 12px 45px 12px 15px !important;
    font-size: 15px !important;
    border-radius: 12px !important;
    border: 2px solid #e2e8f0 !important;
    background: white !important;
    box-sizing: border-box !important;
    outline: none !important;
}

.search-group .dashicons {
    position: absolute !important;
    right: 15px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    font-size: 18px !important;
    color: #999 !important;
    pointer-events: none !important;
}
        .search-group input:focus {
            border-color: #8fbc8f !important;
            box-shadow: 0 0 0 3px rgba(143, 188, 143, 0.2) !important;
        }

        .search-group .dashicons {
            position: absolute !important;
            right: 15px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            font-size: 18px !important;
            color: #999 !important;
            pointer-events: none !important;
        }
        
        /* GRID DE CLASES CENTRADO */
        .classes-grid {
            display: flex !important;
            flex-direction: column !important;
            gap: 15px !important;
            width: 100% !important;
            max-width: 360px !important;
            margin: 0 auto !important;
            padding: 0 !important;
            align-items: center !important;
        }
        
        /* CUADROS PERFECTAMENTE CENTRADOS */
        .class-card {
            width: 100% !important;
            max-width: 340px !important;
            margin: 0 auto !important;
            padding: 15px !important;
            border-radius: 12px !important;
            background: white !important;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1) !important;
            border: 1px solid #e2e8f0 !important;
            position: relative !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
            display: block !important;
        }
        
        /* Header del cuadro */
        .class-card-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            margin-bottom: 12px !important;
            padding-bottom: 8px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        
        .class-card-header h3 {
            font-size: 17px !important;
            margin: 0 !important;
            flex: 1 !important;
            line-height: 1.3 !important;
            color: #2d3748 !important;
            font-weight: 600 !important;
            padding-right: 10px !important;
            text-align: left !important;
        }
        
        /* Botones de acción centrados */
        .class-actions {
            display: flex !important;
            gap: 6px !important;
            flex-shrink: 0 !important;
            justify-content: center !important;
        }
        
        .action-icon {
            width: 34px !important;
            height: 34px !important;
            padding: 8px !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
            transition: all 0.2s ease !important;
        }
        
        .action-icon:hover {
            background: #e2e8f0 !important;
            transform: scale(1.05) !important;
        }
        
        .action-icon.edit-class {
            color: #3182ce !important;
        }
        
        .action-icon.duplicate-class {
            color: #38a169 !important;
        }
        
        .action-icon.delete-class {
            color: #e53e3e !important;
        }
        
        .action-icon .dashicons {
            font-size: 16px !important;
        }
    }
    
    /* Solo hover en desktop, NO en móvil para filtros */
    @media (min-width: 769px) {
        .filter-btn:hover {
            background: #f8fafc !important;
            border-color: #8fbc8f !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15) !important;
        }
    }
    }
    </style>
    <?php
}

// Dashboard
function yoga_render_dashboard() {
    global $wpdb;
    
    // Configurar zona horaria de Ciudad de México
    date_default_timezone_set('America/Mexico_City');
    
    // Asegurar que las tablas existan
    ensure_yoga_tables_exist();
    
    // Obtener fecha actual en zona horaria de México
    $today = date('Y-m-d');
    $current_time = current_time('mysql');
    
    // Obtener estadísticas básicas
    $total_classes = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}yoga_classes WHERE date >= %s", $today)) ?: 0;
    $total_reservations = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}yoga_reservations WHERE status = 'confirmed'") ?: 0;
    $today_classes = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}yoga_classes WHERE date = %s", $today)) ?: 0;
    $this_week_reservations = $wpdb->get_var("
        SELECT COUNT(*) FROM {$wpdb->prefix}yoga_reservations 
        WHERE status = 'confirmed' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ") ?: 0;
    
    // Próximas clases
    $upcoming_classes = $wpdb->get_results($wpdb->prepare("
        SELECT c.*, 
               (SELECT COUNT(*) FROM {$wpdb->prefix}yoga_reservations r 
                WHERE r.class_id = c.class_id AND r.status = 'confirmed') as reserved_spots
        FROM {$wpdb->prefix}yoga_classes c 
        WHERE c.date >= %s 
        ORDER BY c.date ASC, c.time ASC 
        LIMIT 5
    ", $today));
    
    // Actividad reciente
    $recent_activity = $wpdb->get_results("
        SELECT 
            r.user_name, 
            r.user_email, 
            r.created_at, 
            r.status,
            c.name as class_name, 
            c.date, 
            c.time,
            'reservation' as activity_type
        FROM {$wpdb->prefix}yoga_reservations r
        LEFT JOIN {$wpdb->prefix}yoga_classes c ON r.class_id = c.class_id
        WHERE r.created_at >= DATE_SUB(NOW(), INTERVAL 48 HOUR)
        ORDER BY r.created_at DESC
        LIMIT 10
    ");
    ?>
    
    <div class="dashboard-content">
        <!-- Estadísticas principales -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-calendar-alt"></span>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_classes; ?></h3>
                    <p class="stat-text-desktop">Clases Programadas</p>
                    <p class="stat-text-mobile">Clases</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-groups"></span>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_reservations; ?></h3>
                    <p class="stat-text-desktop">Reservas Totales</p>
                    <p class="stat-text-mobile">Reservas</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-clock"></span>
                </div>
                <div class="stat-info">
                    <h3><?php echo $today_classes; ?></h3>
                    <p class="stat-text-desktop">Clases Hoy</p>
                    <p class="stat-text-mobile">Hoy</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
                <div class="stat-info">
                    <h3><?php echo $this_week_reservations; ?></h3>
                    <p class="stat-text-desktop">Reservas Esta Semana</p>
                    <p class="stat-text-mobile">Semana</p>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="quick-actions">
            <h2>Acciones Rápidas</h2>
            <div class="action-buttons">
                <button class="action-btn primary" onclick="switchTab('classes'); setTimeout(showAddClassForm, 100);">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <span class="btn-text-desktop">Nueva Clase Individual</span>
                    <span class="btn-text-mobile">Nueva Clase</span>
                </button>
                <button class="action-btn primary" onclick="switchTab('classes'); setTimeout(showRecurringClassForm, 100);">
                    <span class="dashicons dashicons-update"></span>
                    <span class="btn-text-desktop">Programar Clases Recurrentes</span>
                    <span class="btn-text-mobile">Clases Recurrentes</span>
                </button>
                <button class="action-btn secondary" onclick="switchTab('classes')">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <span class="btn-text-desktop">Ver Todas las Clases</span>
                    <span class="btn-text-mobile">Ver Clases</span>
                </button>
                
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Próximas clases -->
            <div class="dashboard-section">
                <h2>Próximas Clases</h2>
                <div class="upcoming-classes">
                    <?php if (!empty($upcoming_classes)): ?>
                        <?php foreach ($upcoming_classes as $class): ?>
                            <div class="upcoming-class-item clickable" onclick="showClassReservations('<?php echo esc_js($class->class_id); ?>')">
                                <div class="class-date">
                                    <span class="day"><?php echo date('d', strtotime($class->date)); ?></span>
                                    <span class="month"><?php echo date('M', strtotime($class->date)); ?></span>
                                </div>
                                <div class="class-details">
                                    <h4><?php echo esc_html($class->name); ?></h4>
                                    <p><?php echo date('H:i', strtotime($class->time)); ?> - <?php echo esc_html($class->instructor); ?></p>
                                    <span class="occupancy"><?php echo $class->reserved_spots; ?>/<?php echo $class->max_spots; ?> personas</span>
                                    <?php if ($class->is_recurring): ?>
                                        <span class="recurring-badge">📅 Recurrente</span>
                                    <?php endif; ?>
                                </div>
                                <div class="class-status">
                                    <?php 
                                    $percentage = $class->max_spots > 0 ? ($class->reserved_spots / $class->max_spots) * 100 : 0;
                                    if ($percentage >= 100): ?>
                                        <span class="status-full">Completa</span>
                                    <?php elseif ($percentage >= 80): ?>
                                        <span class="status-almost-full">Casi Llena</span>
                                    <?php else: ?>
                                        <span class="status-available">Disponible</span>
                                    <?php endif; ?>
                                </div>
                                <div class="click-indicator">
                                    <span class="dashicons dashicons-visibility"></span>
                                    <small>Click para ver reservas</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">No hay clases programadas</p>
                        <button class="action-btn primary" onclick="switchTab('classes'); setTimeout(showAddClassForm, 100);">
                            Crear Primera Clase
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actividad reciente -->
            <div class="dashboard-section">
                <h2>Actividad Reciente (últimas 48h)</h2>
                <div class="recent-activity">
                    <?php if (!empty($recent_activity)): ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="activity-item <?php echo $activity->status; ?>">
                                <div class="activity-avatar">
                                    <?php if ($activity->status === 'cancelled'): ?>
                                        <span class="dashicons dashicons-dismiss"></span>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-admin-users"></span>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-details">
                                    <?php if ($activity->status === 'cancelled'): ?>
                                        <p><strong><?php echo esc_html($activity->user_name); ?></strong> <span class="action-text cancelled">canceló su reserva para</span></p>
                                    <?php else: ?>
                                        <p><strong><?php echo esc_html($activity->user_name); ?></strong> <span class="action-text confirmed">se reservó para</span></p>
                                    <?php endif; ?>
                                    <p><em><?php echo esc_html($activity->class_name); ?></em></p>
                                    <span class="activity-time">
                                        <?php 
                                        $created_utc = new DateTime($activity->created_at, new DateTimeZone('UTC'));
                                        $created_mexico = $created_utc->setTimezone(new DateTimeZone('America/Mexico_City'));
                                        echo $created_mexico->format('d/m/Y H:i');
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">No hay actividad reciente</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- POPUP PARA VER RESERVAS DE UNA CLASE -->
    <div id="classReservationsModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalClassName">Reservas de la Clase</h3>
                <button class="modal-close" onclick="closeClassReservationsModal()">
                    <span class="dashicons dashicons-no"></span>
                </button>
            </div>
            <div class="modal-body">
                <div id="reservationsList">
                    <!-- Aquí se cargarán las reservas -->
                </div>
            </div>
        </div>
    </div>

    <script>
    function showClassReservations(classId) {
        document.getElementById('classReservationsModal').style.display = 'flex';
        document.getElementById('reservationsList').innerHTML = '<div class="loading">Cargando reservas...</div>';
        
        fetch(yoga_admin_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'yoga_admin_action',
                action_type: 'get_class_reservations',
                class_id: classId,
                nonce: yoga_admin_ajax.nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayClassReservations(data.data);
            } else {
                document.getElementById('reservationsList').innerHTML = '<p>Error al cargar las reservas</p>';
            }
        })
        .catch(error => {
            document.getElementById('reservationsList').innerHTML = '<p>Error de conexión</p>';
        });
    }

    function displayClassReservations(data) {
        const { class_info, reservations } = data;
        
        document.getElementById('modalClassName').innerHTML = 
            `Reservas para ${class_info.name}<br><small>${class_info.date} ${class_info.time} - ${class_info.instructor}</small>`;
        
        let html = '';
        
        if (reservations.length > 0) {
            html += `<div class="reservations-summary">
                        <p><strong>${reservations.length}</strong> persona(s) reservada(s) de <strong>${class_info.max_spots}</strong> cupos disponibles</p>
                     </div>`;
            
            html += '<div class="reservations-list">';
            reservations.forEach((reservation, index) => {
                const reservedDate = new Date(reservation.created_at + ' UTC');
                const localDate = reservedDate.toLocaleString('es-MX', {
                    timeZone: 'America/Mexico_City',
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                html += `
                    <div class="reservation-card">
                        <div class="reservation-number">${index + 1}</div>
                        <div class="reservation-info">
                            <h4>${reservation.user_name}</h4>
                            <p><span class="dashicons dashicons-email"></span> ${reservation.user_email}</p>
                            ${reservation.user_phone ? `<p><span class="dashicons dashicons-phone"></span> ${reservation.user_phone}</p>` : ''}
                            <small class="reservation-date">Reservado: ${localDate}</small>
                        </div>
                        <div class="reservation-status">
                            <span class="status-badge ${reservation.status}">${reservation.status === 'confirmed' ? 'Confirmada' : reservation.status}</span>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        } else {
            html = '<div class="no-reservations"><span class="dashicons dashicons-info"></span><p>No hay reservas para esta clase aún</p></div>';
        }
        
        document.getElementById('reservationsList').innerHTML = html;
    }

    function closeClassReservationsModal() {
        document.getElementById('classReservationsModal').style.display = 'none';
    }

    document.getElementById('classReservationsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeClassReservationsModal();
        }
    });
    </script>
    
    <style>
    /* ===== ESTILOS BASE DASHBOARD ===== */
    .dashboard-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }

    .stat-icon {
    background: linear-gradient(135deg, #95a485 0%, #8b9a7b 100%);
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

    .stat-icon .dashicons {
        color: white;
        font-size: 24px;
    }

    .stat-info h3 {
        font-size: 28px;
        font-weight: bold;
        margin: 0;
        color: #2d3748;
    }

    .stat-info p {
        font-size: 14px;
        color: #718096;
        margin: 0;
    }

    /* TEXTOS RESPONSIVE */
    .stat-text-mobile,
    .btn-text-mobile {
        display: none;
    }

    .stat-text-desktop,
    .btn-text-desktop {
        display: inline-block;
    }

    .quick-actions {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
    }

    .quick-actions h2 {
        margin: 0 0 15px 0;
        color: #2d3748;
        font-size: 18px;
    }

    .action-buttons {
    display: grid;
    grid-template-columns: 1.2fr 1.3fr 1fr;
    gap: 15px;
}

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 16px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .action-btn.primary {
    background: linear-gradient(135deg, #95a485 0%, #8b9a7b 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(149, 164, 133, 0.3);
}

.action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(149, 164, 133, 0.4);
}
    .action-btn.secondary {
        background: white;
        color: #4a5568;
        border: 1px solid #e2e8f0;
    }

    .action-btn.secondary:hover {
    background: #f8f9fa;
    border-color: #95a485;
}
/* 🎯 TAMAÑOS ESPECÍFICOS PARA BOTONES */
.action-btn:nth-child(1) {
    /* Nueva Clase Individual - más grande */
    min-width: 220px;
    padding: 14px 18px;
}

.action-btn:nth-child(2) {
    /* Programar Clases Recurrentes - más grande */
    min-width: 250px;
    padding: 14px 18px;
}

.action-btn:nth-child(3) {
    /* Ver Todas las Clases - más pequeño */
    min-width: 160px;
    padding: 10px 14px;
    font-size: 13px;
}
/* 🎯 FORZAR COLORES CORRECTOS CON !IMPORTANT */
.action-btn.primary,
button.action-btn.primary {
    background: linear-gradient(135deg, #95a485 0%, #8b9a7b 100%) !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(149, 164, 133, 0.3) !important;
}

.action-btn.primary:hover,
button.action-btn.primary:hover {
    background: linear-gradient(135deg, #8b9a7b 0%, #7d8a6e 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(149, 164, 133, 0.4) !important;
}

/* 🎯 FORZAR COLORES EN BOTONES ESPECÍFICOS */
.action-buttons .action-btn:nth-child(1),
.action-buttons .action-btn:nth-child(2) {
    background: linear-gradient(135deg, #95a485 0%, #8b9a7b 100%) !important;
    color: white !important;
    border: none !important;
}

.action-buttons .action-btn:nth-child(1):hover,
.action-buttons .action-btn:nth-child(2):hover {
    background: linear-gradient(135deg, #8b9a7b 0%, #7d8a6e 100%) !important;
}
.action-btn:nth-child(4) {
    /* Ver Reservas - más pequeño */
    min-width: 130px;
    padding: 10px 14px;
    font-size: 13px;
}

    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .dashboard-section {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        height: fit-content;
    }

    .dashboard-section h2 {
        margin: 0 0 20px 0;
        color: #2d3748;
        font-size: 18px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 10px;
    }

    .upcoming-class-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 10px;
        background: white;
        position: relative;
    }

/* 🎯 CALENDARIO SIN FONDO - SOLO COLOR #95a485 */
.class-date {
    text-align: center;
    background: transparent !important;
    color: #95a485 !important;
    border-radius: 8px;
    padding: 10px;
    min-width: 60px;
    flex-shrink: 0;
    box-shadow: none;
    transition: all 0.3s ease;
    border: 2px solid #95a485;
}

.class-date:hover {
    transform: translateY(-1px);
    background: rgba(149, 164, 133, 0.05) !important;
    border-color: #7a8a6d;
}

.class-date .day {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: #95a485 !important;
    text-shadow: none;
    letter-spacing: normal;
}

.class-date .month {
    display: block;
    font-size: 12px;
    text-transform: uppercase;
    font-weight: 600;
    color: #95a485 !important;
    letter-spacing: 0.8px;
    opacity: 1;
    margin-top: 1px;
}

    .class-details {
        flex: 1;
    }

    .class-details h4 {
        margin: 0 0 5px 0;
        color: #2d3748;
        font-size: 16px;
    }

    .class-details p {
        margin: 0 0 5px 0;
        color: #718096;
        font-size: 14px;
    }

    .occupancy {
        background: #f0f8f0;
        color: #2d5a2d;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .recurring-badge {
        background: #e8f5e8;
        color: #2d5a2d;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        margin-left: 8px;
    }

    .class-status {
        text-align: right;
    }

    /* 🎯 BOTÓN "DISPONIBLE" COMPLETAMENTE REDISEÑADO */
    .status-available {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
        color: #166534 !important;
        padding: 10px 18px !important;
        border-radius: 25px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
        border: 2px solid #86efac !important;
        box-shadow: 0 4px 12px rgba(134, 239, 172, 0.4) !important;
        transition: all 0.3s ease !important;
        text-shadow: none !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .status-available:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .status-available:hover:before {
        left: 100%;
    }

    .status-available:hover {
        background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%) !important;
        transform: translateY(-3px) scale(1.05) !important;
        box-shadow: 0 8px 20px rgba(134, 239, 172, 0.6) !important;
        border-color: #4ade80 !important;
    }

    /* 🎯 MEJORAS PARA LOS OTROS ESTADOS TAMBIÉN */
    .status-almost-full {
        background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%) !important;
        color: #92400e !important;
        padding: 10px 18px !important;
        border-radius: 25px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
        border: 2px solid #fbbf24 !important;
        box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4) !important;
        transition: all 0.3s ease !important;
    }

    .status-almost-full:hover {
        transform: translateY(-2px) scale(1.05) !important;
        box-shadow: 0 6px 18px rgba(251, 191, 36, 0.6) !important;
    }

    .status-full {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
        color: #991b1b !important;
        padding: 10px 18px !important;
        border-radius: 25px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
        border: 2px solid #f87171 !important;
        box-shadow: 0 4px 12px rgba(248, 113, 113, 0.4) !important;
        transition: all 0.3s ease !important;
    }

    .status-full:hover {
        transform: translateY(-2px) scale(1.05) !important;
        box-shadow: 0 6px 18px rgba(248, 113, 113, 0.6) !important;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px;
        margin-bottom: 10px;
    }

    .activity-avatar {
        background: #f0f8f0;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .activity-details {
        flex: 1;
    }

    .activity-details p {
        margin: 0 0 3px 0;
        font-size: 14px;
    }

    .activity-time {
        font-size: 12px;
        color: #718096;
    }

    .no-data {
        text-align: center;
        color: #718096;
        font-style: italic;
        padding: 40px 20px;
    }

    .upcoming-class-item.clickable {
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .upcoming-class-item.clickable:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        background: #f8f9fa;
    }

    .upcoming-class-item.clickable:hover .class-date {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 25px rgba(34, 197, 94, 0.6);
    }

    .upcoming-class-item.clickable:hover .status-available {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 18px rgba(134, 239, 172, 0.6);
    }
    
    .click-indicator {
        position: absolute;
        top: 10px;
        right: 10px;
        text-align: center;
        opacity: 0.7;
    }
    
    .click-indicator small {
        display: block;
        font-size: 10px;
        color: #666;
    }

    /* COLORES MEJORADOS PARA ACTIVIDAD */
    .activity-item.cancelled {
        background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);
        border-left: 4px solid #f87171;
        border-radius: 8px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }
    
    .activity-item:not(.cancelled) {
        background: linear-gradient(135deg, #f0f8f0 0%, #f8fcf8 100%);
        border-left: 4px solid #8fbc8f;
        border-radius: 8px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .activity-avatar {
        margin-right: 15px;
        padding: 8px;
        border-radius: 50%;
        background: rgba(255,255,255,0.8);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .activity-item.cancelled .activity-avatar .dashicons {
        color: #ef4444 !important;
        font-size: 18px;
    }

    .activity-item:not(.cancelled) .activity-avatar .dashicons {
        color: #8fbc8f !important;
        font-size: 18px;
    }

    .action-text.cancelled {
        color: #ef4444;
        font-weight: 600;
    }

    .action-text.confirmed {
        color: #7ab87a;
        font-weight: 600;
    }

    /* Modal Styles - Paleta Verde Yoga */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #8fbc8f 0%, #a8d8a8 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        box-shadow: 0 2px 4px rgba(143, 188, 143, 0.2);
    }

    .modal-header h3 {
        margin: 0;
        color: white;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        color: white;
        transition: all 0.3s ease;
    }

    .modal-close:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.1);
    }

    .modal-body {
        padding: 20px;
    }
    
    .reservations-summary {
        background: linear-gradient(135deg, #f0f8f0 0%, #e8f5e8 100%);
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        border: 1px solid #c6e6c6;
        color: #2d5a2d;
    }

    .reservation-card {
        display: flex;
        align-items: center;
        padding: 15px;
        border: 1px solid #e0e8e0;
        border-radius: 8px;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #fafcfa 0%, #ffffff 100%);
        transition: all 0.3s ease;
        border-left: 4px solid #a8d8a8;
    }

    .reservation-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(143, 188, 143, 0.15);
        border-left-color: #8fbc8f;
    }

    .reservation-number {
        background: linear-gradient(135deg, #8fbc8f 0%, #7ab87a 100%);
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 15px;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(143, 188, 143, 0.3);
    }

    .reservation-info {
        flex: 1;
    }

    .reservation-info h4 {
        margin: 0 0 5px 0;
        color: #2d5a2d;
        font-size: 16px;
    }

    .reservation-info p {
        margin: 0 0 3px 0;
        font-size: 14px;
        color: #5a7a5a;
    }

    .reservation-info .dashicons {
        color: #8fbc8f;
        margin-right: 5px;
    }

    .reservation-date {
        color: #8a9a8a;
        font-size: 12px;
    }

    .reservation-status .status-badge {
        background: linear-gradient(135deg, #a8d8a8 0%, #90c890 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 4px rgba(143, 188, 143, 0.2);
    }

    .loading {
        text-align: center;
        padding: 40px;
        color: #5a7a5a;
    }

    .loading::before {
        content: '';
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #e0e8e0;
        border-top: 2px solid #8fbc8f;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 10px;
        vertical-align: middle;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .no-reservations {
        text-align: center;
        padding: 40px;
        color: #5a7a5a;
    }

    .no-reservations .dashicons {
        color: #a8d8a8;
        font-size: 48px;
        margin-bottom: 10px;
    }

    /* ===== ESTILOS RESPONSIVE MÓVIL ===== */
    @media (max-width: 768px) {
        
        .dashboard-content {
            padding: 15px;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* TEXTOS RESPONSIVE EN MÓVIL */
        .stat-text-mobile,
        .btn-text-mobile {
            display: inline-block !important;
        }

        .stat-text-desktop,
        .btn-text-desktop {
            display: none !important;
        }

        /* ESTADÍSTICAS EN MÓVIL */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
            padding: 0;
        }

        .stat-card {
            padding: 15px 8px;
            gap: 8px;
            border-radius: 8px;
            flex-direction: column;
            text-align: center;
            min-height: 120px;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            margin: 0 auto;
        }

        .stat-icon .dashicons {
            font-size: 20px;
        }

        .stat-info h3 {
            font-size: 24px;
            margin: 8px 0 5px 0;
        }

        .stat-info p {
            font-size: 12px;
            margin: 0;
            line-height: 1.2;
            word-wrap: break-word;
            text-align: center;
            font-weight: 500;
        }

        /* ACCIONES RÁPIDAS EN MÓVIL */
        .quick-actions {
            padding: 15px;
            margin-bottom: 20px;
        }

        .quick-actions h2 {
            font-size: 16px;
            margin-bottom: 12px;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .action-btn {
            padding: 14px 16px;
            font-size: 13px;
            width: 100%;
            box-sizing: border-box;
            min-height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .action-btn .dashicons {
            font-size: 16px;
        }
		        /* 🎯 BOTONES RESPONSIVE */
        .action-buttons {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
        }
        
       .action-btn:nth-child(1),
.action-btn:nth-child(2),
.action-btn:nth-child(3) {
    min-width: 100% !important;
    padding: 14px 16px !important;
    font-size: 13px !important;
}

        /* DASHBOARD GRID EN MÓVIL */
        .dashboard-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .dashboard-section {
            padding: 15px;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
        }

        .dashboard-section h2 {
            font-size: 16px;
            margin-bottom: 15px;
        }

        /* PRÓXIMAS CLASES EN MÓVIL */
        .upcoming-classes {
            width: 100%;
        }

        .upcoming-class-item {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 15px;
            margin-bottom: 12px;
            position: relative;
            text-align: center;
        }

        .class-date {
            align-self: center;
            min-width: 60px;
            padding: 10px;
        }

        .class-date .day {
            font-size: 18px;
        }

        .class-date .month {
            font-size: 10px;
        }

        .class-details {
            width: 100%;
        }

        .class-details h4 {
            font-size: 14px;
            margin-bottom: 8px;
        }

        .class-details p {
            font-size: 12px;
            margin-bottom: 8px;
        }

        .occupancy {
            font-size: 11px;
            padding: 3px 8px;
        }

        .recurring-badge {
            font-size: 11px;
            padding: 2px 6px;
            margin-left: 0;
            margin-top: 5px;
            display: inline-block;
        }

        .class-status {
            text-align: center;
            margin-top: 8px;
        }

        /* BOTONES EN MÓVIL MÁS PEQUEÑOS */
        .status-available,
        .status-almost-full,
        .status-full {
            padding: 8px 14px !important;
            font-size: 11px !important;
            letter-spacing: 0.8px !important;
        }

        .click-indicator {
            position: absolute;
            top: 8px;
            right: 8px;
            opacity: 0.7;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        
        .click-indicator .dashicons {
            font-size: 16px !important;
            color: #8fbc8f !important;
        }

        .click-indicator small {
            display: none;
        }

        /* ACTIVIDAD RECIENTE EN MÓVIL */
        .recent-activity {
            width: 100%;
        }

        .activity-item {
            flex-direction: column;
            text-align: center;
            gap: 10px;
            padding: 15px;
        }

        .activity-avatar {
            align-self: center;
            width: 35px;
            height: 35px;
        }

        .activity-details {
            width: 100%;
        }

        .activity-details p {
            font-size: 12px;
            margin-bottom: 5px;
        }

        .activity-time {
            font-size: 11px;
        }

        /* MODAL EN MÓVIL */
        .modal-overlay {
            padding: 15px;
        }
        
        .modal-content {
            width: 100%;
            max-width: 350px;
            margin: 0 auto;
            max-height: 85vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 15px;
        }

        .modal-header h3 {
            font-size: 14px;
            text-align: center;
            line-height: 1.3;
        }

        .modal-body {
            padding: 15px;
        }
        
        .reservation-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .reservation-number {
            margin: 0 auto 10px auto;
            position: static;
        }
        
        .reservation-info {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }

        .reservation-info h4 {
            font-size: 14px;
        }

        .reservation-info p {
            font-size: 12px;
        }
        
        .reservation-status {
            width: 100%;
            text-align: center;
        }

        .reservations-summary p {
            font-size: 13px;
        }

        .no-data {
            padding: 30px 15px;
            font-size: 14px;
        }
    }
    </style>
    <?php
}
// Gestión de clases CORREGIDA
function yoga_render_classes_management() {
    global $wpdb;
    
    // Asegurar que las tablas existan
    ensure_yoga_tables_exist();
    
    // Lista de clases
    $classes = $wpdb->get_results("
        SELECT c.*, 
               (SELECT COUNT(*) FROM {$wpdb->prefix}yoga_reservations r 
                WHERE r.class_id = c.class_id AND r.status = 'confirmed') as reserved_spots
        FROM {$wpdb->prefix}yoga_classes c 
        WHERE c.date >= CURDATE() 
        ORDER BY c.date ASC, c.time ASC
    ");
    ?>
    
    <div class="classes-management">
        <div class="classes-header">
            <h2>Gestionar Clases</h2>
            <div class="header-actions">
                <button class="action-btn primary" onclick="showAddClassForm()">
                    <span class="dashicons dashicons-plus-alt"></span>
                    Nueva Clase
                </button>
                <button class="action-btn secondary" onclick="showRecurringClassForm()">
                    <span class="dashicons dashicons-update"></span>
                    Clases Recurrentes
                </button>
                <button class="action-btn danger" onclick="deleteAllClasses()">
                    <span class="dashicons dashicons-trash"></span>
                    Eliminar Todas
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="classes-filters">
            <div class="filter-group">
                <button class="filter-btn active" data-filter="all">Todas (<?php echo count($classes); ?>)</button>
                <button class="filter-btn" data-filter="today">Hoy</button>
                <button class="filter-btn" data-filter="month">Este Mes</button>
                <button class="filter-btn" data-filter="week">Esta Semana</button>
                <button class="filter-btn" data-filter="recurring">Recurrentes</button>
            </div>
            <div class="search-group">
                <input type="text" id="class-search" placeholder="Buscar clases..." />
                <span class="dashicons dashicons-search"></span>
            </div>
        </div>

        <!-- Grid de clases -->
        <div class="classes-grid" id="classesGrid">
            <?php foreach ($classes as $class): ?>
                <?php
                $percentage_full = $class->max_spots > 0 ? ($class->reserved_spots / $class->max_spots) * 100 : 0;
                $date_obj = new DateTime($class->date);
                $time_obj = new DateTime($class->time);
                ?>
			
                <div class="class-card" 
     data-date="<?php echo esc_attr($class->date); ?>" 
     data-name="<?php echo esc_attr(strtolower($class->name)); ?>"
     data-recurring="<?php echo $class->is_recurring ? 'true' : 'false'; ?>"
     data-debug="clase-<?php echo $class->id; ?>">
                    
                    <div class="class-card-header">
                        <h3><?php echo esc_html($class->name); ?></h3>
                        <div class="class-actions">
                            <button class="action-icon edit-class" data-id="<?php echo $class->id; ?>" title="Editar">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button class="action-icon duplicate-class" data-id="<?php echo $class->id; ?>" title="Duplicar">
                                <span class="dashicons dashicons-admin-page"></span>
                            </button>
                            <button class="action-icon delete-class" data-id="<?php echo $class->id; ?>" title="Eliminar">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="class-info">
                        <div class="info-row">
                            <span class="dashicons dashicons-calendar"></span>
                            <?php echo $date_obj->format('d/m/Y'); ?>
							                            <?php if ($class->is_recurring): ?>
                                <span class="recurring-indicator">🔄</span>
                            <?php endif; ?>
                        </div>
                        <div class="info-row">
                            <span class="dashicons dashicons-clock"></span>
                            <?php echo $time_obj->format('H:i'); ?> 
                            <small>(<?php echo $class->duration; ?> min)</small>
                        </div>
                        <div class="info-row">
                            <span class="dashicons dashicons-admin-users"></span>
                            <?php echo esc_html($class->instructor); ?>
                        </div>
                    </div>
                    
                    <div class="class-occupancy">
                        <div class="occupancy-header">
                            <span>Ocupación</span>
                            <span><?php echo $class->reserved_spots; ?>/<?php echo $class->max_spots; ?></span>
                        </div>
                        <div class="occupancy-bar">
                            <div class="occupancy-fill" style="width: <?php echo $percentage_full; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="class-status">
                        <?php if ($percentage_full >= 100): ?>
                            <span class="status-badge full">Completa</span>
                        <?php elseif ($percentage_full >= 80): ?>
                            <span class="status-badge almost-full">Casi Llena</span>
                        <?php else: ?>
                            <span class="status-badge available">Disponible</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($classes)): ?>
            <div class="no-classes">
                <div class="no-data-illustration">
                    <span class="dashicons dashicons-calendar-alt"></span>
                </div>
                <h3>No hay clases programadas</h3>
                <p>Comienza creando tu primera clase de yoga</p>
                <div class="getting-started-actions">
                    <button class="action-btn primary" onclick="showAddClassForm()">
                        Crear Clase Individual
                    </button>
                    <button class="action-btn secondary" onclick="showRecurringClassForm()">
                        Programar Clases Recurrentes
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para agregar/editar clase individual -->
    <div id="classModal" class="yoga-modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3 id="modalTitle">Nueva Clase</h3>
                <button class="close-modal" onclick="closeClassModal()">
                    <span class="dashicons dashicons-no"></span>
                </button>
            </div>
            <div class="modal-body">
                <?php yoga_render_class_form_content(); ?>
            </div>
        </div>
    </div>

    <!-- Modal para clases recurrentes -->
    <div id="recurringModal" class="yoga-modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Programar Clases Recurrentes</h3>
                <button class="close-modal" onclick="closeRecurringModal()">
                    <span class="dashicons dashicons-no"></span>
                </button>
            </div>
            <div class="modal-body">
                <?php yoga_render_recurring_form_content(); ?>
            </div>
        </div>
    </div>

   <script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 INICIANDO SISTEMA');
    
    // FILTROS - VERSIÓN CORREGIDA PARA MÓVIL Y DESKTOP
    document.querySelectorAll('.filter-btn').forEach(btn => {
        console.log('🔘 Configurando botón:', btn.dataset.filter);
        
        // Touch events para móvil
        btn.addEventListener('touchstart', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('📱 Touch en:', this.dataset.filter);
            activateFilter(this);
        }, { passive: false });
        
        // Click events para desktop
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🖱️ Click en:', this.dataset.filter);
            activateFilter(this);
        });
    });
    
    function activateFilter(btn) {
        // Remover active de todos
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        // Activar este
        btn.classList.add('active');
        // Ejecutar filtro
        const filter = btn.dataset.filter;
        console.log('🎯 Filtrando por:', filter);
        filterClasses(filter);
    }
    
    // BÚSQUEDA MEJORADA Y FORZADA
    const searchInput = document.getElementById('class-search');
    if (searchInput) {
        console.log('🔍 Input de búsqueda encontrado:', searchInput);
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            console.log('🔍 BUSCANDO:', searchTerm);
            
            const cards = document.querySelectorAll('.class-card');
            console.log('📋 Tarjetas para buscar:', cards.length);
            
            let foundCount = 0;
            
            cards.forEach((card, index) => {
                const className = (card.dataset.name || '').toLowerCase();
                const cardText = card.textContent.toLowerCase();
                
                const matchesName = className.includes(searchTerm);
                const matchesContent = cardText.includes(searchTerm);
                const show = searchTerm === '' || matchesName || matchesContent;
                
                if (show) {
                    card.style.display = 'block';
                    card.style.opacity = '1';
                    foundCount++;
                } else {
                    card.style.display = 'none';
                    card.style.opacity = '0';
                }
            });
            
            console.log(`🔍 BÚSQUEDA COMPLETADA: ${foundCount} resultados para "${searchTerm}"`);
        });
        
        // También escuchar eventos de teclado
        searchInput.addEventListener('keyup', function() {
            this.dispatchEvent(new Event('input'));
        });
        
    } else {
        console.log('❌ Input de búsqueda NO encontrado - verificar ID');
    }
    
    setupClassActionListeners();
});

// ← FUNCIÓN filterClasses DEBE ESTAR AQUÍ ↓
function filterClasses(filter) {
    console.log('🎯 INICIANDO FILTRO:', filter);
    
    const cards = document.querySelectorAll('.class-card');
    console.log('📋 Total de tarjetas encontradas:', cards.length);
    
    if (cards.length === 0) {
        console.log('❌ NO HAY TARJETAS - Verificar HTML');
        return;
    }
    
    const today = new Date().toISOString().split('T')[0];
    console.log('📅 Fecha de hoy:', today);
    let visibleCount = 0;
    
    cards.forEach((card, index) => {
        const cardDate = card.dataset.date || '';
        const isRecurring = card.dataset.recurring === 'true';
        let show = true;
        
        switch(filter) {
            case 'today':
                show = cardDate === today;
                break;
            case 'week':
                const weekEnd = new Date();
                weekEnd.setDate(weekEnd.getDate() + 7);
                const weekEndStr = weekEnd.toISOString().split('T')[0];
                show = cardDate >= today && cardDate <= weekEndStr;
                break;
            case 'month':
                const monthEnd = new Date();
                monthEnd.setMonth(monthEnd.getMonth() + 1);
                const monthEndStr = monthEnd.toISOString().split('T')[0];
                show = cardDate >= today && cardDate <= monthEndStr;
                break;
            case 'recurring':
    // DEBUGGING ESPECÍFICO PARA RECURRENTES
    console.log(`📄 Tarjeta ${index} RECURRENTE:`, {
        date: card.dataset.date,
        name: card.dataset.name,
        recurring: card.dataset.recurring,
        debug: card.dataset.debug,
        innerHTML: card.innerHTML.substring(0, 100) + '...',
        isRecurring: isRecurring
    });
    
    show = isRecurring;
    console.log(`🔄 RECURRENTE - Tarjeta ${index}: ${isRecurring} (${card.dataset.recurring})`);
    break;
            default: // 'all'
                show = true;
        }
        
        if (show) {
            card.style.display = 'block';
            card.style.opacity = '1';
            visibleCount++;
        } else {
            card.style.display = 'none';
            card.style.opacity = '0';
        }
    });
    
    console.log(`✅ RESULTADO FINAL: ${visibleCount} tarjetas visibles de ${cards.length} totales`);
}

    function showAddClassForm() {
        document.getElementById('modalTitle').textContent = 'Nueva Clase';
        document.getElementById('classModal').style.display = 'flex';
        document.getElementById('classForm').reset();
        document.getElementById('classId').value = '';
    }

    function showRecurringClassForm() {
        document.getElementById('recurringModal').style.display = 'flex';
        document.getElementById('recurringForm').reset();
    }

    function closeClassModal() {
        document.getElementById('classModal').style.display = 'none';
    }

    function closeRecurringModal() {
        document.getElementById('recurringModal').style.display = 'none';
    }

    function deleteAllClasses() {
        if (confirm('¿Estás COMPLETAMENTE SEGURA de que quieres eliminar TODAS las clases? Esta acción no se puede deshacer.')) {
            if (confirm('Esto eliminará todas las clases y sus reservas. ¿Continuar?')) {
                fetch(yoga_admin_ajax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'yoga_admin_action',
                        action_type: 'delete_all_classes',
                        nonce: yoga_admin_ajax.nonce
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Todas las clases han sido eliminadas');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification('Error al eliminar las clases', 'error');
                    }
                });
            }
        }
    }
    
    function setupClassActionListeners() {
        // Eliminar clase individual
        document.querySelectorAll('.delete-class').forEach(btn => {
            btn.removeEventListener('click', handleDeleteClass);
            btn.addEventListener('click', handleDeleteClass);
        });

        // Duplicar clase
        document.querySelectorAll('.duplicate-class').forEach(btn => {
            btn.removeEventListener('click', handleDuplicateClass);
            btn.addEventListener('click', handleDuplicateClass);
        });

        // Editar clase
        document.querySelectorAll('.edit-class').forEach(btn => {
            btn.removeEventListener('click', handleEditClass);
            btn.addEventListener('click', handleEditClass);
        });
    }

    function handleDeleteClass(e) {
        if (confirm('¿Eliminar esta clase?')) {
            const classId = this.dataset.id;
            
            fetch(yoga_admin_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'yoga_admin_action',
                    action_type: 'delete_class',
                    class_id: classId,
                    nonce: yoga_admin_ajax.nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest('.class-card').remove();
                    showNotification('Clase eliminada correctamente');
                } else {
                    showNotification('Error al eliminar la clase', 'error');
                }
            });
        }
    }

    function handleDuplicateClass(e) {
        const classId = this.dataset.id;
        
        fetch(yoga_admin_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'yoga_admin_action',
                action_type: 'duplicate_class',
                class_id: classId,
                nonce: yoga_admin_ajax.nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Clase duplicada correctamente');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Error al duplicar la clase', 'error');
            }
        });
    }

    function handleEditClass(e) {
        const classId = this.dataset.id;
        
        fetch(yoga_admin_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'yoga_admin_action',
                action_type: 'get_class_data',
                class_id: classId,
                nonce: yoga_admin_ajax.nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditForm(data.data);
            } else {
                showNotification('Error al cargar datos de la clase', 'error');
            }
        });
    }

    function populateEditForm(classData) {
        document.getElementById('modalTitle').textContent = 'Editar Clase';
        document.getElementById('classId').value = classData.id;
        document.getElementById('className').value = classData.name;
        document.getElementById('instructor').value = classData.instructor;
        document.getElementById('duration').value = classData.duration;
        document.getElementById('maxSpots').value = classData.max_spots;
        document.getElementById('classDate').value = classData.date;
        document.getElementById('classTime').value = classData.time;
        
        document.getElementById('classModal').style.display = 'flex';
    }
    </script>
    <?php
}

// Formulario de clase individual CORREGIDO
function yoga_render_class_form_content() {
    ?>
    <form id="classForm" class="yoga-form">
        <input type="hidden" id="classId" name="class_id" />
        
        <div class="form-grid">
            <div class="form-group">
                <label for="className">Tipo de Clase *</label>
                <select id="className" name="class_name" required>
                    <option value="">Selecciona un tipo</option>
                    <option value="Hatha Yoga">Hatha Yoga</option>
                    <option value="Vinyasa Flow">Vinyasa Flow</option>
                    <option value="Yoga Restaurativo">Yoga Restaurativo</option>
                    <option value="Meditación & Pranayama">Meditación & Pranayama</option>
                    <option value="Yoga Prenatal">Yoga Prenatal</option>
                    <option value="Power Yoga">Power Yoga</option>
                    <option value="Yin Yoga">Yin Yoga</option>
                    <option value="Yoga Nidra">Yoga Nidra</option>
                    <option value="Clase Personalizada">Clase Personalizada</option>
                </select>
            </div>
            
            <div class="form-group" id="customNameGroup" style="display:none;">
                <label for="customName">Nombre Personalizado</label>
                <input type="text" id="customName" name="custom_name" />
            </div>
            
            <div class="form-group">
                <label for="instructor">Instructor</label>
                <input type="text" id="instructor" name="instructor" value="Ana Sordo" />
            </div>
            
            <div class="form-group">
                <label for="duration">Duración (minutos) *</label>
                <select id="duration" name="duration" required>
                    <option value="45">45 minutos</option>
                    <option value="60" selected>60 minutos</option>
                    <option value="75">75 minutos</option>
                    <option value="90">90 minutos</option>
                    <option value="120">120 minutos</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="maxSpots">Cupos Máximos *</label>
                <input type="number" id="maxSpots" name="max_spots" value="15" min="1" max="50" required />
            </div>
            
            <div class="form-group">
                <label for="classDate">Fecha *</label>
                <input type="date" id="classDate" name="class_date" required min="<?php echo date('Y-m-d'); ?>" />
            </div>
            
            <div class="form-group">
                <label for="classTime">Hora *</label>
                <input type="time" id="classTime" name="class_time" required />
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" class="action-btn secondary" onclick="closeClassModal()">
                Cancelar
            </button>
            <button type="submit" class="action-btn primary">
                Guardar Clase
            </button>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar campo personalizado
        const classNameSelect = document.getElementById('className');
        if (classNameSelect) {
            classNameSelect.addEventListener('change', function() {
                const customGroup = document.getElementById('customNameGroup');
                if (this.value === 'Clase Personalizada') {
                    customGroup.style.display = 'block';
                    document.getElementById('customName').required = true;
                } else {
                    customGroup.style.display = 'none';
                    document.getElementById('customName').required = false;
                }
            });
        }

        // Manejar envío del formulario
        const classForm = document.getElementById('classForm');
        if (classForm && !classForm.hasEventListener) {
            classForm.hasEventListener = true;
            classForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveClass();
            });
        }
    });

    function saveClass() {
        const formData = new FormData(document.getElementById('classForm'));
        formData.append('action', 'yoga_admin_action');
        formData.append('action_type', 'save_class');
        formData.append('nonce', yoga_admin_ajax.nonce);
        
        const submitBtn = document.querySelector('#classForm button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Guardando...';
        submitBtn.disabled = true;
        
        fetch(yoga_admin_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeClassModal();
                showNotification('Clase guardada exitosamente');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error: ' + data.data, 'error');
            }
        })
        .catch(error => {
            showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }
    </script>
    <?php
}

// Formulario de clases recurrentes CORREGIDO
function yoga_render_recurring_form_content() {
    ?>
    <form id="recurringForm" class="yoga-form">
        <div class="recurring-intro">
            <h4>📅 Programación de Clases Recurrentes</h4>
            <p>Crea múltiples clases automáticamente según un horario repetitivo.</p>
        </div>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="recurringClassName">Tipo de Clase *</label>
                <select id="recurringClassName" name="class_name" required>
                    <option value="">Selecciona un tipo</option>
                    <option value="Hatha Yoga">Hatha Yoga</option>
                    <option value="Vinyasa Flow">Vinyasa Flow</option>
                    <option value="Yoga Restaurativo">Yoga Restaurativo</option>
                    <option value="Meditación & Pranayama">Meditación & Pranayama</option>
                    <option value="Yoga Prenatal">Yoga Prenatal</option>
                    <option value="Power Yoga">Power Yoga</option>
                    <option value="Yin Yoga">Yin Yoga</option>
                    <option value="Yoga Nidra">Yoga Nidra</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="recurringInstructor">Instructor</label>
                <input type="text" id="recurringInstructor" name="instructor" value="Ana Sordo" />
            </div>
            
            <div class="form-group">
                <label for="recurringDuration">Duración (minutos) *</label>
                <select id="recurringDuration" name="duration" required>
                    <option value="45">45 minutos</option>
                    <option value="60" selected>60 minutos</option>
                    <option value="75">75 minutos</option>
                    <option value="90">90 minutos</option>
                    <option value="120">120 minutos</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="recurringMaxSpots">Cupos Máximos *</label>
                <input type="number" id="recurringMaxSpots" name="max_spots" value="15" min="1" max="50" required />
            </div>
            
            <div class="form-group">
                <label for="recurringTime">Hora *</label>
                <input type="time" id="recurringTime" name="class_time" required />
            </div>
            
            <div class="form-group">
                <label for="frequency">Frecuencia *</label>
                <select id="frequency" name="frequency" required>
                    <option value="weekly">Semanal</option>
                    <option value="biweekly">Cada 2 semanas</option>
                    <option value="monthly">Mensual</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Días de la Semana *</label>
            <div class="days-selector">
                <label><input type="checkbox" name="days[]" value="1" /> Lunes</label>
                <label><input type="checkbox" name="days[]" value="2" /> Martes</label>
                <label><input type="checkbox" name="days[]" value="3" /> Miércoles</label>
                <label><input type="checkbox" name="days[]" value="4" /> Jueves</label>
                <label><input type="checkbox" name="days[]" value="5" /> Viernes</label>
                <label><input type="checkbox" name="days[]" value="6" /> Sábado</label>
                <label><input type="checkbox" name="days[]" value="0" /> Domingo</label>
            </div>
        </div>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="startDate">Fecha de Inicio *</label>
                <input type="date" id="startDate" name="start_date" required min="<?php echo date('Y-m-d'); ?>" />
            </div>
            
            <div class="form-group">
                <label for="endDate">Fecha Final *</label>
                <input type="date" id="endDate" name="end_date" required min="<?php echo date('Y-m-d'); ?>" />
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" class="action-btn secondary" onclick="closeRecurringModal()">
                Cancelar
            </button>
            <button type="submit" class="action-btn primary">
                Crear Clases Recurrentes
            </button>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar envío del formulario recurrente
        const recurringForm = document.getElementById('recurringForm');
        if (recurringForm && !recurringForm.hasEventListener) {
            recurringForm.hasEventListener = true;
            recurringForm.addEventListener('submit', function(e) {
                e.preventDefault();
                saveRecurringClasses();
            });
        }
    });

    function saveRecurringClasses() {
        const selectedDays = Array.from(document.querySelectorAll('input[name="days[]"]:checked')).map(cb => cb.value);
        
        if (selectedDays.length === 0) {
            showNotification('Debes seleccionar al menos un día de la semana', 'error');
            return;
        }
        
        const formData = new FormData(document.getElementById('recurringForm'));
        formData.append('action', 'yoga_admin_action');
        formData.append('action_type', 'save_recurring_classes');
        formData.append('nonce', yoga_admin_ajax.nonce);
        
        // Agregar días seleccionados explícitamente
        formData.delete('days[]');
        selectedDays.forEach(day => {
            formData.append('days[]', day);
        });
        
        const submitBtn = document.querySelector('#recurringForm button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Creando clases...';
        submitBtn.disabled = true;
        
        fetch(yoga_admin_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeRecurringModal();
                showNotification('Clases recurrentes creadas exitosamente: ' + data.data.count + ' clases');
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('Error: ' + data.data, 'error');
            }
        })
        .catch(error => {
            showNotification('Error de conexión', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }
    </script>
    <?php
}

// Gestión de reservas
function yoga_render_reservations_management() {
    global $wpdb;
    
    ensure_yoga_tables_exist();
    
    $reservations = $wpdb->get_results("
        SELECT r.*, c.name as class_name, c.date, c.time, c.duration, c.instructor
        FROM {$wpdb->prefix}yoga_reservations r
        LEFT JOIN {$wpdb->prefix}yoga_classes c ON r.class_id = c.class_id
        ORDER BY r.created_at DESC
        LIMIT 50
    ");
    
    $stats = $wpdb->get_row("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM {$wpdb->prefix}yoga_reservations
    ");
    ?>
    
    <div class="reservations-management">
        <div class="reservations-header">
            <h2>Gestionar Reservas</h2>
            <div class="reservations-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $stats->total ?? 0; ?></span>
                    <span class="stat-label">Total</span>
                </div>
                <div class="stat-item confirmed">
                    <span class="stat-number"><?php echo $stats->confirmed ?? 0; ?></span>
                    <span class="stat-label">Confirmadas</span>
                </div>
                <div class="stat-item cancelled">
                    <span class="stat-number"><?php echo $stats->cancelled ?? 0; ?></span>
                    <span class="stat-label">Canceladas</span>
                </div>
                <div class="stat-item pending">
                    <span class="stat-number"><?php echo $stats->pending ?? 0; ?></span>
                    <span class="stat-label">Pendientes</span>
                </div>
            </div>
        </div>

        <div class="reservations-table-container">
            <table class="reservations-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Clase</th>
                        <th>Fecha & Hora</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th>Reservado el</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td>
                                <div class="client-info">
                                    <strong><?php echo esc_html($reservation->user_name); ?></strong>
                                </div>
                            </td>
                            
                            <td>
                                <div class="class-info">
                                    <strong><?php echo esc_html($reservation->class_name ?: 'Clase eliminada'); ?></strong><br>
                                    <small>con <?php echo esc_html($reservation->instructor ?: 'N/A'); ?></small>
                                </div>
                            </td>
                            
                            <td>
                                <div class="datetime-info">
                                    <?php if ($reservation->date): ?>
                                        <strong><?php echo date('d/m/Y', strtotime($reservation->date)); ?></strong><br>
                                        <small><?php echo date('H:i', strtotime($reservation->time)); ?></small>
                                    <?php else: ?>
                                        <em>N/A</em>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td>
                                <div class="contact-info">
                                    <a href="mailto:<?php echo esc_attr($reservation->user_email); ?>">
                                        <?php echo esc_html($reservation->user_email); ?>
                                    </a><br>
                                    <small><?php echo esc_html($reservation->user_phone ?: 'No proporcionado'); ?></small>
                                </div>
                            </td>
                            
                            <td>
                                <span class="status-badge <?php echo $reservation->status; ?>">
                                    <?php 
                                    $status_labels = [
                                        'confirmed' => 'Confirmada',
                                        'cancelled' => 'Cancelada',
                                        'pending' => 'Pendiente'
                                    ];
                                    echo $status_labels[$reservation->status] ?? ucfirst($reservation->status);
                                    ?>
                                </span>
                            </td>
                            
                            <td>
                                <small><?php 
                                    $created_utc = new DateTime($reservation->created_at, new DateTimeZone('UTC'));
                                    $created_mexico = $created_utc->setTimezone(new DateTimeZone('America/Mexico_City'));
                                    echo $created_mexico->format('d/m/Y H:i');
                                ?></small>
                            </td>
                            
                            <td>
                                <div class="reservation-actions">
                                    <select class="status-changer" data-id="<?php echo $reservation->id; ?>">
                                        <option value="confirmed" <?php selected($reservation->status, 'confirmed'); ?>>Confirmada</option>
                                        <option value="cancelled" <?php selected($reservation->status, 'cancelled'); ?>>Cancelada</option>
                                        <option value="pending" <?php selected($reservation->status, 'pending'); ?>>Pendiente</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($reservations)): ?>
                <div class="no-reservations">
                    <div class="no-data-illustration">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <h3>No hay reservas aún</h3>
                    <p>Las reservas aparecerán aquí cuando los clientes reserven clases</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.status-changer').forEach(select => {
            select.addEventListener('change', function() {
                const reservationId = this.dataset.id;
                const newStatus = this.value;
                
                fetch(yoga_admin_ajax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'yoga_admin_action',
                        action_type: 'change_reservation_status',
                        reservation_id: reservationId,
                        new_status: newStatus,
                        nonce: yoga_admin_ajax.nonce
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = this.closest('tr');
                        const badge = row.querySelector('.status-badge');
                        badge.className = 'status-badge ' + newStatus;
                        badge.textContent = newStatus === 'confirmed' ? 'Confirmada' : 
                                           newStatus === 'cancelled' ? 'Cancelada' : 'Pendiente';
                        showNotification('Estado actualizado correctamente');
                    } else {
                        showNotification('Error al cambiar el estado', 'error');
                        this.value = this.dataset.originalValue;
                    }
                });
            });
            
            select.dataset.originalValue = select.value;
        });
    });
    </script>
    <?php
}

// Configuración
function yoga_render_settings() {
    $settings = [
        'default_instructor' => get_option('yoga_default_instructor', 'Ana Sordo'),
        'default_max_spots' => get_option('yoga_default_max_spots', 15),
        'email_from' => get_option('yoga_email_from', get_option('admin_email')),
        'email_from_name' => get_option('yoga_email_from_name', 'Kurunta Yoga'),
        'admin_notifications' => get_option('yoga_admin_notifications', 1),
        'studio_address' => get_option('yoga_studio_address', ''),
        'studio_phone' => get_option('yoga_studio_phone', ''),
        'studio_instagram' => get_option('yoga_studio_instagram', ''),
    ];
    ?>
    
    <div class="settings-management">
        <div class="settings-header">
            <h2>Configuración del Sistema</h2>
            <p>Personaliza la configuración de tu estudio de yoga</p>
        </div>

        <form id="settingsForm" class="settings-form">
            <div class="settings-section">
                <h3>
                    <span class="dashicons dashicons-admin-settings"></span>
                    Configuración General
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="defaultInstructor">Instructor por Defecto</label>
                        <input type="text" id="defaultInstructor" name="default_instructor" 
                               value="<?php echo esc_attr($settings['default_instructor']); ?>" />
                    </div>
                    
                    <div class="form-group">
                        <label for="defaultMaxSpots">Cupos Máximos por Defecto</label>
                        <input type="number" id="defaultMaxSpots" name="default_max_spots" 
                               value="<?php echo esc_attr($settings['default_max_spots']); ?>" min="1" max="50" />
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h3>
                    <span class="dashicons dashicons-location"></span>
                    Información del Estudio
                </h3>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="studioAddress">Dirección del Estudio</label>
                        <textarea id="studioAddress" name="studio_address" rows="3"><?php echo esc_textarea($settings['studio_address']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="studioPhone">Teléfono de Contacto</label>
                        <input type="tel" id="studioPhone" name="studio_phone" 
                               value="<?php echo esc_attr($settings['studio_phone']); ?>" />
                    </div>
                    
                    <div class="form-group">
                        <label for="studioInstagram">Instagram (@usuario)</label>
                        <input type="text" id="studioInstagram" name="studio_instagram" 
                               value="<?php echo esc_attr($settings['studio_instagram']); ?>" 
                               placeholder="@kurunta_yoga" />
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h3>
                    <span class="dashicons dashicons-email"></span>
                    Configuración de Emails
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="emailFromName">Nombre del Remitente</label>
                        <input type="text" id="emailFromName" name="email_from_name" 
                               value="<?php echo esc_attr($settings['email_from_name']); ?>" 
                               placeholder="Kurunta Yoga" />
                        <small>Nombre que aparecerá en los emails enviados</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="emailFrom">Email de Envío</label>
                        <input type="email" id="emailFrom" name="email_from" 
                               value="<?php echo esc_attr($settings['email_from']); ?>" />
                        <small>Email desde el cual se enviarán las confirmaciones</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label>
                            <input type="checkbox" name="admin_notifications" value="1" 
                                   <?php checked($settings['admin_notifications'], 1); ?> />
                            Recibir notificaciones de nuevas reservas por email
                        </label>
                    </div>
                </div>
            </div>

            <div class="settings-actions">
                <button type="submit" class="action-btn primary large">
                    <span class="dashicons dashicons-yes"></span>
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'yoga_admin_action');
            formData.append('action_type', 'save_settings');
            formData.append('nonce', yoga_admin_ajax.nonce);
            
            const saveBtn = this.querySelector('button[type="submit"]');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Guardando...';
            saveBtn.disabled = true;
            
            fetch(yoga_admin_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Configuración guardada exitosamente', 'success');
                } else {
                    showNotification('Error al guardar la configuración', 'error');
                }
            })
            .catch(error => {
                showNotification('Error de conexión', 'error');
            })
            .finally(() => {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
        });
    });
    </script>
    <?php
}

// AJAX Handlers CORREGIDOS
function handle_save_class() {
    global $wpdb;
    
    // Sanitizar datos
    $class_name = sanitize_text_field($_POST['class_name']);
    if ($class_name === 'Clase Personalizada') {
        $class_name = sanitize_text_field($_POST['custom_name']);
    }
    
    $class_id_input = sanitize_text_field($_POST['class_id']);
    $is_editing = !empty($class_id_input);
    $instructor = sanitize_text_field($_POST['instructor']);
    $duration = intval($_POST['duration']);
    $max_spots = intval($_POST['max_spots']);
    $class_date = sanitize_text_field($_POST['class_date']);
    $class_time = sanitize_text_field($_POST['class_time']);
    
    // Validar datos obligatorios
    if (empty($class_name) || empty($instructor) || empty($class_date) || empty($class_time)) {
        wp_send_json_error('Todos los campos obligatorios deben completarse');
        return;
    }
    
    // Detectar período automáticamente
    $hour = intval(explode(':', $class_time)[0]);
    if ($hour >= 6 && $hour < 12) {
        $period = 'morning';
    } elseif ($hour >= 12 && $hour < 18) {
        $period = 'afternoon';
    } else {
        $period = 'evening';
    }
    
    if ($is_editing) {
        // ACTUALIZAR CLASE EXISTENTE
        $data = [
            'name' => $class_name,
            'instructor' => $instructor,
            'duration' => $duration,
            'max_spots' => $max_spots,
            'date' => $class_date,
            'time' => $class_time,
            'period' => $period
        ];
        
        $result = $wpdb->update(
            $wpdb->prefix . 'yoga_classes',
            $data,
            ['id' => intval($class_id_input)],
            ['%s', '%s', '%d', '%d', '%s', '%s', '%s'],
            ['%d']
        );
        
        if ($result !== false) {
            wp_send_json_success(['message' => 'Clase actualizada exitosamente']);
        } else {
            wp_send_json_error('Error al actualizar la clase: ' . $wpdb->last_error);
        }
    } else {
        // CREAR NUEVA CLASE
        $unique_class_id = $class_name . '_' . $class_date . '_' . str_replace(':', '', $class_time) . '_' . time();
        
        $data = [
            'class_id' => $unique_class_id,
            'name' => $class_name,
            'instructor' => $instructor,
            'duration' => $duration,
            'max_spots' => $max_spots,
            'available_spots' => $max_spots,
            'date' => $class_date,
            'time' => $class_time,
            'period' => $period,
            'is_recurring' => 0,
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'yoga_classes',
            $data,
            ['%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s']
        );
        
        if ($result !== false) {
            wp_send_json_success(['message' => 'Clase creada exitosamente']);
        } else {
            wp_send_json_error('Error al crear la clase: ' . $wpdb->last_error);
        }
    }
}

function handle_save_recurring_classes() {
    global $wpdb;
    
    // Sanitizar todos los datos
    $class_name = sanitize_text_field($_POST['class_name']);
    $instructor = sanitize_text_field($_POST['instructor']);
    $duration = intval($_POST['duration']);
    $max_spots = intval($_POST['max_spots']);
    $class_time = sanitize_text_field($_POST['class_time']);
    $frequency = sanitize_text_field($_POST['frequency']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);
    
    // Obtener días seleccionados
    $selected_days = [];
    if (isset($_POST['days']) && is_array($_POST['days'])) {
        $selected_days = array_map('intval', $_POST['days']);
    }
    
    // Validar datos obligatorios
    if (empty($class_name) || empty($instructor) || empty($class_time) || empty($start_date) || empty($end_date) || empty($selected_days)) {
        wp_send_json_error('Todos los campos son obligatorios y debes seleccionar al menos un día');
        return;
    }
    
    // Detectar período automáticamente
    $hour = intval(explode(':', $class_time)[0]);
    if ($hour >= 6 && $hour < 12) {
        $period = 'morning';
    } elseif ($hour >= 12 && $hour < 18) {
        $period = 'afternoon';
    } else {
        $period = 'evening';
    }
    
    $created_count = 0;
    $current_date = new DateTime($start_date);
    $end_date_obj = new DateTime($end_date);
    $max_iterations = 0;
    
    // Crear clases día por día
    while ($current_date <= $end_date_obj && $max_iterations < 365) {
        $max_iterations++;
        $day_of_week = intval($current_date->format('w')); // 0=Domingo, 1=Lunes, etc.
        
        if (in_array($day_of_week, $selected_days)) {
            $unique_class_id = 'recurring_' . $class_name . '_' . $current_date->format('Ymd') . '_' . str_replace(':', '', $class_time) . '_' . $created_count;
            
            $class_data = [
                'class_id' => $unique_class_id,
                'name' => $class_name,
                'instructor' => $instructor,
                'duration' => $duration,
                'max_spots' => $max_spots,
                'available_spots' => $max_spots,
                'date' => $current_date->format('Y-m-d'),
                'time' => $class_time,
                'period' => $period,
                'is_recurring' => 1,
                'recurring_frequency' => $frequency,
                'recurring_end_date' => $end_date,
                'created_at' => current_time('mysql')
            ];
            
            $result = $wpdb->insert(
                $wpdb->prefix . 'yoga_classes',
                $class_data,
                ['%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
            );
            
            if ($result !== false) {
                $created_count++;
            } else {
                error_log('Error creando clase recurrente: ' . $wpdb->last_error);
            }
        }
        
        // Avanzar al siguiente día
        $current_date->add(new DateInterval('P1D'));
    }
    
    if ($created_count > 0) {
        wp_send_json_success([
            'message' => "Se crearon $created_count clases recurrentes exitosamente", 
            'count' => $created_count
        ]);
    } else {
        wp_send_json_error('No se pudo crear ninguna clase recurrente. Verifica los datos.');
    }
}

function handle_get_class_data() {
    global $wpdb;
    
    $class_id = intval($_POST['class_id']);
    
    $class = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}yoga_classes WHERE id = %d",
        $class_id
    ));
    
    if ($class) {
        wp_send_json_success($class);
    } else {
        wp_send_json_error('Clase no encontrada');
    }
}

function handle_delete_class() {
    global $wpdb;
    
    $class_id = intval($_POST['class_id']);
    
    // Obtener el class_id único para eliminar reservas
    $class_info = $wpdb->get_row($wpdb->prepare(
        "SELECT class_id FROM {$wpdb->prefix}yoga_classes WHERE id = %d",
        $class_id
    ));
    
    if ($class_info) {
        // Eliminar reservas asociadas por class_id único
        $wpdb->delete(
            $wpdb->prefix . 'yoga_reservations',
            ['class_id' => $class_info->class_id],
            ['%s']
        );
    }
    
    // Eliminar la clase por ID numérico
    $result = $wpdb->delete(
        $wpdb->prefix . 'yoga_classes',
        ['id' => $class_id],
        ['%d']
    );
    
    if ($result !== false) {
        wp_send_json_success('Clase eliminada correctamente');
    } else {
        wp_send_json_error('Error al eliminar la clase: ' . $wpdb->last_error);
    }
}

function handle_delete_all_classes() {
    global $wpdb;
    
    // Eliminar todas las reservas
    $wpdb->query("DELETE FROM {$wpdb->prefix}yoga_reservations");
    
    // Eliminar todas las clases
    $wpdb->query("DELETE FROM {$wpdb->prefix}yoga_classes");
    
    wp_send_json_success('Todas las clases han sido eliminadas');
}

function handle_duplicate_class() {
    global $wpdb;
    
    $class_id = intval($_POST['class_id']);
    
    // Obtener la clase original
    $original_class = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}yoga_classes WHERE id = %d",
        $class_id
    ), ARRAY_A);
    
    if (!$original_class) {
        wp_send_json_error('Clase no encontrada');
        return;
    }
    
    // Crear nueva fecha (día siguiente)
    $new_date = date('Y-m-d', strtotime($original_class['date'] . ' +1 day'));
    $unique_class_id = 'duplicate_' . $original_class['name'] . '_' . str_replace('-', '', $new_date) . '_' . str_replace(':', '', $original_class['time']) . '_' . time();
    
    // Preparar datos para nueva clase
    $new_data = [
        'class_id' => $unique_class_id,
        'name' => $original_class['name'],
        'instructor' => $original_class['instructor'],
        'duration' => $original_class['duration'],
        'max_spots' => $original_class['max_spots'],
        'available_spots' => $original_class['max_spots'], // Resetear cupos
        'date' => $new_date,
        'time' => $original_class['time'],
        'period' => $original_class['period'],
        'is_recurring' => $original_class['is_recurring'],
        'recurring_frequency' => $original_class['recurring_frequency'],
        'recurring_end_date' => $original_class['recurring_end_date'],
        'created_at' => current_time('mysql')
    ];
    
    $result = $wpdb->insert(
        $wpdb->prefix . 'yoga_classes',
        $new_data,
        ['%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
    );
    
    if ($result !== false) {
        wp_send_json_success('Clase duplicada correctamente para el ' . date('d/m/Y', strtotime($new_date)));
    } else {
        wp_send_json_error('Error al duplicar la clase: ' . $wpdb->last_error);
    }
}

function handle_change_reservation_status() {
    global $wpdb;
    
    $reservation_id = intval($_POST['reservation_id']);
    $new_status = sanitize_text_field($_POST['new_status']);
    
    $result = $wpdb->update(
        $wpdb->prefix . 'yoga_reservations',
        ['status' => $new_status],
        ['id' => $reservation_id],
        ['%s'],
        ['%d']
    );
    
    if ($result !== false) {
        wp_send_json_success('Estado actualizado');
    } else {
        wp_send_json_error('Error al actualizar');
    }
}

function handle_save_settings() {
    $settings = [
        'yoga_default_instructor' => sanitize_text_field($_POST['default_instructor']),
        'yoga_default_max_spots' => intval($_POST['default_max_spots']),
        'yoga_email_from' => sanitize_email($_POST['email_from']),
        'yoga_email_from_name' => sanitize_text_field($_POST['email_from_name']),
        'yoga_admin_notifications' => isset($_POST['admin_notifications']) ? 1 : 0,
        'yoga_studio_address' => sanitize_textarea_field($_POST['studio_address']),
        'yoga_studio_phone' => sanitize_text_field($_POST['studio_phone']),
        'yoga_studio_instagram' => sanitize_text_field($_POST['studio_instagram']),
    ];
    
    foreach ($settings as $key => $value) {
        update_option($key, $value);
    }
    
    wp_send_json_success('Configuración guardada');
}

// Configurar email para usar settings del admin
add_action('init', 'configure_yoga_email_settings');

function configure_yoga_email_settings() {
    add_filter('wp_mail', 'customize_yoga_reservation_emails');
}

function customize_yoga_reservation_emails($args) {
    if (strpos($args['subject'], 'Kurunta Yoga') !== false) {
        $from_email = get_option('yoga_email_from', get_option('admin_email'));
        $from_name = get_option('yoga_email_from_name', 'Kurunta Yoga');
        
        $args['headers'] = array(
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $from_email,
            'Content-Type: text/html; charset=UTF-8'
        );
    }
    
    return $args;
}

// Agregar estilos CSS adicionales
add_action('wp_head', 'yoga_admin_additional_styles');
function yoga_admin_additional_styles() {
    if (is_page('yoga-admin')) {
        ?>
        <style>
        /* Variables CSS */
        :root {
            --admin-primary: #8fbc8f;
            --admin-secondary: #7ab87a;
            --admin-bg: #f8f9fa;
            --admin-border: #e2e8f0;
            --admin-text: #2d3748;
            --admin-text-light: #718096;
            --admin-transition: all 0.3s ease;
            --admin-radius: 12px;
            --admin-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        /* Contenedor principal */
        .yoga-admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--admin-bg);
            min-height: 100vh;
        }

        /* Navegación de tabs */
        .yoga-admin-nav {
            background: white;
            border-bottom: 2px solid var(--admin-border);
            display: flex;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .nav-tab {
            padding: 18px 25px;
            border: none;
            background: transparent;
            color: var(--admin-text-light);
            font-weight: 500;
            font-size: 15px;
            cursor: pointer;
            transition: var(--admin-transition);
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            border-bottom: 3px solid transparent;
        }

        .nav-tab:hover {
            background: var(--admin-bg);
            color: var(--admin-text);
        }

        .nav-tab.active {
            color: var(--admin-primary);
            border-bottom-color: var(--admin-primary);
            background: rgba(143, 188, 143, 0.05);
        }

        .nav-tab .dashicons {
            font-size: 18px;
        }

        /* Contenido de tabs */
        .yoga-admin-content {
            padding: 30px;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Botones de acción */
        .action-btn {
            padding: 12px 20px;
            border: 2px solid transparent;
            border-radius: var(--admin-radius);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--admin-transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            background: white;
            color: var(--admin-text);
        }

        .action-btn.primary {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            color: white;
            border-color: var(--admin-secondary);
        }

        .action-btn.primary:hover {
            background: linear-gradient(135deg, var(--admin-secondary) 0%, #6ba56b 100%);
            transform: translateY(-2px);
            box-shadow: var(--admin-shadow);
        }

        .action-btn.secondary {
            border-color: var(--admin-border);
            background: white;
        }

        .action-btn.secondary:hover {
            border-color: var(--admin-primary);
            background: rgba(143, 188, 143, 0.05);
        }

        .action-btn.danger {
            border-color: #ef4444;
            color: #ef4444;
        }

        .action-btn.danger:hover {
            background: #ef4444;
            color: white;
        }

        /* Estadísticas del dashboard */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: var(--admin-radius);
            box-shadow: var(--admin-shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: var(--admin-transition);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .stat-icon .dashicons {
            font-size: 24px;
        }

        .stat-info h3 {
            margin: 0 0 5px 0;
            font-size: 32px;
            font-weight: 700;
            color: var(--admin-text);
        }

        .stat-info p {
            margin: 0;
            color: var(--admin-text-light);
            font-size: 14px;
        }

        /* Grid responsivo */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .dashboard-section {
            background: white;
            border-radius: var(--admin-radius);
            padding: 25px;
            box-shadow: var(--admin-shadow);
        }

        .dashboard-section h2 {
            margin: 0 0 20px 0;
            color: var(--admin-text);
            font-size: 20px;
            font-weight: 600;
            border-bottom: 2px solid var(--admin-bg);
            padding-bottom: 10px;
        }

        /* Acciones rápidas */
        .quick-actions {
            background: white;
            border-radius: var(--admin-radius);
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--admin-shadow);
        }

        .quick-actions h2 {
            margin: 0 0 25px 0;
            color: var(--admin-text);
            font-size: 22px;
            font-weight: 600;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        /* Clases próximas */
        .upcoming-classes {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .upcoming-class-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: var(--admin-bg);
            border-radius: var(--admin-radius);
            border-left: 4px solid var(--admin-primary);
            transition: var(--admin-transition);
        }

        .class-date {
            text-align: center;
            padding: 10px;
            background: var(--admin-primary);
            color: white;
            border-radius: 8px;
            min-width: 60px;
            flex-shrink: 0;
        }

        .class-date .day {
            display: block;
            font-size: 18px;
            font-weight: 700;
        }

        .class-date .month {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
        }

        .class-details {
            flex: 1;
        }

        .class-details h4 {
            margin: 0 0 5px 0;
            color: var(--admin-text);
            font-size: 16px;
        }

        .class-details p {
            margin: 0 0 5px 0;
            color: var(--admin-text-light);
            font-size: 14px;
        }
			        .occupancy {
            font-size: 12px;
            background: rgba(143, 188, 143, 0.1);
            color: var(--admin-primary);
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        .class-status {
            flex-shrink: 0;
        }

        .status-full {
            background: #fef2f2;
            color: #dc2626;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-almost-full {
            background: #fffbeb;
            color: #d97706;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-available {
            background: #f0fdf4;
            color: #16a34a;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Actividad reciente */
        .recent-activity {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 15px;
            background: var(--admin-bg);
            border-radius: 8px;
            transition: var(--admin-transition);
        }

        .activity-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .activity-avatar {
            margin-right: 15px;
            padding: 8px;
            border-radius: 50%;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .activity-details {
            flex: 1;
        }

        .activity-details strong {
            color: var(--admin-text);
            font-weight: 600;
        }

        .activity-time {
            color: var(--admin-text-light);
            font-size: 12px;
            font-style: italic;
        }

        /* Formularios */
        .yoga-form {
            max-width: 800px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--admin-text);
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 16px;
            border: 2px solid var(--admin-border);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--admin-transition);
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px rgba(143, 188, 143, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 25px;
            border-top: 2px solid var(--admin-bg);
        }

        /* Modales */
        .yoga-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 20px;
        }

        .modal-content.large {
            background: white;
            border-radius: var(--admin-radius);
            width: 100%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        .close-modal {
            background: rgba(255,255,255,0.2);
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            color: white;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }

        /* Tablas de reservas */
        .reservations-table-container {
            background: white;
            border-radius: var(--admin-radius);
            overflow: hidden;
            box-shadow: var(--admin-shadow);
        }

        .reservations-table {
            width: 100%;
            border-collapse: collapse;
        }

        .reservations-table th,
        .reservations-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--admin-border);
        }

        .reservations-table th {
            background: var(--admin-bg);
            font-weight: 600;
            color: var(--admin-text);
            font-size: 14px;
        }

        .reservations-table td {
            font-size: 14px;
        }

        .reservations-table tr:hover {
            background: rgba(143, 188, 143, 0.02);
        }

        .client-info strong {
            color: var(--admin-text);
            font-weight: 600;
        }

        .class-info strong {
            color: var(--admin-primary);
            font-weight: 600;
        }

        .contact-info a {
            color: #3182ce;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-changer {
            padding: 6px 12px;
            border: 1px solid var(--admin-border);
            border-radius: 6px;
            font-size: 12px;
            background: white;
        }

        .status-changer:focus {
            outline: none;
            border-color: var(--admin-primary);
        }

        /* Estadísticas de reservas */
        .reservations-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .stat-number {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: var(--admin-text);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--admin-text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-item.confirmed .stat-number {
            color: #16a34a;
        }

        .stat-item.cancelled .stat-number {
            color: #dc2626;
        }

        .stat-item.pending .stat-number {
            color: #d97706;
        }

        /* Estados vacíos */
        .no-data,
        .no-classes,
        .no-reservations {
            text-align: center;
            padding: 60px 20px;
            color: var(--admin-text-light);
        }

        .no-data-illustration {
            margin-bottom: 20px;
        }

        .no-data-illustration .dashicons {
            font-size: 48px;
            color: var(--admin-border);
        }

        .no-data h3,
        .no-classes h3,
        .no-reservations h3 {
            margin: 0 0 10px 0;
            color: var(--admin-text);
            font-size: 20px;
        }

        .no-data p,
        .no-classes p,
        .no-reservations p {
            margin: 0 0 20px 0;
            font-size: 14px;
        }

        /* Ocupación de clases */
        .class-occupancy {
            margin-bottom: 15px;
            padding: 12px;
            background: var(--admin-bg);
            border-radius: 8px;
            border: 1px solid var(--admin-border);
        }

        .occupancy-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--admin-text);
        }

        .occupancy-bar {
            height: 8px;
            background: var(--admin-border);
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
        }

        .occupancy-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            transition: width 0.3s ease;
        }

        /* Información de clases */
        .info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 13px;
            color: var(--admin-text-light);
        }

        .info-row .dashicons {
            font-size: 15px;
            color: var(--admin-primary);
            flex-shrink: 0;
            width: 15px;
        }

        .recurring-indicator {
            margin-left: 8px;
            font-size: 0.8rem;
        }

        /* Notificaciones */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(400px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
        }

        .notification.error {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        }

        /* Spinner de carga */
        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .yoga-admin-content {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .nav-tab {
                padding: 15px 20px;
                font-size: 14px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .reservations-table-container {
                overflow-x: auto;
            }
            
            .reservations-table {
                min-width: 800px;
            }
            
            .upcoming-class-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .class-date {
                align-self: center;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .yoga-admin-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
                padding: 20px;
            }
            
            .admin-logo {
                flex-direction: column;
                gap: 15px;
            }
            
            .logo-image {
                width: 100px !important;
                height: 100px !important;
            }
            
            .logo-info h1 {
                font-size: 24px;
            }
            
            .notification {
                right: 10px;
                left: 10px;
                transform: translateY(-100px);
            }
            
            .notification.show {
                transform: translateY(0);
            }
            }
        </style>
        <?php
    }
}

// ========== FUNCIONES AJAX PARA LA PÁGINA PÚBLICA DE RESERVAS ==========

// Registrar las acciones AJAX para la página pública
add_action('wp_ajax_get_yoga_classes_public', 'handle_get_yoga_classes_public');
add_action('wp_ajax_nopriv_get_yoga_classes_public', 'handle_get_yoga_classes_public');

add_action('wp_ajax_get_class_details', 'handle_get_class_details_public');
add_action('wp_ajax_nopriv_get_class_details', 'handle_get_class_details_public');

add_action('wp_ajax_yoga_cancel_reservation', 'handle_cancel_yoga_reservation_public');
add_action('wp_ajax_nopriv_yoga_cancel_reservation', 'handle_cancel_yoga_reservation_public');

// DEBUG: Verificar que las acciones se registren
error_log('YOGA DEBUG: Acciones de cancelación registradas en admin.php');

add_action('wp_ajax_make_yoga_reservation', 'handle_make_yoga_reservation_public');
add_action('wp_ajax_nopriv_make_yoga_reservation', 'handle_make_yoga_reservation_public');

// Función para obtener clases en la página pública
function handle_get_yoga_classes_public() {
    // Verificar nonce
    if (!wp_verify_nonce($_POST['nonce'], 'yoga_public_nonce')) {
        wp_send_json_error('Error de seguridad');
        return;
    }
    
    global $wpdb;
    
    $date = sanitize_text_field($_POST['date']);
    $filter = sanitize_text_field($_POST['filter']);
    
    // DEBUGGING TEMPORAL
    error_log("🔍 FUNCIÓN EJECUTADA: handle_get_yoga_classes_public");
    error_log("📅 Fecha solicitada: " . $date);
    error_log("🎯 Filtro: " . $filter);
    
    // Asegurar que las tablas existan
    ensure_yoga_tables_exist();
    
    // Consulta base para obtener clases con reservas
    $sql = "SELECT c.*, 
                   (c.max_spots - COUNT(r.id)) AS available_spots,
                   COUNT(r.id) AS reserved_spots
            FROM {$wpdb->prefix}yoga_classes c
            LEFT JOIN {$wpdb->prefix}yoga_reservations r ON c.class_id = r.class_id AND r.status = 'confirmed'
            WHERE c.date = %s
            GROUP BY c.id, c.class_id
            ORDER BY c.time ASC";
    
    $classes = $wpdb->get_results($wpdb->prepare($sql, $date));
    
    error_log("📊 CLASES ENCONTRADAS: " . count($classes) . " para fecha $date");
    
    if ($classes) {
        foreach ($classes as &$class) {
            // Asegurar que available_spots no sea negativo
            if ($class->available_spots < 0) {
                $class->available_spots = 0;
            }
            
            // Determinar el período si no está definido o es incorrecto
            if (empty($class->period) || !in_array($class->period, ['morning', 'afternoon', 'evening'])) {
                $hour = intval(explode(':', $class->time)[0]);
                if ($hour >= 6 && $hour < 12) {
                    $class->period = 'morning';
                } elseif ($hour >= 12 && $hour < 18) {
                    $class->period = 'afternoon';
                } else {
                    $class->period = 'evening';
                }
            }
            
            error_log("✅ Clase: {$class->name} - {$class->time} - Disponibles: {$class->available_spots}/{$class->max_spots} - Período: {$class->period}");
        }
        
        // Aplicar filtro de tiempo si no es "all"
        if ($filter !== 'all') {
            $classes = array_filter($classes, function($class) use ($filter) {
                return $class->period === $filter;
            });
        }
        
        error_log("🎯 CLASES DESPUÉS DEL FILTRO '$filter': " . count($classes));
        
        wp_send_json_success(['classes' => array_values($classes)]);
    } else {
        error_log("❌ NO SE ENCONTRARON CLASES para fecha $date");
        wp_send_json_success(['classes' => []]);
    }
}

// Función para obtener detalles de una clase específica
function handle_get_class_details_public() {
    if (!wp_verify_nonce($_POST['nonce'], 'yoga_public_nonce')) {
        wp_send_json_error('Error de seguridad');
        return;
    }
    
    global $wpdb;
    
    $class_id = sanitize_text_field($_POST['class_id']);
    
    $class = $wpdb->get_row($wpdb->prepare("
        SELECT c.*, 
               (c.max_spots - COUNT(r.id)) AS available_spots,
               COUNT(r.id) AS reserved_spots
        FROM {$wpdb->prefix}yoga_classes c
        LEFT JOIN {$wpdb->prefix}yoga_reservations r ON c.class_id = r.class_id AND r.status = 'confirmed'
        WHERE c.class_id = %s
        GROUP BY c.id, c.class_id
    ", $class_id));
    
    if ($class) {
        // Asegurar que available_spots no sea negativo
        if ($class->available_spots < 0) {
            $class->available_spots = 0;
        }
        
        wp_send_json_success($class);
    } else {
        wp_send_json_error('Clase no encontrada');
    }
}

// Función para cancelar una reserva
function handle_cancel_yoga_reservation_public() {
    // Verificar que llegan los datos
    if (!isset($_POST['class_id']) || !isset($_POST['email'])) {
        wp_send_json_error('Datos incompletos');
        return;
    }
    
    global $wpdb;
    $class_id = sanitize_text_field($_POST['class_id']);
    $email = sanitize_email($_POST['email']);
    
    // Usar directamente la tabla que sabemos que existe
    $table_name = $wpdb->prefix . 'yoga_reservations';
    
    // Verificar que la tabla existe
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        wp_send_json_error('Error interno del sistema');
        return;
    }
    
    // Buscar la reserva usando diferentes formatos de class_id
    $reservation = null;
    
    // Primero buscar por el class_id tal como llegó
    $reservation = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM $table_name 
        WHERE class_id = %s AND user_email = %s AND (status = 'confirmed' OR status IS NULL)
    ", $class_id, $email));
    
    if (!$reservation) {
        // Buscar por class_id que contenga el número
        $reservation = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name 
            WHERE class_id LIKE %s AND user_email = %s AND (status = 'confirmed' OR status IS NULL)
        ", '%' . $class_id . '%', $email));
    }
    
    if (!$reservation) {
        // Buscar sin filtro de class_id, solo por email (reserva más reciente)
        $reservation = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table_name 
            WHERE user_email = %s AND (status = 'confirmed' OR status IS NULL)
            ORDER BY created_at DESC LIMIT 1
        ", $email));
    }
    
    if (!$reservation) {
        wp_send_json_error('No se encontró una reserva activa con ese email para esta clase');
        return;
    }
    
    // Cancelar la reserva
    $result = $wpdb->update(
        $table_name,
        ['status' => 'cancelled'],
        ['id' => $reservation->id],
        ['%s'],
        ['%d']
    );
    
    if ($result !== false) {
		
        // Obtener datos de la clase para el mensaje
        $class_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yoga_classes WHERE class_id = %s OR class_id LIKE %s",
            $reservation->class_id, '%' . $reservation->class_id . '%'
        ));
        // Enviar email de confirmación de cancelación
if ($class_data) {
    send_cancellation_confirmation_email($reservation, $class_data);
}
        wp_send_json_success(array(
            'message' => 'Reserva cancelada exitosamente',
            'user_name' => $reservation->user_name,
            'class_name' => $class_data ? $class_data->name : 'clase',
            'class_date' => $reservation->class_date,
            'class_time' => $reservation->class_time
        ));
    } else {
        wp_send_json_error('Error al procesar la cancelación. Por favor, intenta nuevamente.');
    }
}

// Función para hacer una nueva reserva
function handle_make_yoga_reservation_public() {
    if (!wp_verify_nonce($_POST['nonce'], 'yoga_public_nonce')) {
        wp_send_json_error('Error de seguridad');
        return;
    }
    
    global $wpdb;
    
    $class_id = sanitize_text_field($_POST['class_id']);
    $user_name = sanitize_text_field($_POST['userName']);
    $user_email = sanitize_email($_POST['userEmail']);
    $user_phone = sanitize_text_field($_POST['userPhone']);
    
    // Validar datos
    if (empty($class_id) || empty($user_name) || empty($user_email)) {
        wp_send_json_error('Todos los campos son obligatorios');
        return;
    }
    
    // Verificar que la clase existe y tiene cupo
    $class = $wpdb->get_row($wpdb->prepare("
        SELECT c.*, 
               (c.max_spots - COUNT(r.id)) AS available_spots
        FROM {$wpdb->prefix}yoga_classes c
        LEFT JOIN {$wpdb->prefix}yoga_reservations r ON c.class_id = r.class_id AND r.status = 'confirmed'
        WHERE c.class_id = %s
        GROUP BY c.id, c.class_id
    ", $class_id));
    
    if (!$class) {
        wp_send_json_error('La clase no existe');
        return;
    }
    
    if ($class->available_spots <= 0) {
        wp_send_json_error('La clase está completa');
        return;
    }
    
    // Verificar que el email no tenga ya una reserva para esta clase
    $existing = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM {$wpdb->prefix}yoga_reservations 
        WHERE class_id = %s AND user_email = %s AND status = 'confirmed'
    ", $class_id, $user_email));
    
    if ($existing > 0) {
        wp_send_json_error('Ya tienes una reserva confirmada para esta clase');
        return;
    }
    
    // Crear la reserva
    $result = $wpdb->insert(
        $wpdb->prefix . 'yoga_reservations',
        [
            'class_id' => $class_id,
            'user_name' => $user_name,
            'user_email' => $user_email,
            'user_phone' => $user_phone,
            'status' => 'confirmed',
            'created_at' => current_time('mysql')
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s']
    );
    
    if ($result !== false) {
        // Enviar email de confirmación
        $reservation_data = [
            'user_name' => $user_name,
            'user_email' => $user_email,
            'user_phone' => $user_phone
        ];
        
$email_sent = send_reservation_confirmation_email($reservation_data, $class);
		
        wp_send_json_success([
            'message' => 'Reserva confirmada exitosamente',
            'reservation_id' => $wpdb->insert_id,
            'email_sent' => $email_sent
        ]);
    } else {
        wp_send_json_error('Error al crear la reserva: ' . $wpdb->last_error);
    }
}

// Función de email de confirmación para la página pública
function send_reservation_confirmation_email_public($reservation_data, $class_data) {
    add_filter('wp_mail_content_type', function() { return 'text/html'; });
    
    $to = $reservation_data['user_email'];
    $subject = 'Reserva Confirmada - Kurunta Yoga';
    
    // FECHA EN ESPAÑOL
    $date = new DateTime($class_data->date);
    $days = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 
               'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    
    $day_name = $days[$date->format('w')];
    $day_number = $date->format('d');
    $month_name = $months[$date->format('n') - 1];
    $year = $date->format('Y');
    
    $date_formatted = ucfirst($day_name) . ', ' . $day_number . ' de ' . $month_name . ' de ' . $year;
    $time_formatted = date('H:i', strtotime($class_data->time));
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { 
                font-family: 'Montserrat', Arial, sans-serif; 
                margin: 0; 
                padding: 0; 
                background-color: #f4f3f1; 
                line-height: 1.6;
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background: white; 
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #94a484 0%, #95a485 100%);
                color: white; 
                padding: 40px 30px; 
                text-align: center; 
                position: relative;
            }
            .logo { 
                width: 120px; 
                height: auto; 
                margin: 0 auto 20px auto;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            }
            .content { 
                padding: 40px 30px; 
            }
            .details { 
                background: #f4f3f1; 
                padding: 25px; 
                border-radius: 8px; 
                margin: 25px 0;
                border-left: 4px solid #94a484;
            }
            .footer { 
                background: #2c2c2c; 
                color: white; 
                padding: 30px; 
                text-align: center; 
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <img src='https://kuruntayoga.com.mx/wp-content/uploads/2025/06/icono.png' alt='Kurunta Yoga' class='logo'>
                <h1>Kurunta Yoga</h1>
                <p>by Ana Sordo</p>
            </div>
            
            <div class='content'>
                <h2>Hola {$reservation_data['user_name']},</h2>
                
                <p>Tu reserva ha sido <strong>confirmada exitosamente</strong>. Te esperamos en la clase.</p>
                
                <div class='details'>
                    <h3>Detalles de tu clase:</h3>
                    <p><strong>Clase:</strong> {$class_data->name}</p>
                    <p><strong>Instructor:</strong> {$class_data->instructor}</p>
                    <p><strong>Fecha:</strong> {$date_formatted}</p>
                    <p><strong>Hora:</strong> {$time_formatted}</p>
                    <p><strong>Duración:</strong> {$class_data->duration} minutos</p>
                </div>
                
                <p><strong>Nos vemos en la clase.</strong></p>
                <p><em>Namaste</em></p>
            </div>
            
            <div class='footer'>
                <h4>Kurunta Yoga by Ana Sordo</h4>
                <p>anasordo@kuruntayoga.com.mx</p>
                <p>kuruntayoga.com.mx</p>
                <p>Tel: 5531245645</p>
            </div>
        </div>
    </body>
    </html>";
        
    // Configuración de email
    $from_email = get_option('yoga_email_from', 'anasordo@kuruntayoga.com.mx');
    $from_name = get_option('yoga_email_from_name', 'Ana Sordo - Kurunta Yoga');
    
    error_log('YOGA EMAIL: Enviando desde ' . $from_email . ' (' . $from_name . ') a ' . $to);

    $headers = array(
        'From: ' . $from_name . ' <' . $from_email . '>',
        'Reply-To: ' . $from_email,
        'Content-Type: text/html; charset=UTF-8'
    );
    
    $result = wp_mail($to, $subject, $message, $headers);
    
    remove_filter('wp_mail_content_type', function() { return 'text/html'; });
    
    return $result;
}

