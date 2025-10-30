<?php
/**
 * Plugin Name: Firestarter Chatbot
 * Plugin URI: https://github.com/mendableai/firestarter
 * Description: Plugin de WordPress para integrar con Firestarter - Crea chatbots AI para cualquier sitio web
 * Version: 1.0.0
 * Author: Firestarter
 * Author URI: https://github.com/mendableai/firestarter
 * License: MIT
 * Text Domain: firestarter-chatbot
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('FIRESTARTER_VERSION', '1.0.0');
define('FIRESTARTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FIRESTARTER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir archivos necesarios
require_once FIRESTARTER_PLUGIN_DIR . 'admin/class-firestarter-admin.php';

// Inicializar el plugin
function firestarter_init() {
    if (is_admin()) {
        new Firestarter_Admin();
    }
}
add_action('plugins_loaded', 'firestarter_init');

// Hook de activación
register_activation_hook(__FILE__, 'firestarter_activate');
function firestarter_activate() {
    // Crear opciones por defecto
    if (!get_option('firestarter_api_url')) {
        add_option('firestarter_api_url', '');
    }
}

// Hook de desactivación
register_deactivation_hook(__FILE__, 'firestarter_deactivate');
function firestarter_deactivate() {
    // Limpieza si es necesario
}
