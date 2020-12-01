import React, { Component } from 'react'
import Head from 'next/head'
import Header from '../components/Header.js';
import Footer from '../components/Footer.js';
import Mainbanner from '../components/Banner';
import Bookingform from '../components/Bookingform';
import Textblock from '../components/Textblock';
import Corefeature from '../components/Corefeature';
import Featureroom from '../components/Featureroom';
import Counter from '../components/Counter';
import Roomslider from '../components/Roomslider';
import Menuarea from '../components/Menuarea';
import Testimonial from '../components/Testimonials';
import Layout from '../components/Layout';
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
          <meta name="og:title" content={siteData.title} />\
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
          {/*====== TESTIMONIAL SLIDER END ======*/}
          <Footer siteData={siteData}/>
        </Layout>
      </>
    );
}

export async function getServerSideProps() {
  const data = await fetchData()

  return {
    props: {
      siteData: data.siteData.data
    }
  }
}
