import React, { Component, useState, useEffect } from 'react'
import { Collapse, Button, CardBody, Card, Table} from 'reactstrap';
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
import { PayPalButton } from "react-paypal-button-v2";
import {CardElement, useStripe, useElements} from '@stripe/react-stripe-js';
import moment from 'moment';

const CARD_ELEMENT_OPTIONS = {
  style: {
    base: {
      fontFamily: "Roboto, sans-serif",
      fontSmoothing: "antialiased",
      fontSize: '16px',
      fontWeight:`400`,
      lineHeight:`26px`,
      color: '#777777',
      "::placeholder": {
        color: "#8c8b8b",
      },
    },
    invalid: {
      color: "#fa755a",
      iconColor: "#fa755a",
    },
  },
};

export default function Booking({query}) {  

  const router = useRouter()
  const { room, start, end } = router.query;

  /*Fetch sites */
  const { data: {data: siteData} = {}, isValidating  } = useSWR('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID, fetcher_api)
  const { data: {not_available: calendar} = {}} = useSWR(room ? 'site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID+'/rooms/'+room+'/calendar' : null, fetcher_api)
  const [isAvailable, setIsAvailable] = useState(false)
  const [notAvailableMessage, setNotAvailableMessage] = useState(false)
  const [checkingAvailable, setCheckingAvailable] = useState(null)
  const [isLoading, setIsLoading] = useState(null)
  const { register, handleSubmit } = useForm()
  const [number1, setNumber1] = useState(Math.floor(Math.random() * 10))
  const [number2, setNumber2] = useState(Math.floor(Math.random() * 10))
  const [resultChallenge, SetResultChallenge] = useState(false)
  const [errorForm, setErrorForm] = useState(false)
  const [missingField, setMissingField] = useState(false)
  const [requestSuccess, setRequestSuccess] = useState(false)
  const [requestFailure, setRequestFailure] = useState(false)
  const [sendingRequest, setSendingRequest] = useState(false)
  const [paymentRequest, setPaymentRequest] = useState(false)
  const [nights, setNights] = useState(null)
  const [price, setPrice] = useState(null)
  const [roomData, setRoomData] = useState(null)
  const [clientSecret, setClientSecret] = useState(null)
  const [firstNameStripe, setFirstNameStripe] = useState(null)
  const [lastNameStripe, setLastNameStripe] = useState(null)
  const [phoneStripe, setPhoneStripe] = useState(null)
  const [emailStripe, setEmailStripe] = useState(null)
  const [guestStripe, setGuestStripe] = useState(null)
  const [isCollapseOpen, setIsCollapseOpen] = useState(false);
  const [disabledStripeButton, setDisabledStripeButton] = useState(true);

  const stripe = useStripe();
  const elements = useElements();

  useEffect(() => {

    if(siteData && room && start && end)
    {
      setNumber1(Math.floor(Math.random() * 10))
      setNumber2(Math.floor(Math.random() * 10))
      checkAvailable(room, start, end)
      setNights(moment(end).diff(moment(start), 'days'))
      setIsCollapseOpen(false)
      setErrorForm(false)
      setRequestSuccess(false)
      setRequestFailure(false)

      var key = null;
      var len;

      for( var i = 0, len = siteData.listings.length; i < len; i++ ) {
          if( siteData.listings[i].id == room ) {
              key = i;
              setRoomData(siteData.listings[`${key}`])
              break;
          }
      }
    }
  }, [room, start, end]);




  // useEffect(() => {
  //   if(roomData)
  //   {
  //     //Set price
  //     if(nights<7){setPrice(Math.round(nights*roomData.price))}
  //     if(nights>7 && nights<28){setPrice(Math.round(nights*roomData.price*roomData.weekly_factor))}
  //     if(nights>28){setPrice(Math.round(nights*roomData.price*roomData.monthly_factor))}
  //   }
  // }, [nights]);

  // useEffect(() => {
  //   if(roomData && roomData.pricing_type == "per_person")
  //   {
  //     setPrice(Math.round(nights*roomData.price*guestStripe))
  //     setIsCollapseOpen(false)
  //   }
  // }, [guestStripe]);





  const handleStripeChange = async (event) => {
    // Listen for changes in the CardElement
    // and display any errors as the customer types their card details
    setDisabledStripeButton(event.empty);
    setErrorForm(event.error ? event.error.message : "");
  };

  const checkAvailable = (roomId, startDate, endDate) => {
    setCheckingAvailable(true)
    setPrice(null)

    apiClient.get('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID+'/rooms/'+room+'/calendar/available?start='+startDate+'&end='+endDate)
    .then(result => {
      setNights(moment(end).diff(moment(start), 'days'))
      setIsAvailable(result.data.is_available)
      setCheckingAvailable(false)
      setFirstNameStripe(null)
      setLastNameStripe(null)
      setPhoneStripe(null)
      setEmailStripe(null)
      setGuestStripe(null)
    })
    .catch(e =>{
      setCheckingAvailable(false)
      setIsAvailable(false)

      if(e.response.status == 401)
      {
        setNotAvailableMessage(e.response.data.message)
      }
      else{
        setNotAvailableMessage(null)
      }
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
          setRequestFailure(false)
        })
        .catch(e =>{
          setRequestSuccess(false)
          setRequestFailure(true)
          setSendingRequest(false)
        });
      }
    }
    else{
      setErrorForm(true)
    }
  };

  const saveNewPaypalBooking = (data) => {

    setPaymentRequest(true)

    apiClient.post('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID+'/rooms/'+room+'/bookings/paypal', 
    {
      merchant_id : data.purchase_units[0].payee.merchant_id ? data.purchase_units[0].payee.merchant_id : '',
      email: emailStripe,
      phone: phoneStripe,
      first_name: firstNameStripe,
      last_name: lastNameStripe,
      guests: guestStripe,
      checkin: start,
      checkout: end,
      reference_id : data.id ? data.id : '',
      payment_id : data.id ? data.id : '',
      currency : data.purchase_units[0].amount.currency_code ? data.purchase_units[0].amount.currency_code : '',
      gross_amount : data.purchase_units[0].amount.value ? data.purchase_units[0].amount.value : '',
      net_amount : data.purchase_units[0].amount.value ? data.purchase_units[0].amount.value : '',
      payment_fee : 0,
      address_line1 : data.purchase_units[0].shipping.address.address_line_1 ? data.purchase_units[0].shipping.address.address_line_1 : '',
      address_line2 : '',
      address_city : data.purchase_units[0].shipping.address.admin_area_1 ? data.purchase_units[0].shipping.address.admin_area_1 : '',
      address_country : data.purchase_units[0].shipping.address.country_code ? data.purchase_units[0].shipping.address.country_code : '',
      address_state : '',
      address_postal_code : data.purchase_units[0].shipping.address.postal_code ? data.purchase_units[0].shipping.address.postal_code : '',
      paid_at : data.update_time ? data.update_time : ''
    })
    .then(result => {
      router.push('/thanks')
      setRequestSuccess(true)
    })
    .catch(e =>{
      setRequestFailure(true)
      setPaymentRequest(false)
    });
  };  

  const getPaymentIntent = () => {

    setSendingRequest(true)
    setErrorForm(null)
    setPrice(null)

    apiClient.post('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID+'/rooms/'+room+'/intent', 
    {
      nights : nights,
      guests: guestStripe
    })
    .then(result => {
      setSendingRequest(false)
      setClientSecret(result.data.client_secret)
      setPrice({
        'total':result.data.price,
        'discount': result.data.discount,
        'discount_type': result.data.discount_type,
        'cleaning': result.data.cleaning_price,
        'deposit': result.data.deposit_price
      })
      console.log(price)
    })
    .catch(e =>{
      setSendingRequest(false)
    });
  };  

  const saveBasicBookingInfo = (gateway) => {

    apiClient.post('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID+'/rooms/'+room+'/bookings/stripe', 
    {
      client_secret: clientSecret,
      gateway: gateway,
      email: emailStripe,
      first_name: firstNameStripe, 
      last_name: lastNameStripe, 
      phone: phoneStripe,
      guests: guestStripe,
      checkin: start,
      checkout: end
    })
    .then(result => {
      console.log(result.data.message)
    })
    .catch(e =>{
    });
  };  

  const payWithStripe = async (event) => {

    saveBasicBookingInfo('stripe') 

    // We don't want to let default form submission happen here,
    // which would refresh the page.
    event.preventDefault();

    if (!stripe || !elements) {
      // Stripe.js has not yet loaded.
      // Make sure to disable form submission until Stripe.js has loaded.
      return;
    }

    const result = await stripe.confirmCardPayment(clientSecret, {
      payment_method: {
        card: elements.getElement(CardElement),
        billing_details: {
          email: emailStripe, 
          name: firstNameStripe+' '+lastNameStripe,
          phone: phoneStripe
        }
      }
    });

    setPaymentRequest(true)
    
    if (result.error) {
      // Show error to your customer (e.g., insufficient funds)
      setRequestFailure(true)
      setPaymentRequest(false)
    } else {
      // The payment has been processed!
      if (result.paymentIntent.status === 'succeeded') {
        // Show a success message to your customer
        // There's a risk of the customer closing the window before callback
        // execution. Set up a webhook or plugin to listen for the
        // payment_intent.succeeded event that handles any business critical
        // post-payment actions.

        router.push('/thanks')

        setRequestSuccess(true)
      }
    }
  };

  const loader = !siteData

  if (loader) return <><Preloader /></>

  return (
    <>
      <Head>
        <title>{'Booking Requests | '+siteData.title}</title>
        <meta name="description" content={siteData.meta_description} />
        <meta name="og:title" content={'Booking Requests | '+siteData.title} />
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
                {!process.env.NEXT_PUBLIC_STRIPE_ACCOUNT_ID && !process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID ? 'Booking Request' : 'Booking' }
              </h1>
              <ul className="breadcrumb-nav">
                <li><Link href="/">Home</Link></li>
                <li className="active">{!process.env.NEXT_PUBLIC_STRIPE_ACCOUNT_ID && !process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID ? 'Booking Request' : 'Booking' }</li>
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

              { checkingAvailable ? (
                  <div className="pt-50 pb-50 pl-40 pr-40" style={{background:`white`}}>
                    <div className="text-center">
                      <h2 style={{color:'#bead8e'}}><i className="far fa-spinner fa-spin" /></h2>
                      <h2 style={{color:'#bead8e'}}>Checking availibility</h2>
                    </div>
                  </div>
                ) : 
                (
                  paymentRequest ? 
                  (
                    <div className="pt-50 pb-50 pl-40 pr-40" style={{background:`white`}}>
                      <div className="text-center">
                        <h2 style={{color:'#bead8e'}}><i className="far fa-spinner fa-spin" /></h2>
                        <h2 style={{color:'#bead8e'}}>Processing payment</h2>
                      </div>
                    </div>
                  ) : 
                  (
                    isAvailable ? (
                      <>
                      <div className="pt-40 pb-40">
                        <h2 className="subtitle mb-4"><strong>{roomData.name}</strong> is <span className="text-success">available!</span></h2>        
                        <div className="contact-info">
                          <div className="row justify-content-center">
                            <div className="col-lg-4 col-sm-12 text-center">
                              <img src={roomData.picture_sm} height="175px"/>
                            </div>
                            <div className="col-lg-4 col-6">
                              <div className="booking-details-box">
                                  <div className="title-label"><strong>Check-in</strong></div>
                                  <div className="title-details"><strong>{moment(start).format("MMM D, YYYY")}</strong></div>
                                  {roomData.checkin_time ? (<span><i className="flaticon-clock"></i> From  {roomData.checkin_time < 12 ? roomData.checkin_time+'AM' : (roomData.checkin_time-12)+'PM'}</span>) : ''}
                              </div>
                            </div>
                            <div className="col-lg-4 col-6">
                                <div className="booking-details-box">
                                  <div className="title-label"><strong>Check-out</strong></div>
                                  <div className="title-details"><strong>{moment(end).format("MMM D, YYYY")}</strong></div>
                                  {roomData.checkout_time ? (<span><i className="flaticon-clock"></i> Before {roomData.checkout_time < 12 ? roomData.checkout_time+'AM' : (roomData.checkout_time-12)+'PM'}</span>) : ''}
                                </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div className="pt-50 pb-50 pl-40 pr-40" style={{background:`white`}}>

                        { !process.env.NEXT_PUBLIC_STRIPE_ACCOUNT_ID && !process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID ? (
                          <>
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
                                    <input type="number" placeholder="Number of guests*" min="1" max={roomData.capacity} name="guests" ref={register} required />
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

                                {errorForm && <div className="text-center col-12 mb-5"><h4 className="text-danger">You gave a wrong result to the addition challenge</h4></div>}
                                {requestFailure && !requestSuccess && <div className="text-center col-12 mb-5"><h4 className="text-danger">An error happened. Please try sending your booking request again.</h4></div>}

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
                            <div>
                              <h3 className="subtitle mb-3">1. Tell us more about you</h3>
                              <form>
                                <div className="row">
                                  <div className="col-md-6">
                                    <div className="input-group mb-30">
                                      <span className="icon"><i className="far fa-user" /></span>
                                      <input type="text" placeholder="Your first name*" name="first_name" onChange={e => setFirstNameStripe(e.target.value)} required />
                                    </div>
                                  </div>
                                  <div className="col-md-6">
                                    <div className="input-group mb-30">
                                      <span className="icon"><i className="far fa-user" /></span>
                                      <input type="text" placeholder="Your last name*" name="last_name" onChange={e => setLastNameStripe(e.target.value)} required />
                                    </div>
                                  </div>
                                  <div className="col-md-4">
                                    <div className="input-group mb-30">
                                      <span className="icon"><i className="far fa-envelope" /></span>
                                      <input type="email" placeholder="Enter email*" name="email" onChange={e => setEmailStripe(e.target.value)} required />
                                    </div>
                                  </div>
                                  <div className="col-md-4">
                                    <div className="input-group mb-30">
                                      <span className="icon"><i className="far fa-phone" /></span>
                                      <input type="text" placeholder="Enter phone number*" onChange={e => setPhoneStripe(e.target.value)} name="phone" required/>
                                    </div>
                                  </div>
                                  <div className="col-md-4">
                                    <div className="input-group mb-30">
                                      <span className="icon"><i className="far fa-users" /></span>
                                      <input type="number" placeholder="Number of guests*" min="1" max={roomData.capacity} onChange={e => setGuestStripe(e.target.value)} name="guests" required />
                                    </div>
                                  </div>
                                </div>   
                                <div className="row">
                                  <div className="col-md-12 mb-30 text-center" >
                                    <Button size="lg" color={sendingRequest || price ? 'secondary' : 'primary'} style={{ marginBottom: '0.5rem' }} onClick={e => getPaymentIntent()} disabled={!(emailStripe && firstNameStripe && lastNameStripe && phoneStripe && guestStripe && start && end) || sendingRequest}>
                                      {sendingRequest ? <><i className="far fa-spinner fa-spin" /> Processing </> : (price ? 'Save changes' : 'Confirm')}
                                    </Button>
                                  </div>
                                </div>
                              </form>   

                              {price ? (
                                <>
                                <h3 className="subtitle mb-3">2. Price details</h3>
                                  <Table responsive>
                                    <tbody>
                                      <tr>
                                        <td>
                                          <b>Accommodation</b>
                                          {roomData.pricing_type == 'per_guest' && <p>{roomData.price+' '+roomData.currency} per guest each night</p>}
                                          {roomData.pricing_type == 'per_night' && !roomData.additional_guest_fee && <p>{roomData.price+' '+roomData.currency}/night</p>}
                                          {roomData.pricing_type == 'per_night' && roomData.additional_guest_fee && <p>{roomData.price+' '+roomData.currency}/night. Additional {roomData.additional_guest_price+' '+roomData.currency}/night after {roomData.additional_guest_threshold} guests.</p>}
                                        </td>
                                        <td></td>
                                        <td className="text-right">{price.total+price.discount-price.cleaning-price.deposit+' '+roomData.currency}</td>
                                      </tr>
                                      {price.discount!=0 && ( 
                                        <tr>
                                          <td><b>Discount</b>
                                            {nights>7 && roomData.weekly_factor!=1 && nights<28 && <p>Weekly discount of {Math.round((1-roomData.weekly_factor)*100)}%</p>}
                                            {nights>28 && roomData.monthly_factor!=1 && <p>Monthly discount of {Math.round((1-roomData.monthly_factor)*100)}%</p>}
                                          </td>
                                          <td></td>
                                          <td className="text-right text-success">-{price.discount+' '+roomData.currency}</td>
                                        </tr>
                                      )}
                                      {roomData.cleaning_fee === 1 && (
                                        <tr>
                                          <td><b>Cleaning fees</b></td>
                                          <td></td>
                                          <td className="text-right">{price.cleaning+' '+roomData.currency}</td>
                                        </tr>
                                      )}
                                      {roomData.security_deposit_fee === 1 && (
                                        <tr>
                                          <td><b>Security deposit</b></td>
                                          <td></td>
                                          <td className="text-right">{price.deposit+' '+roomData.currency}</td>
                                        </tr>
                                      )}
                                      <tr>
                                        <td></td>
                                        <td style={{background: 'black', color:'white'}} className="text-center"><b>Total</b></td>
                                        <td className="text-right" style={{background: '#BEAD8E', color:'white'}}><b>{price.total+' '+roomData.currency}</b></td>
                                      </tr>
                                    </tbody>
                                  </Table>
                                <h3 className="subtitle mt-2 mb-3">3. Choose a payment method</h3>
                                {(siteData.cancellation_policy || siteData.no_show_policy || siteData.deposit || siteData.other_policy) && (
                                  <p className="mb-5">By confirming your payment, you agree to our <a href="/policy" target="_blank"> Booking Policy.</a></p>
                                )}
                                { process.env.NEXT_PUBLIC_STRIPE_ACCOUNT_ID && (
                                  <>
                                    <div className="col-12 text-center">
                                      <Button size="lg" color={isCollapseOpen ? 'secondary':'primary'} style={{ marginBottom: '0.5rem' }} onClick={() => setIsCollapseOpen(!isCollapseOpen)}>
                                         <i className="far fa-lock"></i> Pay with credit or debit card
                                      </Button>
                                      <div>
                                        <i className="fab fa-cc-mastercard"></i> <i className="fab fa-cc-visa"></i> <i className="fab fa-cc-amex"></i>
                                      </div>
                                    </div>
                                    <Collapse isOpen={isCollapseOpen}>
                                      <Card>
                                        <CardBody>
                                          <CardElement className="stripe-input" options={CARD_ELEMENT_OPTIONS} onChange={handleStripeChange} />
                                          <div style={{marginTop: `3rem`}}>
                                            {errorForm && <div className="text-center col-12 mb-2"><h4 className="text-danger">{errorForm}</h4></div>}
                                            {requestFailure && <div className="text-center col-12 mb-2"><h4 className="text-danger">An error happened. Please try sending your booking request again.</h4></div>}
                                            <Button onClick={(e) => payWithStripe(e)} color="primary" size="lg" type="submit" className="btn-block" disabled={sendingRequest || disabledStripeButton || requestSuccess}>  
                                              {sendingRequest ? <i className="far fa-spinner fa-spin" /> : <>Pay now {roomData.currency} {price.total}</>}
                                            </Button>
                                            <div className="text-center"><small><i className="far fa-lock"></i> Payment is secured and processed by our partner <a href="http://stripe.com/" target="_blank">Stripe</a></small></div>
                                          </div>
                                        </CardBody>
                                      </Card>
                                    </Collapse>
                                  </>
                                )}
                                { process.env.NEXT_PUBLIC_STRIPE_ACCOUNT_ID && process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID && <h4 className="subtitle col-12 text-center mt-2 mb-2">or</h4>}
                                { process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID && (
                                  <>
                                    <div className="col-12 text-center">
                                      <PayPalButton
                                        createOrder={(data, actions) => {
                                          return actions.order.create({
                                            purchase_units: [{
                                              amount: {
                                                currency_code: siteData && roomData.currency,
                                                value: price.total
                                              }
                                            }],
                                          });
                                        }}
                                        onSuccess={(details, data) => {
                                          //console.log(data)
                                          //console.log(details)
                                          // OPTIONAL: Call your server to save the transaction
                                          saveNewPaypalBooking(details)
                                        }}
                                        style={{layout: "horizontal"}}
                                      />
                                    </div>
                                  </>
                                )}
                                </>
                              ) : 
                              (
                                <>
                                <h3 className="subtitle mb-3" style={{opacity:`0.5`}}>2. Price details</h3>
                                <h3 className="subtitle mb-3" style={{opacity:`0.5`}}>3. Choose a payment method</h3>
                                </>
                              )
                              }
                            </div>
                        )}
                      </div>
                      </>
                      ) : 
                      (
                        <div className="pt-50 pb-50 pl-40 pr-40" style={{background:`white`}}>
                          <div className="text-center">
                            {notAvailableMessage ? (
                              <>
                              <h2 className="subtitle mb-5">{notAvailableMessage}</h2>
                              <h4 className="subtitle mb-5">Please make a new search with new dates</h4>    
                              </>
                            ) : 
                            (
                              <>
                              <h2 className="subtitle mb-5">This accommodation is not available for your selected dates</h2>
                              <h4 className="subtitle mb-5">You can choose another accommodation or select different check-in and check-out dates</h4>    
                              </>                        
                            )}

                          </div>
                        </div>
                      )
                  )
                )
              }
          </div>
        </section>

        {/*====== CONTACT PART END ======*/}
        <Footer siteData={siteData} />
      </Layout>
    </>
  )
}