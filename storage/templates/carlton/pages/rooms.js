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
import Layout from '../components/Layout.js';
import Preloader from '../components/Preloader.js';
import useSWR from "swr";
import {fetcher_api} from '../services/fetcher.js'

import axios from 'axios';

import Link from 'next/link'

export default function Rooms() {

  /*Fetch sites */
  const { data: {data: siteData} = {}, isValidating  } = useSWR('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID, fetcher_api)

  const roomsList = siteData && siteData.listings.map((item, i) => {
    return {
      slug: item.slug,
      photo: item.photos[0].picture,
      title: item.name,
      url: '/room-details',
      price: item.price+' '+item.currency,
      time: 'Night',
      bedrooms: item.bedrooms,
      bathrooms: item.bathrooms,
      capacity: item.capacity,
      btn: 'View Details',
      desc: item.summary,
    }
  });

  const loader = !siteData

  if (loader) return <><Preloader /></>

  return (  
    <>
        <Head>
          <title>{'Rooms | '+siteData.title}</title>
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
                <span>{siteData.title}</span>
                <h2 className="page-title">Our Rooms</h2>
                <ul className="breadcrumb-nav">
                  <li><Link href="/">Home</Link></li>
                  <li className="active">Rooms</li>
                </ul>
              </div>
            </div>
          </section>
          {/*====== BREADCRUMB PART END ======*/}
          {/*====== ROOM-LIST START ======*/}
          <section className="room-section room-list-style pt-120 pb-120">
            <div className="container">
              <div className="room-list-loop">
              {roomsList.map((item, i) => (
                <div key={i} className="room-box mb-30">
                  <div className="row no-gutters justify-content-center">
                    <div className="col-lg-5 col-md-10">
                      <div className="room-img-wrap" style={{cursor:'pointer'}}>
                        <Link href={'/rooms/'+item.slug}>
                        <div className="room-img" style={{backgroundImage: "url("+ item.photo + ")"}} />
                        </Link>
                      </div>
                    </div>
                    <div className="col-lg-7 col-md-10">
                      <div className="room-desc">
                        <div className="row align-items-center">
                          <div className="col-sm-8">
                            <ul className="icons">
                              <li><h5>{item.capacity} <i className="fal fa-user-friends" /></h5></li>
                              <li><h5>{item.bedrooms} <i className="fal fa-bed" /></h5></li>
                              <li><h5>{item.bathrooms} <i className="fal fa-shower" /></h5></li>
                              
                            </ul>
                            <h4 className="title"><Link href={'/rooms/'+item.slug}>{item.title}</Link></h4>
                            <p>
                            {item.desc}
                            </p>
                          </div>
                          <div className="col-sm-4">
                            <div className="text-sm-center">
                              <span className="price">{item.price}<span>/{item.time}</span> </span>
                              <Link href={'/rooms/'+item.slug}><a className="book-btn">{item.btn}</a></Link>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              ))}

              </div>
            </div>
          </section>
          {/*====== ROOM-LIST END ======*/}

          <Footer siteData={siteData}/>
        </Layout>
    </>
  );
}