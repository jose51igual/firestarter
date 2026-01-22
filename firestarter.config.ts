import { groq } from '@ai-sdk/groq'
import { openai } from '@ai-sdk/openai'
import { anthropic } from '@ai-sdk/anthropic'
import { google } from '@ai-sdk/google'
import { Ratelimit } from '@upstash/ratelimit'
import { Redis } from '@upstash/redis'

// AI provider configuration
const AI_PROVIDERS = {
  google: {
    model: google('gemini-2.5-flash'),
    enabled: !!process.env.GOOGLE_GENERATIVE_AI_API_KEY,
  },
  groq: {
    model: groq('meta-llama/llama-4-scout-17b-16e-instruct'),
    enabled: !!process.env.GROQ_API_KEY,
  },
  openai: {
    model: openai('gpt-4o'),
    enabled: !!process.env.OPENAI_API_KEY,
  },
  anthropic: {
    model: anthropic('claude-3-5-sonnet-20241022'),
    enabled: !!process.env.ANTHROPIC_API_KEY,
  },
}

// Get the active AI provider
function getAIModel() {
  // Only check on server side
  if (typeof window !== 'undefined') {
    return null
  }
  // Priority: Gemini (más barato) > OpenAI > Anthropic > Groq
  if (AI_PROVIDERS.google.enabled) return AI_PROVIDERS.google.model
  if (AI_PROVIDERS.openai.enabled) return AI_PROVIDERS.openai.model
  if (AI_PROVIDERS.anthropic.enabled) return AI_PROVIDERS.anthropic.model
  if (AI_PROVIDERS.groq.enabled) return AI_PROVIDERS.groq.model
  throw new Error('No AI provider configured. Please set GOOGLE_GENERATIVE_AI_API_KEY, OPENAI_API_KEY, ANTHROPIC_API_KEY, or GROQ_API_KEY')
}

// Rate limiter factory
function createRateLimiter(identifier: string, requests = 50, window = '1 d') {
  if (typeof window !== 'undefined') {
    return null
  }
  if (!process.env.UPSTASH_REDIS_REST_URL || !process.env.UPSTASH_REDIS_REST_TOKEN) {
    return null
  }
  
  const redis = new Redis({
    url: process.env.UPSTASH_REDIS_REST_URL,
    token: process.env.UPSTASH_REDIS_REST_TOKEN,
  })
  
  return new Ratelimit({
    redis,
    limiter: Ratelimit.fixedWindow(requests, window),
    analytics: true,
    prefix: `firestarter:ratelimit:${identifier}`,
  })
}

const config = {
  app: {
    name: 'Firestarter',
    url: process.env.NEXT_PUBLIC_URL || 'http://localhost:3000',
    logoPath: '/firecrawl-logo-with-fire.png',
  },

  ai: {
    model: getAIModel(),
    temperature: 0.7,
    maxTokens: 2000,
    systemPrompt: `Eres un asistente virtual que representa a nuestra empresa. SIEMPRE responde en primera persona del plural (nosotros/nuestra/nuestros/tenemos/ofrecemos/estamos) como si fueras parte del equipo de la empresa.

REGLAS CRÍTICAS:
- NUNCA hables de la empresa en tercera persona ("ellos", "la empresa", "esa empresa")
- SIEMPRE usa "nosotros", "nuestra empresa", "nuestro equipo", "ofrecemos", "tenemos"
- Cuando te pregunten sobre contacto, di "puedes contactarnos" o "contáctanos", NUNCA "puedes contactar con ellos"
- Habla como si TÚ fueras parte de la empresa
- Responde SOLO usando el contexto proporcionado. No uses otro conocimiento.
- Si saludan o hacen conversación casual, responde educadamente sin mencionar el sitio web.
- NUNCA menciones explícitamente "el contexto", "la información proporcionada", o "según los datos". Responde de forma natural.
- Si no tienes información sobre algo específico, ofrece ayuda con temas relacionados que SÍ conoces o invita a contactar para más información.
- NUNCA digas frases como "el contexto no especifica", "no tengo información en el contexto", "según el contexto". Sé directo y natural.
- SIEMPRE responde en el mismo idioma en que se te pregunte.

FORMATO DE ENLACES:
- SIEMPRE formatea los enlaces como HTML: <a href="URL">texto descriptivo</a>
- NUNCA pongas URLs sueltas como texto plano
- Ejemplos correctos:
  · "Puedes <a href="https://ejemplo.com/contacto">contactarnos aquí</a>"
  · "Visita nuestra <a href="https://ejemplo.com/tienda">tienda online</a>"
  · "Consulta nuestros <a href="https://ejemplo.com/servicios">servicios</a>"

EJEMPLOS DE RESPUESTAS CORRECTAS:
- "Puedes <a href='URL'>contactarnos aquí</a>"
- "Nuestro horario de atención es..."
- "Ofrecemos los siguientes servicios..."
- "Estamos ubicados en..."
- "Tenemos disponibles estos productos..."
- "No tenemos información específica sobre ese servicio en este momento, pero podemos ayudarte con [otros temas]. También puedes <a href='URL'>contactarnos directamente</a> para consultas específicas."
- "Nuestra experiencia principal está en [áreas conocidas]. Para consultas sobre [tema específico], te recomendamos <a href='URL'>contactarnos</a> y con gusto te atenderemos."

EJEMPLOS INCORRECTOS (NUNCA usar):
- "Puedes contactar con ellos en..."
- "La empresa está ubicada en..."
- "Ellos ofrecen servicios de..."
- "El contexto no especifica..."
- "Según la información proporcionada..."
- "No tengo información en el contexto sobre..."
- URLs sin formatear: "Contacto: https://ejemplo.com/contacto"`,
    providers: AI_PROVIDERS,
  },

  crawling: {
    defaultLimit: 10,
    maxLimit: 100,
    minLimit: 10,
    limitOptions: [10, 25, 50, 100],
    scrapeTimeout: 15000,
    cacheMaxAge: 604800,
  },

  search: {
    maxResults: 100,
    maxContextDocs: 10,
    maxContextLength: 1500,
    maxSourcesDisplay: 20,
    snippetLength: 200,
  },

  storage: {
    maxIndexes: 50,
    localStorageKey: 'firestarter_indexes',
    redisPrefix: {
      indexes: 'firestarter:indexes',
      index: 'firestarter:index:',
    },
  },

  rateLimits: {
    create: createRateLimiter('create', 20, '1 d'),
    query: createRateLimiter('query', 100, '1 h'),
    scrape: createRateLimiter('scrape', 50, '1 d'),
  },

  features: {
    enableCreation: process.env.DISABLE_CHATBOT_CREATION !== 'true',
    enableRedis: !!(process.env.UPSTASH_REDIS_REST_URL && process.env.UPSTASH_REDIS_REST_TOKEN),
    enableSearch: !!(process.env.UPSTASH_SEARCH_REST_URL && process.env.UPSTASH_SEARCH_REST_TOKEN),
  },
}

export type Config = typeof config

// Client-safe config (no AI model initialization)
export const clientConfig = {
  app: config.app,
  crawling: config.crawling,
  search: config.search,
  storage: config.storage,
  features: config.features,
}

// Server-only config (includes AI model)
export const serverConfig = config

// Default export for backward compatibility
export { clientConfig as config }