<?php
if (!defined('ABSPATH')) {
    exit;
}

$api_url = get_option('firestarter_api_url', '');
?>

<div class="wrap firestarter-chatbots">
    <h1>Mis Chatbots</h1>

    <?php if (empty($api_url)): ?>
        <div class="notice notice-error">
            <p>
                <strong>Error:</strong>
                Debes configurar la URL de la API de Firestarter.
                <a href="<?php echo admin_url('admin.php?page=firestarter-settings'); ?>">
                    Ir a Configuración
                </a>
            </p>
        </div>
    <?php else: ?>
        <div id="chatbots-loading" class="firestarter-loading">
            <span class="spinner is-active"></span>
            <p>Cargando chatbots...</p>
        </div>

        <div id="chatbots-list" style="display: none;">
            <!-- Lista de chatbots se cargará aquí via AJAX -->
        </div>

        <div id="no-chatbots" style="display: none;">
            <div class="firestarter-empty-state">
                <span class="dashicons dashicons-format-chat"></span>
                <h2>No hay chatbots todavía</h2>
                <p>Crea tu primer chatbot para empezar a usar Firestarter.</p>
                <a href="<?php echo admin_url('admin.php?page=firestarter-create'); ?>" class="button button-primary">
                    Crear Chatbot
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para consultar chatbot -->
<div id="query-modal" class="firestarter-modal" style="display: none;">
    <div class="firestarter-modal-content">
        <div class="firestarter-modal-header">
            <h2>Consultar Chatbot: <span id="modal-chatbot-title"></span></h2>
            <button type="button" class="firestarter-modal-close">&times;</button>
        </div>
        <div class="firestarter-modal-body">
            <div id="chat-messages" class="firestarter-chat-messages">
                <!-- Mensajes del chat se mostrarán aquí -->
            </div>
            <div class="firestarter-chat-input">
                <input
                    type="text"
                    id="chat-query-input"
                    placeholder="Escribe tu pregunta..."
                    class="regular-text"
                >
                <button type="button" id="send-query-btn" class="button button-primary">
                    Enviar
                </button>
            </div>
            <div id="query-loading" style="display: none;">
                <span class="spinner is-active"></span>
                Procesando pregunta...
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let currentNamespace = '';

    // Cargar chatbots
    function loadChatbots() {
        $.ajax({
            url: firestarter.ajax_url,
            type: 'POST',
            data: {
                action: 'firestarter_get_indexes',
                nonce: firestarter.nonce
            },
            success: function(response) {
                $('#chatbots-loading').hide();

                if (response.success && response.data.indexes && response.data.indexes.length > 0) {
                    displayChatbots(response.data.indexes);
                    $('#chatbots-list').show();
                } else {
                    $('#no-chatbots').show();
                }
            },
            error: function() {
                $('#chatbots-loading').hide();
                alert('Error al cargar los chatbots');
            }
        });
    }

    // Mostrar chatbots
    function displayChatbots(chatbots) {
        let html = '<div class="firestarter-chatbots-grid">';

        chatbots.forEach(function(chatbot) {
            const title = chatbot.metadata?.title || chatbot.url || 'Sin título';
            const description = chatbot.metadata?.description || 'Sin descripción';
            const favicon = chatbot.metadata?.favicon || '';
            const pages = chatbot.pagesCrawled || 0;
            const date = new Date(chatbot.createdAt).toLocaleDateString('es-ES');

            html += `
                <div class="firestarter-chatbot-card" data-namespace="${chatbot.namespace}">
                    <div class="chatbot-card-header">
                        ${favicon ? `<img src="${favicon}" class="chatbot-favicon" alt="">` : ''}
                        <h3>${title}</h3>
                    </div>
                    <div class="chatbot-card-body">
                        <p class="chatbot-description">${description}</p>
                        <div class="chatbot-meta">
                            <span><strong>URL:</strong> <a href="${chatbot.url}" target="_blank">${chatbot.url}</a></span>
                            <span><strong>Páginas:</strong> ${pages}</span>
                            <span><strong>Creado:</strong> ${date}</span>
                            <span><strong>Namespace:</strong> <code>${chatbot.namespace}</code></span>
                        </div>
                    </div>
                    <div class="chatbot-card-actions">
                        <button class="button button-primary query-chatbot" data-namespace="${chatbot.namespace}" data-title="${title}">
                            Consultar
                        </button>
                        <button class="button button-secondary delete-chatbot" data-namespace="${chatbot.namespace}">
                            Eliminar
                        </button>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        $('#chatbots-list').html(html);
    }

    // Abrir modal de consulta
    $(document).on('click', '.query-chatbot', function() {
        currentNamespace = $(this).data('namespace');
        const title = $(this).data('title');

        $('#modal-chatbot-title').text(title);
        $('#chat-messages').empty();
        $('#chat-query-input').val('');
        $('#query-modal').fadeIn();
    });

    // Cerrar modal
    $('.firestarter-modal-close, .firestarter-modal').on('click', function(e) {
        if (e.target === this) {
            $('#query-modal').fadeOut();
        }
    });

    // Enviar consulta
    function sendQuery() {
        const query = $('#chat-query-input').val().trim();
        if (!query) return;

        // Añadir mensaje del usuario
        $('#chat-messages').append(`
            <div class="chat-message user-message">
                <strong>Tú:</strong> ${query}
            </div>
        `);

        $('#chat-query-input').val('');
        $('#query-loading').show();

        $.ajax({
            url: firestarter.ajax_url,
            type: 'POST',
            data: {
                action: 'firestarter_query_chatbot',
                nonce: firestarter.nonce,
                query: query,
                namespace: currentNamespace
            },
            success: function(response) {
                $('#query-loading').hide();

                if (response.success) {
                    const answer = response.data.answer;
                    const sources = response.data.sources || [];

                    let sourcesHtml = '';
                    if (sources.length > 0) {
                        sourcesHtml = '<div class="chat-sources"><strong>Fuentes:</strong><ul>';
                        sources.forEach(function(source) {
                            sourcesHtml += `<li><a href="${source.url}" target="_blank">${source.title}</a></li>`;
                        });
                        sourcesHtml += '</ul></div>';
                    }

                    $('#chat-messages').append(`
                        <div class="chat-message bot-message">
                            <strong>Chatbot:</strong> ${answer}
                            ${sourcesHtml}
                        </div>
                    `);

                    // Scroll al final
                    $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                } else {
                    $('#chat-messages').append(`
                        <div class="chat-message error-message">
                            <strong>Error:</strong> ${response.data.message || 'Error desconocido'}
                        </div>
                    `);
                }
            },
            error: function() {
                $('#query-loading').hide();
                $('#chat-messages').append(`
                    <div class="chat-message error-message">
                        <strong>Error:</strong> Error de conexión
                    </div>
                `);
            }
        });
    }

    $('#send-query-btn').on('click', sendQuery);
    $('#chat-query-input').on('keypress', function(e) {
        if (e.which === 13) {
            sendQuery();
        }
    });

    // Eliminar chatbot
    $(document).on('click', '.delete-chatbot', function() {
        if (!confirm('¿Estás seguro de que quieres eliminar este chatbot?')) {
            return;
        }

        const namespace = $(this).data('namespace');
        const $card = $(this).closest('.firestarter-chatbot-card');

        $.ajax({
            url: firestarter.ajax_url,
            type: 'POST',
            data: {
                action: 'firestarter_delete_chatbot',
                nonce: firestarter.nonce,
                namespace: namespace
            },
            success: function(response) {
                if (response.success) {
                    $card.fadeOut(function() {
                        $(this).remove();
                        if ($('.firestarter-chatbot-card').length === 0) {
                            $('#chatbots-list').hide();
                            $('#no-chatbots').show();
                        }
                    });
                } else {
                    alert('Error al eliminar el chatbot: ' + (response.data.message || 'Error desconocido'));
                }
            },
            error: function() {
                alert('Error de conexión al eliminar el chatbot');
            }
        });
    });

    // Cargar chatbots al inicio
    loadChatbots();
});
</script>
