import React, { Component, useState, useEffect } from 'react'
import ReactDOM from "react-dom"
import { Collapse, Button, CardBody, Card } from 'reactstrap';
import Head from 'next/head'
import Link from 'next/link'
import { useForm } from "react-hook-form";
import { useRouter } from 'next/router'
import Layout from '../components/Layout.js';
import Header from '../components/Header.js';
import Footer from '../components/Footer.js';
import Preloader from '../components/Preloader.js';
import apiClient from '../services/api.js';
import {fetcher_api} from '../services/fetcher.js'
import useSWR from "swr";

export default function Policy() {  

  const router = useRouter()

  const { data: {data: siteData} = {}, isValidating  } = useSWR('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID, fetcher_api)
  const loader = !siteData

  if (loader) return <><Preloader /></>

  return (
    <>
      <Head>
        <title>{'Booking Policy | '+siteData.title}</title>
          <meta name="description" content={siteData.meta_description} />
          <meta name="og:title" content={'Booking Policy | '+siteData.title} />
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
                Booking Policy
              </h1>
            </div>
          </div>
        </section>
        {/*====== BREADCRUMB PART END ======*/}


        {/*====== CONTACT PART START ======*/}

        <section className="contact-part pt-50 pb-50 ">
          <div className="container">
            <div className="pt-20 pb-20 pl-30 pr-30" style={{background:`white`}}>
                <h3 className="subtitle mb-3 mt-5">Cancellation</h3>
                <p style={{whiteSpace: `pre-line`, textAlign: `justify`}}>{siteData.cancellation_policy}</p>
                <h3 className="subtitle mb-3 mt-5">Refund</h3>
                <p style={{whiteSpace: `pre-line`, textAlign: `justify`}}>{siteData.no_show_policy}</p>
                <h3 className="subtitle mb-3 mt-5">Deposit</h3>
                <p style={{whiteSpace: `pre-line`, textAlign: `justify`}}>{siteData.deposit_policy}</p>
                <h3 className="subtitle mb-3 mt-5">Other</h3>
                <p style={{whiteSpace: `pre-line`, textAlign: `justify`}}>{siteData.other_policy}</p>
            </div>
          </div>
        </section>

        {/*====== CONTACT PART END ======*/}
        <Footer siteData={siteData} />
      </Layout>
    </>
  )
}