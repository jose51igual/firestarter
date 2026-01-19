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
    // Limitar el contenido a un tamaño manejable
    const maxContentLength = 5000
    const truncatedContent = content.substring(0, maxContentLength)
    
    // Crear texto buscable
    const searchableText = `namespace:${namespace} documento:${filename} ${truncatedContent}`.substring(0, 1000)

    const document = {
      id: doc_id,
      content: {
        text: searchableText,
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
        description: `Documento: ${filename}`,
        fullContent: truncatedContent,
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
