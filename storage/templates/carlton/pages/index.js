import React, { Component } from 'react'
import Head from 'next/head'
import Header from '../components/Header.js';
import Footer from '../components/Footer.js';
import Mainbanner from '../components/Banner.js';
import Bookingform from '../components/Bookingform.js';
import Textblock from '../components/Textblock.js';
import Corefeature from '../components/Corefeature.js';
import Featureroom from '../components/Featureroom.js';
import Counter from '../components/Counter.js';
import Roomslider from '../components/Roomslider.js';
import Menuarea from '../components/Menuarea.js';
import Testimonial from '../components/Testimonials.js';
import Instagram from '../components/Instagram.js';
import Layout from '../components/Layout.js';
import axios from 'axios';

export default function Home({ siteData }) {
    return (
      <>
        <Head>
          <title>{siteData.title+' - Welcome'}</title>
          <meta name="description" content={siteData.meta_description} />
          <meta name="og:title" content={siteData.title+' - Welcome'} />
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
          {/*====== BANNER PART START ======*/}
          <Mainbanner siteData={siteData} />
          {/*====== BANNER PART ENDS ======*/}
          {/*====== BOOKING FORM START ======*/}
          <Bookingform siteData={siteData} />
          {/*====== BOOKING FORM END ======*/}
          {/*====== TEXT BLOCK START ======*/}
          <Textblock siteData={siteData}/>
          {/*====== TEXT BLOCK END ======*/}
          {/*====== CORE FEATURES START ======*/}
          {/*<Corefeature siteData={siteData}/>*/}
          {/*====== CORE FEATURES END ======*/}
          {/*====== ROOM SLIDER START ======*/}
          <Roomslider siteData={siteData} />
          {/*====== ROOM SLIDER END ======*/}
          {/*====== MENU PART START ======*/
          /*<Menuarea/>
          /*====== MENU PART END ======*/}
          {/*====== TESTIMONIAL SLIDER START ======*/}
          <Testimonial siteData={siteData}/>
          <Instagram siteData={siteData}/>
          {/*====== TESTIMONIAL SLIDER END ======*/}
          <Footer siteData={siteData}/>
        </Layout>
      </>
    );
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
