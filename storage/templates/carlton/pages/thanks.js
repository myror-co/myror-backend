import React, { Component, useState} from 'react'
import ReactDOM from "react-dom"
import Head from 'next/head'
import Link from 'next/link'
import Layout from '../components/Layout.js';
import Header from '../components/Header.js';
import Footer from '../components/Footer.js';
import axios from 'axios';

const fetchData = async () => await axios.get(process.env.NEXT_PUBLIC_API_BASE_URL+'/site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID)
  .then(res => ({
    error: false,
    siteData: res.data,
  }))
  .catch(() => ({
      error: true,
      siteData: null,
    }),
  );

export default function Thanks({siteData}) {
  
  return (
    <>
      <Head>
        <title>{'Booking Policy | '+siteData.title}</title>
        <meta name="og:title" content={siteData.title} />
        <meta name="description" content={siteData.meta_description} />
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
                  {process.env.NEXT_PUBLIC_STRIPE_ACCOUNT_ID !== 'undefined' || process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID != 'undefined'  ? 'Your booking request was sent!' : 'Thank you for your booking!' }
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
  const data = await fetchData()

  return {
    props: {
      siteData: data.siteData.data
    },
    revalidate: 1, // In seconds
  }
}
