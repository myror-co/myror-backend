import Head from 'next/head'
import Link from 'next/link'

export const siteTitle = 'Listing Template'

export default function Layout({ children, siteData }) {
  return (
    <div>
      <Head>
        //<link rel="icon" href="/favicon.ico" />
        <meta name='description' content={siteData.meta_description} />
      </Head>
      <main id="page">{children}</main>
    </div>
  )
}