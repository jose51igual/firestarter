/**
 * Firestarter Chatbot Admin JavaScript
 */

(function($) {
    'use strict';

    // Utilidades comunes
    window.FirestarterAdmin = {
        /**
         * Mostrar notificación
         */
        showNotice: function(message, type = 'success') {
            const noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
            const notice = $(`
                <div class="notice ${noticeClass} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Descartar este aviso.</span>
                    </button>
                </div>
            `);

            $('.wrap').prepend(notice);

            // Auto-dismiss después de 5 segundos
            setTimeout(function() {
                notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);

            // Manual dismiss
            notice.find('.notice-dismiss').on('click', function() {
                notice.fadeOut(function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Validar URL
         */
        isValidUrl: function(string) {
            try {
                const url = new URL(string);
                return url.protocol === 'http:' || url.protocol === 'https:';
            } catch (_) {
                return false;
            }
        },

        /**
         * Formatear fecha
         */
        formatDate: function(dateString) {
            const date = new Date(dateString);
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return date.toLocaleDateString('es-ES', options);
        },

        /**
         * Truncar texto
         */
        truncate: function(str, length) {
            if (str.length <= length) return str;
            return str.substring(0, length) + '...';
        },

        /**
         * Hacer llamada AJAX genérica
         */
        ajaxCall: function(action, data, successCallback, errorCallback) {
            $.ajax({
                url: firestarter.ajax_url,
                type: 'POST',
                data: {
                    action: action,
                    nonce: firestarter.nonce,
                    ...data
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof successCallback === 'function') {
                            successCallback(response.data);
                        }
                    } else {
                        if (typeof errorCallback === 'function') {
                            errorCallback(response.data);
                        } else {
                            FirestarterAdmin.showNotice(
                                response.data.message || 'Error desconocido',
                                'error'
                            );
                        }
                    }
                },
                error: function(xhr, status, error) {
                    if (typeof errorCallback === 'function') {
                        errorCallback({ message: error });
                    } else {
                        FirestarterAdmin.showNotice(
                            'Error de conexión: ' + error,
                            'error'
                        );
                    }
                }
            });
        },

        /**
         * Verificar configuración de API
         */
        checkApiConfig: function(callback) {
            if (!firestarter.api_url) {
                FirestarterAdmin.showNotice(
                    'Por favor configura la URL de la API de Firestarter en la página de configuración.',
                    'error'
                );
                return false;
            }
            if (typeof callback === 'function') {
                callback();
            }
            return true;
        },

        /**
         * Copiar al portapapeles
         */
        copyToClipboard: function(text, successMessage = 'Copiado al portapapeles') {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    FirestarterAdmin.showNotice(successMessage, 'success');
                }).catch(function(err) {
                    console.error('Error al copiar:', err);
                    FirestarterAdmin.fallbackCopyToClipboard(text, successMessage);
                });
            } else {
                FirestarterAdmin.fallbackCopyToClipboard(text, successMessage);
            }
        },

        /**
         * Copiar al portapapeles (fallback para navegadores antiguos)
         */
        fallbackCopyToClipboard: function(text, successMessage) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    FirestarterAdmin.showNotice(successMessage, 'success');
                }
            } catch (err) {
                console.error('Error al copiar:', err);
            }

            document.body.removeChild(textArea);
        }
    };

    // Inicialización cuando el documento está listo
    $(document).ready(function() {
        // Añadir botones de copiar a elementos con clase 'copyable'
        $('.copyable').each(function() {
            const text = $(this).text();
            const $copyBtn = $('<button>', {
                class: 'button button-small copy-btn',
                text: 'Copiar',
                click: function(e) {
                    e.preventDefault();
                    FirestarterAdmin.copyToClipboard(text);
                }
            });
            $(this).after($copyBtn);
        });

        // Validación de formularios
        $('form[data-validate="url"]').on('submit', function(e) {
            const urlInput = $(this).find('input[type="url"]');
            const url = urlInput.val();

            if (!FirestarterAdmin.isValidUrl(url)) {
                e.preventDefault();
                FirestarterAdmin.showNotice('Por favor ingresa una URL válida', 'error');
                urlInput.focus();
                return false;
            }
        });

        // Confirmación para acciones destructivas
        $('[data-confirm]').on('click', function(e) {
            const message = $(this).data('confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });

})(jQuery);
