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

export default function Home({ siteData }) {
    return (
      <>
        <Head>
          <title>{'Welcome | '+siteData.title}</title>
          <meta name="og:title" content={siteData.title} />
          <meta name="description" content={siteData.meta_description} />
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
  const data = await fetchData()

  return {
    props: {
      siteData: data.siteData.data
    },
    revalidate: 1, // In seconds
  }
}
