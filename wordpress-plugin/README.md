# Firestarter Chatbot - Plugin de WordPress

Plugin de WordPress para integrar con [Firestarter](https://github.com/mendableai/firestarter), una plataforma que permite crear chatbots AI para cualquier sitio web utilizando tecnología RAG (Retrieval Augmented Generation).

## Características

- **Crear Chatbots**: Crea chatbots AI para cualquier sitio web directamente desde WordPress
- **Gestión de Chatbots**: Administra todos tus chatbots desde un panel centralizado
- **Consultas Interactivas**: Realiza consultas a tus chatbots directamente desde la interfaz de administración
- **Integración Completa**: Se conecta con tu instalación de Firestarter mediante API REST

## Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior
- Una instalación de Firestarter (puede ser local, en Vercel, etc.)
- Claves de API configuradas en Firestarter:
  - Firecrawl API Key
  - Upstash Search credentials
  - Al menos un proveedor de LLM (OpenAI, Anthropic, o Groq)

## Instalación

1. **Descargar el Plugin**
   - Descarga la carpeta `firestarter-chatbot` de este repositorio

2. **Instalar en WordPress**
   - Copia la carpeta `firestarter-chatbot` en el directorio `wp-content/plugins/` de tu instalación de WordPress
   - O comprime la carpeta en un archivo ZIP y súbela desde el panel de administración de WordPress

3. **Activar el Plugin**
   - Ve a "Plugins" en el panel de administración de WordPress
   - Busca "Firestarter Chatbot" y haz clic en "Activar"

4. **Configurar el Plugin**
   - Ve a "Firestarter" > "Configuración" en el menú de administración
   - Ingresa la URL base de tu instalación de Firestarter
   - Guarda los cambios

## Configuración de Firestarter

Si aún no tienes una instalación de Firestarter, sigue estos pasos:

### Opción 1: Deploy en Vercel (Recomendado)

1. Ve al [repositorio de Firestarter](https://github.com/mendableai/firestarter)
2. Haz clic en el botón "Deploy with Vercel"
3. Configura las siguientes variables de entorno:
   ```
   FIRECRAWL_API_KEY=tu_clave_de_firecrawl
   UPSTASH_SEARCH_REST_URL=tu_url_de_upstash
   UPSTASH_SEARCH_REST_TOKEN=tu_token_de_upstash
   OPENAI_API_KEY=tu_clave_de_openai
   ```
4. Completa el deploy
5. Copia la URL de tu deployment (ej: `https://your-app.vercel.app`)

### Opción 2: Instalación Local

1. Clona el repositorio:
   ```bash
   git clone https://github.com/mendableai/firestarter.git
   cd firestarter
   ```

2. Instala las dependencias:
   ```bash
   npm install
   ```

3. Crea un archivo `.env.local` con tus claves de API:
   ```
   FIRECRAWL_API_KEY=tu_clave_de_firecrawl
   UPSTASH_SEARCH_REST_URL=tu_url_de_upstash
   UPSTASH_SEARCH_REST_TOKEN=tu_token_de_upstash
   OPENAI_API_KEY=tu_clave_de_openai
   ```

4. Inicia el servidor de desarrollo:
   ```bash
   npm run dev
   ```

5. Tu instalación estará disponible en `http://localhost:3000`

### Obtener Claves de API

| Servicio | URL | Descripción |
|----------|-----|-------------|
| Firecrawl | [firecrawl.dev/app/api-keys](https://www.firecrawl.dev/app/api-keys) | Web scraping y agregación de contenido |
| Upstash | [console.upstash.com](https://console.upstash.com) | Base de datos vectorial para búsqueda semántica |
| OpenAI | [platform.openai.com/api-keys](https://platform.openai.com/api-keys) | Proveedor de modelos de lenguaje |

## Uso del Plugin

### 1. Crear un Chatbot

1. Ve a "Firestarter" > "Crear Chatbot" en el menú de administración
2. Ingresa la URL del sitio web que deseas indexar
3. Selecciona el límite de páginas a crawlear (10, 25, 50 o 100)
4. Haz clic en "Crear Chatbot"
5. Espera a que el proceso de crawling e indexación termine (puede tomar varios minutos)

### 2. Ver y Gestionar Chatbots

1. Ve a "Firestarter" > "Mis Chatbots"
2. Verás una lista de todos los chatbots que has creado
3. Para cada chatbot puedes:
   - Ver información (URL, páginas crawleadas, fecha de creación)
   - Hacer consultas interactivas
   - Eliminar el chatbot

### 3. Consultar un Chatbot

1. En la página "Mis Chatbots", haz clic en "Consultar" en el chatbot que desees
2. Se abrirá un modal de chat
3. Escribe tu pregunta en el campo de texto
4. Presiona Enter o haz clic en "Enviar"
5. El chatbot responderá basándose en el contenido indexado del sitio web
6. Las fuentes utilizadas para la respuesta se mostrarán debajo de cada respuesta

### 4. Dashboard

El dashboard proporciona:
- Acceso rápido a crear nuevos chatbots
- Estadísticas sobre tus chatbots
- Enlaces a todas las funcionalidades del plugin

## Arquitectura

### Endpoints de Firestarter Utilizados

El plugin se comunica con los siguientes endpoints de Firestarter:

1. **POST /api/firestarter/create**
   - Crea un nuevo chatbot crawleando un sitio web
   - Body: `{ url, limit }`

2. **POST /api/firestarter/query**
   - Realiza consultas al chatbot
   - Body: `{ query, namespace, stream: false }`

3. **GET /api/indexes**
   - Obtiene la lista de chatbots/índices creados

4. **DELETE /api/indexes?namespace=xxx**
   - Elimina un chatbot específico

### Estructura del Plugin

```
firestarter-chatbot/
├── firestarter-chatbot.php          # Archivo principal del plugin
├── admin/
│   ├── class-firestarter-admin.php  # Clase de administración
│   └── views/                       # Vistas de las páginas de admin
│       ├── dashboard.php
│       ├── create.php
│       ├── chatbots.php
│       └── settings.php
└── assets/
    ├── css/
    │   └── admin.css                # Estilos del admin
    └── js/
        └── admin.js                 # JavaScript del admin
```

## Tecnologías

- **WordPress Plugin API**: Framework de plugins de WordPress
- **WordPress AJAX API**: Para comunicación asíncrona
- **jQuery**: Manipulación del DOM e interacciones
- **Firestarter API**: Backend para crawling y RAG

## Características de Firestarter

El chatbot creado por Firestarter utiliza:
- **Firecrawl**: Para crawlear y extraer contenido limpio de sitios web
- **Upstash Search**: Base de datos vectorial para búsqueda semántica
- **RAG (Retrieval Augmented Generation)**: Para respuestas contextuales precisas
- **LLM Providers**: OpenAI, Anthropic o Groq para generación de respuestas

## Solución de Problemas

### El plugin no se conecta a Firestarter

- Verifica que la URL de la API esté configurada correctamente
- Asegúrate de que tu instalación de Firestarter esté funcionando
- Verifica que no haya problemas de CORS
- Revisa los logs de WordPress y Firestarter para errores

### El crawling falla

- Verifica que tu Firecrawl API key sea válida y tenga créditos disponibles
- Asegúrate de que el sitio web a crawlear sea accesible públicamente
- Reduce el límite de páginas si el sitio es muy grande

### Las consultas no funcionan

- Verifica que el chatbot se haya creado correctamente
- Asegúrate de que tu LLM provider (OpenAI/Anthropic/Groq) tenga una API key válida
- Verifica la configuración de Upstash Search

### Errores de timeout

- Los procesos de crawling pueden tomar tiempo. Considera:
  - Reducir el límite de páginas
  - Aumentar el timeout en la configuración de PHP
  - Crawlear el sitio directamente desde Firestarter y luego usar el plugin solo para consultas

## Licencia

MIT License - Compatible con la licencia de Firestarter

## Soporte

Para problemas y preguntas:
- Sobre el plugin: Abre un issue en este repositorio
- Sobre Firestarter: [github.com/mendableai/firestarter](https://github.com/mendableai/firestarter)

## Contribuir

Las contribuciones son bienvenidas. Por favor:
1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Créditos

- Plugin desarrollado para integrar con [Firestarter](https://github.com/mendableai/firestarter)
- Firestarter es un proyecto de [Mendable.ai](https://mendable.ai)
