import React, { Component, useState} from 'react'
import ReactDOM from "react-dom"
import Head from 'next/head'
import Link from 'next/link'
import Layout from '../components/Layout.js';
import Header from '../components/Header.js';
import Footer from '../components/Footer.js';
import axios from 'axios';

export default function Thanks({ siteData }) {

  return (
    <>
      <Head>
        <title>{'Thanks | '+siteData.title}</title>
        <meta name="description" content={siteData.meta_description} />
        <meta name="og:title" content={'Thanks | '+siteData.title} />
        <meta property="og:type" content="website" />
        <meta property="og:description" content={siteData.meta_description} />
        <meta property="og:url" content={'https://'+process.env.NEXT_PUBLIC_SITE_URL} />
        <meta property="og:site_name" content={siteData.title} />
        <meta property="og:image" content={siteData.icon ? siteData.icon :"/myror_m.png"} />
        <link rel="icon" type="image/png" sizes="32x32" href={siteData.icon ? siteData.icon :"/myror_m.png"} />
        <link rel="icon" type="image/png" sizes="16x16" href={siteData.icon ? siteData.icon :"/myror_m.png"} />
        <link rel="apple-touch-icon" sizes="180x180" href={siteData.icon ? siteData.icon :"/myror_m.png"} />
      </Head>

      <Layout siteData={siteData}>
        <Header siteData={siteData} />
        {/*====== BREADCRUMB PART START ======*/}
        <section className="breadcrumb-area" style={{backgroundImage: `url(${siteData.main_picture})`}}>
          <div className="container">
            <div className="breadcrumb-text">
              {/*<span>{siteData.title}</span>*/}
              <h1 className="page-title">
                Booking
              </h1>
            </div>
          </div>
        </section>
        {/*====== BREADCRUMB PART END ======*/}


        {/*====== CONTACT PART START ======*/}

        <section className="contact-part pt-50 pb-50 ">
          <div className="container">
            <div className="pt-50 pb-50" style={{background:`white`}}>
              <div className="text-center">
                <h2 style={{color:'#bead8e'}}>
                  {process.env.NEXT_PUBLIC_STRIPE_ACCOUNT_ID !== 'undefined' || process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID !== 'undefined'  ? 'Your booking request was sent!' : 'Thank you for your booking!' }
                </h2>
                <h3 className="mt-5">
                  {process.env.NEXT_PUBLIC_STRIPE_ACCOUNT_ID == 'undefined' && process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID == 'undefined' ? 
                    'We will get back to you as soon as possible.' :
                    'You will receive your receipt by email in a few minutes.'
                  }
                </h3>
                <h5 className="mt-5"><Link href="/">Go back to Home page</Link></h5>
              </div>
            </div>
          </div>
        </section>

        {/*====== CONTACT PART END ======*/}
        <Footer siteData={siteData} />
      </Layout>
    </>
  )
}

export async function getStaticProps() {

  const res = await fetch(process.env.NEXT_PUBLIC_API_BASE_URL+'/site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID)
  const siteData = await res.json()

  return {
    props: {
      siteData: siteData.data
    },
    revalidate: 60, // In seconds
  }
}
