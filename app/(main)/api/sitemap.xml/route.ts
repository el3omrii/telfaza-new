import { NextResponse } from 'next/server'

const SITEMAP_URL = 'https://admin.telfazalive.com/sitemap.xml'
const CACHE_DURATION = 86400 // 24 hours in seconds

export async function GET() {
  try {
    // Fetch the video sitemap from the backend
    const response = await fetch(SITEMAP_URL, {
      next: { revalidate: CACHE_DURATION },
      headers: { Accept: 'application/xml' }
    })

    if (!response.ok) {
      console.error(`Failed to fetch video sitemap: ${response.status} ${response.statusText}`)
      return new NextResponse('Failed to fetch video sitemap', { status: 502 })
    }

    // Get the XML content
    const xmlContent = await response.text()

    // Return the XML with appropriate headers
    return new NextResponse(xmlContent, {
      headers: {
        'Content-Type': 'application/xml',
        'Cache-Control': `public, max-age=${CACHE_DURATION}, stale-while-revalidate=${CACHE_DURATION}`
      }
    })

  } catch (error) {
    console.error('Error fetching video sitemap:', error)
    return new NextResponse('Internal Server Error', { status: 500 })
  }
}