import React, { Component } from 'react';
import Link from 'next/link'
import ReactWOW from 'react-wow'

export default function Textblock({listing}){

    return (
        <section className="text-block pt-115 pb-115">
            <div className="container">
                <ReactWOW animation="fadeInLeft" data-wow-delay=".3s">
            <div className="row align-items-center justify-content-center">
                <div className="col-lg-7">
                <div className="text-img text-center text-lg-left mb-small">
                    <img src={listing.listings[0].id} alt="" />
                </div>
                </div>
                <ReactWOW animation="fadeInRight" data-wow-delay=".5s">
                <div className="col-lg-5 col-md-8 col-sm-10">
                <div className="block-text">
                    <div className="section-title mb-20">
                    <span className="title-tag">Take a tour</span>
                    <h2>Discover Our Underground.</h2>
                    </div>
                    <p>
Do Coron in style! Choose a lush postcard-perfect tropical paradise with turquoise-blue waters, powdery-white sand and coconut trees instead of the usual noisy and dirty choices the town has to offer. Our island was just listed in SPOT's 10 Breathtaking Island Resorts Around the Philippines and has the richest marine life, fabulous organic food, experienced and caring staff and thrilling activities to keep you coming back! Our fee includes: - Accommodation - Snorkeling gear, kayaks and SUPs
                    </p>
                    <Link href="/" className="main-btn btn-filled mt-40">Learn More</Link>
                </div>
                </div>
                </ReactWOW>
            </div>
            </ReactWOW>
            </div>
        </section>
        
    );
}
