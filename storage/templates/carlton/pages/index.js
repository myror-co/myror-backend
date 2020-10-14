import React, { Component } from 'react'
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

const fetchData = async () => await axios.get(process.env.API_BASE_URL+'/site/'+process.env.WEBSITE_API_ID)
  .then(res => ({
    error: false,
    listing: res.data,
  }))
  .catch(() => ({
      error: true,
      listing: null,
    }),
  );

export default function Home({ homeData }) {
    return (
      <Layout listing={homeData}>
        <Header listing={homeData} />
        {/*====== BANNER PART START ======*/}
        <Mainbanner listing={homeData} />
        {/*====== BANNER PART ENDS ======*/}
        {/*====== BOOKING FORM START ======*/}
        <Bookingform/>
        {/*====== BOOKING FORM END ======*/}
        {/*====== TEXT BLOCK START ======*/}
        <Textblock listing={homeData}/>
        {/*====== TEXT BLOCK END ======*/}
        {/*====== CORE FEATURES START ======*/}
        <Corefeature listing={homeData}/>
        {/*====== CORE FEATURES END ======*/}
        {/*====== ROOM SLIDER START ======*/}
        <Roomslider listing={homeData} />
        {/*====== ROOM SLIDER END ======*/}
        {/*====== MENU PART START ======*/}
        <Menuarea/>
        {/*====== MENU PART END ======*/}
        {/*====== TESTIMONIAL SLIDER START ======*/}
        <Testimonial listing={homeData}/>
        {/*====== TESTIMONIAL SLIDER END ======*/}
        <Footer listing={homeData}/>
      </Layout>
    );
}

export async function getServerSideProps() {
  const data = await fetchData()

  return {
    props: {
      homeData: data.listing.data
    }
  }
}
