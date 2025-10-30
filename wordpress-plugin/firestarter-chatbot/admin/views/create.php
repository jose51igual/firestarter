<?php
if (!defined('ABSPATH')) {
    exit;
}

$api_url = get_option('firestarter_api_url', '');
?>

<div class="wrap firestarter-create">
    <h1>Crear Nuevo Chatbot</h1>

    <?php if (empty($api_url)): ?>
        <div class="notice notice-error">
            <p>
                <strong>Error:</strong>
                Debes configurar la URL de la API de Firestarter antes de crear un chatbot.
                <a href="<?php echo admin_url('admin.php?page=firestarter-settings'); ?>">
                    Ir a Configuración
                </a>
            </p>
        </div>
    <?php else: ?>
        <div class="firestarter-card">
            <form id="firestarter-create-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="website_url">URL del Sitio Web</label>
                        </th>
                        <td>
                            <input
                                type="url"
                                name="website_url"
                                id="website_url"
                                class="regular-text"
                                placeholder="https://ejemplo.com"
                                required
                            >
                            <p class="description">
                                Ingresa la URL del sitio web que quieres indexar para el chatbot.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="crawl_limit">Límite de Páginas</label>
                        </th>
                        <td>
                            <select name="crawl_limit" id="crawl_limit">
                                <option value="10">10 páginas</option>
                                <option value="25">25 páginas</option>
                                <option value="50" selected>50 páginas</option>
                                <option value="100">100 páginas</option>
                            </select>
                            <p class="description">
                                Número máximo de páginas a crawlear. Más páginas = mejor cobertura pero más tiempo de procesamiento.
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary" id="create-chatbot-btn">
                        Crear Chatbot
                    </button>
                </p>
            </form>

            <!-- Progress indicator -->
            <div id="creation-progress" style="display: none;">
                <div class="firestarter-progress">
                    <div class="firestarter-spinner">
                        <span class="spinner is-active"></span>
                    </div>
                    <p class="progress-message">Crawleando el sitio web y creando el índice...</p>
                    <p class="progress-note">Este proceso puede tomar varios minutos dependiendo del tamaño del sitio.</p>
                </div>
            </div>

            <!-- Success message -->
            <div id="creation-success" class="notice notice-success" style="display: none;">
                <p>
                    <strong>¡Chatbot creado exitosamente!</strong><br>
                    <span id="success-details"></span>
                </p>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=firestarter-chatbots'); ?>" class="button button-primary">
                        Ver Mis Chatbots
                    </a>
                </p>
            </div>

            <!-- Error message -->
            <div id="creation-error" class="notice notice-error" style="display: none;">
                <p>
                    <strong>Error al crear el chatbot:</strong><br>
                    <span id="error-details"></span>
                </p>
            </div>
        </div>

        <div class="firestarter-info">
            <h2>¿Cómo funciona?</h2>
            <ol>
                <li><strong>Crawling:</strong> Firecrawl visitará las páginas del sitio web y extraerá el contenido.</li>
                <li><strong>Indexación:</strong> El contenido se convertirá en embeddings vectoriales y se almacenará en Upstash.</li>
                <li><strong>Listo:</strong> El chatbot estará listo para responder preguntas sobre el contenido del sitio.</li>
            </ol>
            <p>
                <strong>Nota:</strong> El proceso puede tardar desde unos segundos hasta varios minutos
                dependiendo del tamaño del sitio web y el límite de páginas seleccionado.
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('#firestarter-create-form').on('submit', function(e) {
        e.preventDefault();

        const url = $('#website_url').val();
        const limit = $('#crawl_limit').val();

        // Ocultar mensajes previos
        $('#creation-success, #creation-error').hide();

        // Mostrar progreso
        $('#creation-progress').show();
        $('#create-chatbot-btn').prop('disabled', true);

        $.ajax({
            url: firestarter.ajax_url,
            type: 'POST',
            data: {
                action: 'firestarter_create_chatbot',
                nonce: firestarter.nonce,
                url: url,
                limit: limit
            },
            success: function(response) {
                $('#creation-progress').hide();
                $('#create-chatbot-btn').prop('disabled', false);

                if (response.success) {
                    const data = response.data;
                    $('#success-details').html(
                        `Namespace: <code>${data.namespace}</code><br>` +
                        `Páginas crawleadas: ${data.details.pagesCrawled}`
                    );
                    $('#creation-success').show();
                    $('#firestarter-create-form')[0].reset();
                } else {
                    $('#error-details').text(response.data.message || 'Error desconocido');
                    $('#creation-error').show();
                }
            },
            error: function(xhr, status, error) {
                $('#creation-progress').hide();
                $('#create-chatbot-btn').prop('disabled', false);
                $('#error-details').text(error || 'Error de conexión');
                $('#creation-error').show();
            }
        });
    });
});
</script>
