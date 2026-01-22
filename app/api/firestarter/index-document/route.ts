import { NextRequest, NextResponse } from 'next/server'
import { searchIndex } from '@/lib/upstash-search'

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const { namespace, doc_id, filename, content } = body

    if (!namespace || !doc_id || !filename || !content) {
      return NextResponse.json(
        { error: 'Namespace, doc_id, filename y content son requeridos' },
        { status: 400 }
      )
    }

    // Preparar el documento para indexar
    // Limitar el contenido a un tamaño manejable para el almacenamiento completo
    const maxFullContentLength = 50000 // Aumentado significativamente
    const fullContent = content.substring(0, maxFullContentLength)
    
    // Crear texto buscable - DEBE contener suficiente contenido para búsqueda semántica
    // Upstash necesita más texto para hacer búsquedas efectivas
    const maxSearchableLength = 8000 // Mucho más texto para búsqueda semántica
    const searchableText = `${filename} ${fullContent}`.substring(0, maxSearchableLength)

    const document = {
      id: doc_id,
      content: {
        text: searchableText, // Texto para búsqueda semántica - AUMENTADO
        url: `document://${filename}`,
        title: filename
      },
      metadata: {
        namespace: namespace,
        title: filename,
        url: `document://${filename}`,
        sourceURL: `document://${filename}`,
        crawlDate: new Date().toISOString(),
        pageTitle: filename,
        description: `Documento subido: ${filename}`,
        fullContent: fullContent, // Contenido completo para el contexto - AUMENTADO
        isUploadedDocument: true
      }
    }

    // Indexar en Upstash
    await searchIndex.upsert([document])

    return NextResponse.json({
      success: true,
      message: 'Documento indexado correctamente',
      doc_id: doc_id
    })

  } catch (error) {
    console.error('Error indexing document:', error)
    return NextResponse.json(
      { error: 'Error al indexar el documento' },
      { status: 500 }
    )
  }
}
