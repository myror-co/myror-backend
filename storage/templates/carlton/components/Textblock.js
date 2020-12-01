import React, { Component } from 'react';
import Link from 'next/link'
import ReactWOW from 'react-wow'

import airbnb_superhost from '../assets/img/airbnb_superhost.png';

export default function Textblock({siteData}){

    return (
        <section className="text-block pt-60 pb-115">
            <div className="container">
                <ReactWOW animation="fadeInLeft" data-wow-delay=".3s">
                    {siteData.listings[0].user.is_superhost && (
                        <div className="row align-items-center justify-content-center mb-5">
                            <a href={`https://www.airbnb.com/users/show/${siteData.listings[0].user.id}`} target="_blank"><img width="200px" src={airbnb_superhost} /></a>
                        </div>
                    )}
                    <div className="row align-items-center justify-content-center">
                        <div className="col-lg-7">
                            <div className="text-img text-center text-lg-left mb-small">
                                <img src={siteData.listings[0].photos[1].picture} alt="" />
                            </div>
                        </div>
                        <ReactWOW animation="fadeInRight" data-wow-delay=".5s">
                            <div className="col-lg-5 col-md-8 col-sm-10">
                                <div className="block-text">
                                    <div className="section-title mb-20">
                                        <span className="title-tag">Take a tour</span>
                                        <h2>Discover Our Underground.</h2>
                                    </div>
                                    <p>{siteData.description}</p>
                                    <Link href="/rooms" className="main-btn btn-filled mt-40">Learn More</Link>
                                </div>
                            </div>
                        </ReactWOW>
                    </div>
                </ReactWOW>
            </div>
        </section>
        
    );
}
