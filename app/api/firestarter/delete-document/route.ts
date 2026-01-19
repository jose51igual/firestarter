import { NextRequest, NextResponse } from 'next/server'
import { searchIndex } from '@/lib/upstash-search'

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const { doc_id } = body

    if (!doc_id) {
      return NextResponse.json(
        { error: 'doc_id es requerido' },
        { status: 400 }
      )
    }

    // Eliminar el documento de Upstash
    await searchIndex.delete(doc_id)

    return NextResponse.json({
      success: true,
      message: 'Documento eliminado correctamente'
    })

  } catch (error) {
    console.error('Error deleting document:', error)
    return NextResponse.json(
      { error: 'Error al eliminar el documento' },
      { status: 500 }
    )
  }
}
