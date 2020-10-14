import Head from 'next/head'
import Link from 'next/link'

export const siteTitle = 'Listing Template'

export default function Layout({ children, listing }) {
  return (
    <div>
      <Head>
        //<link rel="icon" href="/favicon.ico" />
        <title>{listing.name}</title>
        <meta name="og:title" content={siteTitle} />
      </Head>
      <main>{children}</main>
    </div>
  )
}