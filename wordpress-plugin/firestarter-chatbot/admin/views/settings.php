<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap firestarter-settings">
    <h1>Configuración de Firestarter Chatbot</h1>

    <div class="firestarter-card">
        <form method="post" action="">
            <?php wp_nonce_field('firestarter_settings_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="firestarter_api_url">URL de Firestarter API</label>
                    </th>
                    <td>
                        <input
                            type="url"
                            name="firestarter_api_url"
                            id="firestarter_api_url"
                            value="<?php echo esc_attr(get_option('firestarter_api_url', '')); ?>"
                            class="regular-text"
                            placeholder="https://your-firestarter-deployment.vercel.app"
                        >
                        <p class="description">
                            Ingresa la URL base de tu instalación de Firestarter (por ejemplo: https://your-deployment.vercel.app)
                        </p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input
                    type="submit"
                    name="firestarter_save_settings"
                    id="submit"
                    class="button button-primary"
                    value="Guardar Configuración"
                >
            </p>
        </form>
    </div>

    <div class="firestarter-info">
        <h2>Acerca de Firestarter</h2>
        <p>
            Firestarter es una plataforma que permite crear chatbots AI para cualquier sitio web utilizando
            tecnología RAG (Retrieval Augmented Generation).
        </p>
        <p>
            <strong>Para configurar este plugin necesitas:</strong>
        </p>
        <ul>
            <li>Una instalación de Firestarter (puede ser en Vercel, local, etc.)</li>
            <li>La URL base de tu instalación de Firestarter</li>
            <li>Claves de API configuradas en tu instalación de Firestarter (Firecrawl, Upstash, OpenAI/Anthropic/Groq)</li>
        </ul>
        <p>
            <a href="https://github.com/mendableai/firestarter" target="_blank" class="button">
                Ver documentación en GitHub
            </a>
        </p>
    </div>
</div>
