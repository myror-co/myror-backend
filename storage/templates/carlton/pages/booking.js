import React, { Component, useState, useEffect } from 'react'
import Head from 'next/head'
import Link from 'next/link'
import { useForm } from "react-hook-form";
import { useRouter } from 'next/router'
import Layout from '../components/Layout.js';
import Header from '../components/Header.js';
import Footer from '../components/Footer.js';
import Preloader from '../components/Preloader.js';
import Bookingform from '../components/Bookingform.js';
import apiClient from '../services/api.js';
import {fetcher_api} from '../services/fetcher.js'
import useSWR from "swr";

export default function Booking({query}) {  

  const router = useRouter()
  const { room, start, end } = router.query;

  /*Fetch sites */
  const { data: {data: siteData} = {}, isValidating  } = useSWR('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID, fetcher_api)
  const { data: {not_available: calendar} = {}} = useSWR(room ? 'site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID+'/rooms/'+room+'/calendar' : null, fetcher_api)
  const [isAvailable, setIsAvailable] = useState(false)
  const [checkingAvailable, setCheckingAvailable] = useState(null)
  const { register, handleSubmit } = useForm()
  const [number1, setNumber1] = useState(Math.floor(Math.random() * 10))
  const [number2, setNumber2] = useState(Math.floor(Math.random() * 10))
  const [resultChallenge, SetResultChallenge] = useState(false)
  const [errorForm, setErrorForm] = useState(false)
  const [requestSuccess, setRequestSuccess] = useState(false)
  const [requestFailure, setRequestFailure] = useState(false)
  const [sendingRequest, setSendingRequest] = useState(false)

  useEffect(() => {

    if(room && start && end)
    {
      setNumber1(Math.floor(Math.random() * 10))
      setNumber2(Math.floor(Math.random() * 10))
      checkAvailable(room, start, end)
    }

  }, [room, start, end]);

  const checkAvailable = (roomId, startDate, endDate) => {
    setRequestSuccess(false)
    setCheckingAvailable(true)

    apiClient.get('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID+'/rooms/'+room+'/calendar/available?start='+startDate+'&end='+endDate)
    .then(result => {
      setIsAvailable(result.data.is_available)
      setCheckingAvailable(false)
    })
    .catch(e =>{
      setCheckingAvailable(false)
    });
  }

  const sendBookingRequest = (data) => {

    //Challenge sender
    if(number1+number2 == data.resultChallenge)
    {
      //Honeybot check
      if(!data.address)
      {
        setSendingRequest(true)

        apiClient.post('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID+'/rooms/'+room+'/requestBooking', 
        {
            email: data.email,
            first_name: data.first_name,
            last_name: data.last_name,
            phone: data.phone,
            guests: data.guests,
            start: start,
            end: end,
            message: data.message,
        })
        .then(result => {
          setRequestSuccess(true)
          setSendingRequest(false)
        })
        .catch(e =>{
          setRequestFailure(true)
          setSendingRequest(false)
        });
      }
    }
    else{
      setErrorForm(true)
    }
  };

  const loader = !siteData

  if (loader) return <><Preloader /></>

  if(siteData){

    var key = null;
    var len;

    for( var i = 0, len = siteData.listings.length; i < len; i++ ) {
        if( siteData.listings[i].id == room ) {
            key = i;
            break;
        }
    }
  }

  const roomData = siteData.listings[`${key}`]

  return (
    <>
      <Head>
        <title>{'Booking Requests | '+siteData.title}</title>
        <meta name="og:title" content={siteData.title} />
      </Head>

      <Layout siteData={siteData}>
        <Header siteData={siteData} />
        {/*====== BREADCRUMB PART START ======*/}
        <section className="breadcrumb-area" style={{backgroundImage: `url(${siteData.main_picture})`}}>
          <div className="container">
            <div className="breadcrumb-text">
              <span>{siteData.title}</span>
              <h2 className="page-title">Booking Request</h2>
              <ul className="breadcrumb-nav">
                <li><Link href="/">Home</Link></li>
                <li className="active">Booking Request</li>
              </ul>
            </div>
          </div>
        </section>
        {/*====== BREADCRUMB PART END ======*/}

        <Bookingform 
          siteData={siteData} 
          calendar={calendar}
          startDateInit={start}
          endDateInit={end}
          selectedListingIdInit={room}
          checkingAvailibility={checkingAvailable}
        />

        {/*====== CONTACT PART START ======*/}

        <section className="contact-part pt-50 pb-50">
          <div className="container">
            {/* Contact Form */}
            <div className="contact-form">

              { checkingAvailable ? (
                  <div className="text-center">
                    <h2 style={{color:'#bead8e'}}><i className="far fa-spinner fa-spin" /></h2>
                    <h2 style={{color:'#bead8e'}}>Checking availibility</h2>
                  </div>
                ) : 
                (
                  isAvailable ? (
                    <>
                      <div className="mb-5">
                        <h3 className="subtitle mb-3">The following dates are available</h3>
                        <p className="booking-request-details">Room name: <strong>{roomData.name}</strong></p>
                        <p className="booking-request-details">Check-in: <strong>{start}</strong></p>
                        <p className="booking-request-details">Check-out: <strong>{end}</strong></p>
                      </div>

                      <h3 className="subtitle mb-5">Fill in our booking form to send us a request</h3>
                      <form onSubmit={handleSubmit(sendBookingRequest)}>
                        <div className="row">
                          <div className="col-md-6">
                            <div className="input-group mb-30">
                              <span className="icon"><i className="far fa-user" /></span>
                              <input type="text" placeholder="Your first name*" name="first_name" ref={register} required />
                            </div>
                          </div>
                          <div className="col-md-6">
                            <div className="input-group mb-30">
                              <span className="icon"><i className="far fa-user" /></span>
                              <input type="text" placeholder="Your last name*" name="last_name" ref={register} required />
                            </div>
                          </div>
                          <div className="col-md-4">
                            <div className="input-group mb-30">
                              <span className="icon"><i className="far fa-envelope" /></span>
                              <input type="email" placeholder="Enter email*" name="email" ref={register} required />
                            </div>
                          </div>
                          <div className="col-md-4">
                            <div className="input-group mb-30">
                              <span className="icon"><i className="far fa-phone" /></span>
                              <input type="text" placeholder="Enter phone number" name="phone" ref={register}/>
                            </div>
                          </div>
                          <div className="col-md-4">
                            <div className="input-group mb-30">
                              <span className="icon"><i className="far fa-users" /></span>
                              <input type="number" placeholder="Number of guests*" min="0" max={roomData.capacity} name="guests" ref={register} required />
                            </div>
                          </div>
                          <div className="col-12">
                            <div className="input-group textarea mb-30">
                              <span className="icon"><i className="far fa-pen" /></span>
                              <textarea type="text" placeholder="Let us know if you have any special requests" name="message" ref={register} />
                            </div>
                          </div>
                          <div className="col-md-6">
                            <div className="input-group mb-30">
                              <label><h5>Kindly answer : {number1+' + '+number2+ ' = ?'}</h5></label>
                              <input type="number" placeholder="Type your answer*" name="resultChallenge" ref={register} required />
                            </div>
                          </div>
                          <input className="special-form-field" name="address" type="text" ref={register} />

                          {errorForm && <div className="text-center col-12 mb-5"><h4 className="text-danger">You gave a wrong result to the addition challenge</h4></div>}
                          {requestFailure && <div className="text-center col-12 mb-5"><h4 className="text-danger">An error happened. Please try sending your booking request again.</h4></div>}

                          {!requestSuccess ? (
                            <div className="col-12 text-center">
                              <button type={!sendingRequest && 'submit'} className="main-btn btn-filled">{sendingRequest ? <i className="far fa-spinner fa-spin" /> : 'Request Booking'}</button>
                            </div>
                          ) :
                          (
                            <div className="text-center col-12 mb-5"><h4 className="text-success">Thank you for your booking request! We will come back to you the soonest.</h4></div>
                          )}
                        </div>

                      </form>
                    </>
                    ) : 
                    (
                      <div className="text-center">
                        <h2 className="subtitle mb-5">This room is not available for your selected dates.</h2>
                        <h4 className="subtitle mb-5">You can choose another room or select different check-in and check-out dates</h4>
                      </div>
                    )
                )
              }

            </div>
          </div>
        </section>

        {/*====== CONTACT PART END ======*/}
        <Footer siteData={siteData} />
      </Layout>
    </>
  )
}