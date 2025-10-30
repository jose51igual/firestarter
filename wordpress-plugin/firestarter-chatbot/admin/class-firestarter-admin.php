<?php
/**
 * Clase principal de administración para Firestarter Chatbot
 */

if (!defined('ABSPATH')) {
    exit;
}

class Firestarter_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_firestarter_create_chatbot', array($this, 'ajax_create_chatbot'));
        add_action('wp_ajax_firestarter_query_chatbot', array($this, 'ajax_query_chatbot'));
        add_action('wp_ajax_firestarter_get_indexes', array($this, 'ajax_get_indexes'));
        add_action('wp_ajax_firestarter_delete_chatbot', array($this, 'ajax_delete_chatbot'));
    }

    /**
     * Agregar menú de administración
     */
    public function add_admin_menu() {
        // Menú principal
        add_menu_page(
            'Firestarter Chatbot',
            'Firestarter',
            'manage_options',
            'firestarter-chatbot',
            array($this, 'render_dashboard_page'),
            'dashicons-format-chat',
            30
        );

        // Submenú - Dashboard
        add_submenu_page(
            'firestarter-chatbot',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'firestarter-chatbot',
            array($this, 'render_dashboard_page')
        );

        // Submenú - Crear Chatbot
        add_submenu_page(
            'firestarter-chatbot',
            'Crear Chatbot',
            'Crear Chatbot',
            'manage_options',
            'firestarter-create',
            array($this, 'render_create_page')
        );

        // Submenú - Mis Chatbots
        add_submenu_page(
            'firestarter-chatbot',
            'Mis Chatbots',
            'Mis Chatbots',
            'manage_options',
            'firestarter-chatbots',
            array($this, 'render_chatbots_page')
        );

        // Submenú - Configuración
        add_submenu_page(
            'firestarter-chatbot',
            'Configuración',
            'Configuración',
            'manage_options',
            'firestarter-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Encolar assets de admin
     */
    public function enqueue_admin_assets($hook) {
        // Solo cargar en páginas de Firestarter
        if (strpos($hook, 'firestarter') === false) {
            return;
        }

        // CSS
        wp_enqueue_style(
            'firestarter-admin-css',
            FIRESTARTER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            FIRESTARTER_VERSION
        );

        // JavaScript
        wp_enqueue_script(
            'firestarter-admin-js',
            FIRESTARTER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            FIRESTARTER_VERSION,
            true
        );

        // Pasar datos a JavaScript
        wp_localize_script('firestarter-admin-js', 'firestarter', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('firestarter_nonce'),
            'api_url' => get_option('firestarter_api_url', '')
        ));
    }

    /**
     * Renderizar página de dashboard
     */
    public function render_dashboard_page() {
        include FIRESTARTER_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Renderizar página de crear chatbot
     */
    public function render_create_page() {
        include FIRESTARTER_PLUGIN_DIR . 'admin/views/create.php';
    }

    /**
     * Renderizar página de chatbots
     */
    public function render_chatbots_page() {
        include FIRESTARTER_PLUGIN_DIR . 'admin/views/chatbots.php';
    }

    /**
     * Renderizar página de configuración
     */
    public function render_settings_page() {
        // Guardar configuración si se envió el formulario
        if (isset($_POST['firestarter_save_settings'])) {
            check_admin_referer('firestarter_settings_nonce');
            update_option('firestarter_api_url', sanitize_text_field($_POST['firestarter_api_url']));
            echo '<div class="notice notice-success"><p>Configuración guardada correctamente.</p></div>';
        }

        include FIRESTARTER_PLUGIN_DIR . 'admin/views/settings.php';
    }

    /**
     * AJAX: Crear chatbot
     */
    public function ajax_create_chatbot() {
        check_ajax_referer('firestarter_nonce', 'nonce');

        $url = sanitize_text_field($_POST['url']);
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        $api_url = get_option('firestarter_api_url', '');

        if (empty($api_url)) {
            wp_send_json_error(array('message' => 'URL de API no configurada'));
            return;
        }

        // Hacer llamada al endpoint de Firestarter
        $response = wp_remote_post($api_url . '/api/firestarter/create', array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'url' => $url,
                'limit' => $limit
            )),
            'timeout' => 120
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            wp_send_json_error(array('message' => $data['error']));
            return;
        }

        wp_send_json_success($data);
    }

    /**
     * AJAX: Consultar chatbot
     */
    public function ajax_query_chatbot() {
        check_ajax_referer('firestarter_nonce', 'nonce');

        $query = sanitize_text_field($_POST['query']);
        $namespace = sanitize_text_field($_POST['namespace']);
        $api_url = get_option('firestarter_api_url', '');

        if (empty($api_url)) {
            wp_send_json_error(array('message' => 'URL de API no configurada'));
            return;
        }

        // Hacer llamada al endpoint de Firestarter
        $response = wp_remote_post($api_url . '/api/firestarter/query', array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'query' => $query,
                'namespace' => $namespace,
                'stream' => false
            )),
            'timeout' => 60
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            wp_send_json_error(array('message' => $data['error']));
            return;
        }

        wp_send_json_success($data);
    }

    /**
     * AJAX: Obtener índices/chatbots
     */
    public function ajax_get_indexes() {
        check_ajax_referer('firestarter_nonce', 'nonce');

        $api_url = get_option('firestarter_api_url', '');

        if (empty($api_url)) {
            wp_send_json_error(array('message' => 'URL de API no configurada'));
            return;
        }

        // Hacer llamada al endpoint de Firestarter
        $response = wp_remote_get($api_url . '/api/indexes', array(
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        wp_send_json_success($data);
    }

    /**
     * AJAX: Eliminar chatbot
     */
    public function ajax_delete_chatbot() {
        check_ajax_referer('firestarter_nonce', 'nonce');

        $namespace = sanitize_text_field($_POST['namespace']);
        $api_url = get_option('firestarter_api_url', '');

        if (empty($api_url)) {
            wp_send_json_error(array('message' => 'URL de API no configurada'));
            return;
        }

        // Hacer llamada al endpoint de Firestarter
        $response = wp_remote_request($api_url . '/api/indexes?namespace=' . urlencode($namespace), array(
            'method' => 'DELETE',
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        wp_send_json_success($data);
    }
}
