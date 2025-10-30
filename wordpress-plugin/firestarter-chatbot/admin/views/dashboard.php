<?php
if (!defined('ABSPATH')) {
    exit;
}

$api_url = get_option('firestarter_api_url', '');
?>

<div class="wrap firestarter-dashboard">
    <h1>Dashboard de Firestarter Chatbot</h1>

    <?php if (empty($api_url)): ?>
        <div class="notice notice-warning">
            <p>
                <strong>Configuración requerida:</strong>
                Por favor configura la URL de tu API de Firestarter antes de continuar.
                <a href="<?php echo admin_url('admin.php?page=firestarter-settings'); ?>">
                    Ir a Configuración
                </a>
            </p>
        </div>
    <?php endif; ?>

    <div class="firestarter-grid">
        <!-- Card: Crear Chatbot -->
        <div class="firestarter-card firestarter-card-action">
            <div class="firestarter-card-icon">
                <span class="dashicons dashicons-plus-alt"></span>
            </div>
            <h2>Crear Nuevo Chatbot</h2>
            <p>Crea un chatbot AI para cualquier sitio web. El sistema crawleará el sitio y creará un índice para responder preguntas.</p>
            <a href="<?php echo admin_url('admin.php?page=firestarter-create'); ?>" class="button button-primary">
                Crear Chatbot
            </a>
        </div>

        <!-- Card: Mis Chatbots -->
        <div class="firestarter-card firestarter-card-action">
            <div class="firestarter-card-icon">
                <span class="dashicons dashicons-format-chat"></span>
            </div>
            <h2>Mis Chatbots</h2>
            <p>Ver y administrar todos los chatbots que has creado. Realiza consultas y elimina chatbots que ya no necesites.</p>
            <a href="<?php echo admin_url('admin.php?page=firestarter-chatbots'); ?>" class="button button-primary">
                Ver Chatbots
            </a>
        </div>

        <!-- Card: Estadísticas -->
        <div class="firestarter-card">
            <h3>Estadísticas Rápidas</h3>
            <div id="firestarter-stats" class="firestarter-stats">
                <div class="stat-item">
                    <span class="stat-label">Total de Chatbots:</span>
                    <span class="stat-value" id="total-chatbots">--</span>
                </div>
            </div>
        </div>

        <!-- Card: Información -->
        <div class="firestarter-card">
            <h3>Acerca de Firestarter</h3>
            <p>
                Firestarter utiliza tecnología RAG (Retrieval Augmented Generation) para crear chatbots
                inteligentes que pueden responder preguntas sobre el contenido de cualquier sitio web.
            </p>
            <p>
                <strong>Tecnologías utilizadas:</strong>
            </p>
            <ul>
                <li>Firecrawl - Web scraping</li>
                <li>Upstash Search - Base de datos vectorial</li>
                <li>OpenAI/Anthropic/Groq - Modelos de lenguaje</li>
            </ul>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Cargar estadísticas
    $.ajax({
        url: firestarter.ajax_url,
        type: 'POST',
        data: {
            action: 'firestarter_get_indexes',
            nonce: firestarter.nonce
        },
        success: function(response) {
            if (response.success && response.data.indexes) {
                $('#total-chatbots').text(response.data.indexes.length);
            }
        }
    });
});
</script>
