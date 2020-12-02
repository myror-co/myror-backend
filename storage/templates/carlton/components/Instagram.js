import React, { Component, useState, useEffect } from 'react';
import Link from 'next/link'
import Slider from "react-slick"; 

import apiClient from '../services/api.js';

export default function Instagram({siteData}){

    const [instaPosts, setInstaPosts] = useState([])
    const [loading, setLoading] = useState(false)

    useEffect(() => {
      getInstagramPosts()
    }, []);

    const getInstagramPosts = () => {
        setLoading(true)

        apiClient.get('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID+'/instagram')
        .then(result => {
          setInstaPosts(result.data.instagram_photos)
          setLoading(false)
        })
        .catch(e =>{
          setLoading(false)
        });
    }

    const settings = {
        slidesToShow: 6,
        slidesToScroll: 1,
        fade: false,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 3000,
        arrows: false,
        dots: false,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 4,
                },
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 3,
                },
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 2,
                },
            },
        ],
    };

    if(loading) return <div className="row"><div className="col-lg-12 mb-5 text-center"><i className="far fa-spinner fa-spin fa-3x" /><br/> Loading Instagram Pictures</div></div>

    return (
        <section className="instagram-feed-section">
            <div className="container-fluid p-0">
            <Slider className="instagram-slider" {...settings}>
            {instaPosts.map((item, i) => (
                <div key={i} className="image">
                    <img src={item} alt="" />
                </div>
            ))}
            </Slider>
            </div>
        </section>
    );
}