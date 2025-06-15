// ========== SISTEMA DE RESERVAS YOGA - COMPLETO ==========

// Cargar scripts y estilos para el sistema de reservas
function yoga_booking_enqueue_scripts() {
    // Solo cargar en páginas que contengan el shortcode
    global $post;
    
    if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'yoga_reservas') || is_admin())) {
        
        // Cargar jQuery si no está cargado
        wp_enqueue_script('jquery');
        
        // Localizar script para AJAX
        wp_localize_script('jquery', 'yoga_booking_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yoga_booking_nonce')
        ));
    }
}

add_action('wp_enqueue_scripts', 'yoga_booking_enqueue_scripts');
add_action('admin_enqueue_scripts', 'yoga_booking_enqueue_scripts');

// ========== HANDLERS AJAX PARA RESERVAS ==========

// Handler para crear reservas (EL QUE FALTABA)
add_action('wp_ajax_yoga_book_class', 'handle_yoga_book_class');
add_action('wp_ajax_nopriv_yoga_book_class', 'handle_yoga_book_class');

function handle_yoga_book_class() {
    error_log('=== YOGA BOOKING DEBUG START ===');
    error_log('POST data: ' . json_encode($_POST));
    
    // Verificar nonce
    if (!wp_verify_nonce($_POST['nonce'], 'yoga_public_nonce')) {
        error_log('YOGA BOOKING - Nonce inválido');
        wp_die(json_encode(['success' => false, 'data' => 'Nonce inválido']));
    }
    
    // Obtener datos del POST
    $class_id = sanitize_text_field($_POST['class_id']);
    $participant_name = sanitize_text_field($_POST['participant_name']);
    $participant_email = sanitize_email($_POST['participant_email']);
    $participant_phone = sanitize_text_field($_POST['participant_phone']);
    $class_date = sanitize_text_field($_POST['class_date']);
    $class_time = sanitize_text_field($_POST['class_time']);
    
    // Validar datos requeridos
    if (empty($class_id) || empty($participant_name) || empty($participant_email)) {
        wp_die(json_encode(['success' => false, 'data' => 'Datos incompletos']));
    }
    
    global $wpdb;
    
    // Buscar tabla existente con diferentes nombres posibles
    $possible_tables = [
        $wpdb->prefix . 'yoga_reservations',
        $wpdb->prefix . 'reservations', 
        $wpdb->prefix . 'yoga_bookings',
        $wpdb->prefix . 'bookings',
        $wpdb->prefix . 'yoga_classes_bookings'
    ];
    
    $table_name = null;
    foreach ($possible_tables as $test_table) {
        if ($wpdb->get_var("SHOW TABLES LIKE '$test_table'") == $test_table) {
            $table_name = $test_table;
            break;
        }
    }
    
    if (!$table_name) {
        wp_die(json_encode(['success' => false, 'data' => 'No se encontró tabla de reservas']));
    }
    
    // Obtener estructura de la tabla existente
    $columns = $wpdb->get_results("DESCRIBE $table_name");
    $column_names = array_column($columns, 'Field');
    
    error_log('Tabla encontrada: ' . $table_name);
    error_log('Columnas disponibles: ' . json_encode($column_names));
    
    // Mapear nuestros datos a las columnas existentes
    $column_mapping = [
        // Posibles nombres para participant_name
        'name' => ['participant_name', 'name', 'client_name', 'user_name', 'customer_name'],
        // Posibles nombres para participant_email  
        'email' => ['participant_email', 'email', 'client_email', 'user_email', 'customer_email'],
        // Posibles nombres para participant_phone
        'phone' => ['participant_phone', 'phone', 'client_phone', 'user_phone', 'customer_phone', 'telephone'],
        // Posibles nombres para class_id
        'class_id' => ['class_id', 'classe_id', 'yoga_class_id', 'booking_class_id'],
        // Posibles nombres para class_date
        'class_date' => ['class_date', 'date', 'booking_date', 'reservation_date', 'event_date'],
        // Posibles nombres para class_time
        'class_time' => ['class_time', 'time', 'booking_time', 'reservation_time', 'event_time'],
        // Posibles nombres para status
        'status' => ['status', 'booking_status', 'reservation_status', 'state']
    ];
    
    // Encontrar las columnas correctas
    $mapped_columns = [];
    foreach ($column_mapping as $our_field => $possible_names) {
        foreach ($possible_names as $possible_name) {
            if (in_array($possible_name, $column_names)) {
                $mapped_columns[$our_field] = $possible_name;
                break;
            }
        }
    }
    
    error_log('Mapeo de columnas: ' . json_encode($mapped_columns));
    
    // Verificar que tenemos al menos las columnas esenciales
    if (!isset($mapped_columns['name']) || !isset($mapped_columns['email'])) {
        wp_die(json_encode(['success' => false, 'data' => 'Estructura de tabla incompatible. Columnas requeridas no encontradas.']));
    }
    
    // Preparar datos para insertar usando los nombres correctos
    $insert_data = [];
    $format_array = [];
    
    // Agregar campos mapeados
    if (isset($mapped_columns['name'])) {
        $insert_data[$mapped_columns['name']] = $participant_name;
        $format_array[] = '%s';
    }
    
    if (isset($mapped_columns['email'])) {
        $insert_data[$mapped_columns['email']] = $participant_email;
        $format_array[] = '%s';
    }
    
    if (isset($mapped_columns['phone']) && !empty($participant_phone)) {
        $insert_data[$mapped_columns['phone']] = $participant_phone;
        $format_array[] = '%s';
    }
    
    if (isset($mapped_columns['class_id'])) {
        $insert_data[$mapped_columns['class_id']] = $class_id;
        $format_array[] = '%s';
    }
    
    if (isset($mapped_columns['class_date'])) {
        $insert_data[$mapped_columns['class_date']] = $class_date;
        $format_array[] = '%s';
    }
    
    if (isset($mapped_columns['class_time'])) {
        $insert_data[$mapped_columns['class_time']] = $class_time;
        $format_array[] = '%s';
    }
    
    if (isset($mapped_columns['status'])) {
        $insert_data[$mapped_columns['status']] = 'confirmed';
        $format_array[] = '%s';
    }
    
    // Agregar timestamp si existe columna de fecha de creación
    $timestamp_columns = ['created_at', 'reservation_date', 'booking_date', 'date_created'];
    foreach ($timestamp_columns as $ts_col) {
        if (in_array($ts_col, $column_names)) {
            $insert_data[$ts_col] = current_time('mysql');
            $format_array[] = '%s';
            break;
        }
    }
    
    error_log('Datos finales a insertar: ' . json_encode($insert_data));
    error_log('Formatos: ' . json_encode($format_array));
    
    // Insertar la reserva
    $result = $wpdb->insert($table_name, $insert_data, $format_array);
    
    error_log('Resultado insert: ' . ($result === false ? 'FALSE' : $result));
    error_log('Último error: ' . $wpdb->last_error);
    error_log('Insert ID: ' . $wpdb->insert_id);
    
    if ($result === false) {
        wp_die(json_encode(['success' => false, 'data' => 'Error BD: ' . $wpdb->last_error]));
    }
    
    error_log('=== YOGA BOOKING SUCCESS ===');
    
    // Respuesta exitosa
    wp_die(json_encode([
        'success' => true, 
        'data' => 'Reserva creada exitosamente',
        'reservation_id' => $wpdb->insert_id,
        'table_used' => $table_name,
        'columns_mapped' => $mapped_columns
    ]));
}

// Handler para cargar clases (si no existe ya)
if (!function_exists('handle_get_yoga_classes_public')) {
    add_action('wp_ajax_get_yoga_classes_public', 'handle_get_yoga_classes_public');
    add_action('wp_ajax_nopriv_get_yoga_classes_public', 'handle_get_yoga_classes_public');
    

}