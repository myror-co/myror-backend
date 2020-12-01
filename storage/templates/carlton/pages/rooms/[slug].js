import React, { Component } from 'react'
import Link from 'next/link'
import Head from 'next/head'
import Header from '../../components/Header.js';
import Footer from '../../components/Footer.js';
import Mainbanner from '../../components/Banner';
import Bookingform from '../../components/Bookingform';
import Textblock from '../../components/Textblock';
import Corefeature from '../../components/Corefeature';
import Featureroom from '../../components/Featureroom';
import Counter from '../../components/Counter';
import Roomslider from '../../components/Roomslider';
import Menuarea from '../../components/Menuarea';
import Testimonial from '../../components/Testimonials';
import Roomsidebar from '../../components/Roomsidebar';
import Layout from '../../components/Layout';
import PreLoader from '../../components/PreLoader';

import { useRouter } from 'next/router'
import useSWR from "swr";
import {fetcher_api} from '../../services/fetcher.js'
import airbnb_superhost from '../../assets/img/airbnb_superhost.png';

import axios from 'axios';


export default function Room({query}) {

  const router = useRouter()
  const { slug } = router.query;

  /*Fetch sites */
  const { data: {data: siteData} = {}, isValidating  } = useSWR('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID, fetcher_api)

  const loader = !siteData

  if (loader) return <><PreLoader /></>

  if(siteData){

    var key = null;
    var len;

    for( var i = 0, len = siteData.listings.length; i < len; i++ ) {
        if( siteData.listings[i].slug === slug ) {
            key = i;
            break;
        }
    }
  }

  const roomData = siteData.listings[`${key}`]

  return (
  <>
    <Head>
      <title>{'Rooms Details | '+siteData.title}</title>
      <meta name="og:title" content={siteData.title} />\
    </Head>

    <Layout siteData={siteData}>
      <Header siteData={siteData} />
     
      {/*====== BREADCRUMB PART START ======*/}
      <section className="breadcrumb-area" style={{backgroundImage: `url(${roomData.picture_xl})`}}>
        <div className="container">
          <div className="breadcrumb-text">
            <span>The ultimate luxury</span>
            <h2 className="page-title">Room Details</h2>
            <ul className="breadcrumb-nav">
              <li><Link href="/">Home</Link></li>
              <li className="active">Rooms</li>
            </ul>
          </div>
        </div>
      </section>
      {/*====== BREADCRUMB PART END ======*/}
      {/*====== ROOM-DETAILS START ======*/}
      <section className="room-details pt-120 pb-90">
        <div className="container">
          <div className="row">
            {/* details */}
            <div className="col-lg-8">
              <div className="deatils-box">
                <div className="title-wrap">
                  <div className="title">
                    <div className="room-cat">{roomData.room_type}</div>
                    <h2>{roomData.name}</h2>
                    <ul className="icons" style={{display:'inline-block'}}>
                      <li className="m-2" style={{display:'inline-block'}}><h5>{roomData.capacity} <i className="fal fa-user-friends" /></h5></li>
                      <li className="m-2" style={{display:'inline-block'}}><h5>{roomData.bedrooms} <i className="fal fa-bed" /></h5></li>
                      <li className="m-2" style={{display:'inline-block'}}><h5>{roomData.bathrooms} <i className="fal fa-shower" /></h5></li>
                    </ul>
                  </div>
                  <div className="price">
                    {roomData.price+' '+roomData.currency}<span>/Night</span>
                  </div>
                </div>
                <div className="thumb">
                  <img src={roomData.picture_xl} alt="" />
                </div>
                <div className="cancellation-box clearfix mb-60">
                  <h3 className="subtitle">About</h3>
                  <p>
                    {roomData.description}
                  </p>
                </div>

                <div className="cancellation-box clearfix mb-60">
                  <h3 className="subtitle">The Neighborhood</h3>
                  <p>
                    {roomData.neighborhood}
                  </p>
                  <p className="mt-4"><a href={`https://www.google.com/maps/@${roomData.lat},${roomData.lng},20z`} target="_blank" className="main-btn btn-filled">View location on maps</a></p>
                </div>

                <div className="room-fearures clearfix mt-60 mb-60">
                  <h3 className="subtitle">Amenities</h3>
                  <ul className="room-fearures-list">
                    {roomData.amenities.map((item, i) => {
                        switch(item) {
                          case 'Wifi':
                            return <li><i className="fal fa-wifi" />WiFi</li>
                          break;
                          case 'TV':
                            return <li><i className="fal fa-tv" />TV</li>
                          break;
                          case 'Kitchen':
                            return <li><i className="fal fa-knife-kitchen" />Kitchen</li>
                          break;
                          case 'Smoking allowed':
                            return <li><i className="fal fa-smoking" />Smoking allowed</li>
                          break;
                          case 'Breakfast':
                            return <li><i className="fal fa-croissant" />Breakfast</li>
                          break;
                          case 'Pets live on this property':
                            return <li><i className="fal fa-paw" />Pets live on this property</li>
                          break;
                          case 'Cat(s)':
                            return <li><i className="fal fa-cat" />Cat(s)</li>
                          break;
                          case 'First aid kit':
                            return <li><i className="fal fa-first-aid" />First aid kit</li>
                          break;
                          case 'Fire extinguisher':
                            return <li><i className="fal fa-fire-extinguisher" />Fire extinguisher</li>
                          break;
                          case 'Laptop-friendly workspace':
                            return <li><i className="fal fa-laptop" />Laptop-friendly workspace</li>
                          break;
                          case 'Luggage dropoff allowed':
                            return <li><i className="fal fa-suitcase" />Luggage dropoff allowed</li>
                          break;  
                          case 'Air conditioning':
                            return <li><i className="fal fa-wind" />Air conditioning</li>
                          break; 
                          case 'Iron':
                            return <li><i className="fal fa-tshirt" />Iron</li>
                          break; 
                          case 'Coffee maker':
                            return <li><i className="fal fa-coffee" />Coffee maker</li>
                          break;
                          case 'Free parking on premises' || 'Free street parking':
                            return <li><i className="fal fa-car" />Free parking</li>
                          break; 
                          case 'Dishes and silverware':
                            return <li><i className="fal fa-utensils" />Dishes and silverware</li>
                          break;                                                                                                                               
                          default:
                            return <li><i className="fal fa-check" />{item}</li>
                        }
                      }
                    )}
                  </ul>
                </div>
                <div className="room-rules clearfix mb-60">
                  <h3 className="subtitle">House Rules</h3>
                  <ul className="room-rules-list">
                    { roomData.rules.structured_house_rules.map((item, i) => (
                        <li>{item}</li>
                      ))
                    }
                  </ul>
                </div>
                <h3 className="subtitle">Our hosts</h3>
                <div className="testimonials mt-60 mb-20">
                  <div className="row">
                    <div className="col-lg-6 col-sm-6">
                      <div className="testimonial-box">
                        <div className="client-img">
                          <img src={roomData.user.has_profile_pic ? roomData.user.picture_url : ''} alt="" />
                          <span className="check"><i className="fal fa-check" /></span>
                        </div>
                        <h3>{roomData.user.first_name}</h3>
                        <p className="clinet-post">Host</p>
                        {roomData.user.is_superhost && <img width="200px" src={airbnb_superhost} />}
                      </div>
                    </div>
                    { roomData.hosts.filter(function(host){
                          if(host.id != roomData.user.id) return 
                      })
                      .map((item, i) => (
                          <div className="col-lg-6 col-sm-6">
                            <div className="testimonial-box">
                              <div className="client-img">
                                <img src={item.has_profile_pic ? item.picture_url : ''} alt="" />
                                <span className="check"><i className="fal fa-check" /></span>
                              </div>
                              <h3>{item.first_name}</h3>
                              <span className="clinet-post">Co-Host</span>
                            </div>
                          </div>                          
                      ))
                    }
                  </div>
                </div>
              </div>
            </div>
            {/* form */}
            <div className="col-lg-4">
              <Roomsidebar roomId={roomData.id}/>
            </div>
          </div>
        </div>
      </section>
      {/*====== ROOM-DETAILS END ======*/}

      <Footer siteData={siteData}/>
    </Layout>
  </>
  );
}