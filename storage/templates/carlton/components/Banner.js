import React, { Component } from 'react';
import Link from 'next/link'
import Slider from "react-slick"
import ReactWOW from 'react-wow'

{/*
import bannerimg1 from '../assets/img/banner/04.jpg';
import bannerimg2 from '../assets/img/banner/05.jpg';
*/}
class Banner extends Component {
  constructor(props) {
    super(props);
    this.state = {
       redText: false
    };
  }

  render() {
    const settings = {
        infinite: true,
        autoplay: true,
        autoplaySpeed: 5000,
        dots: false,
        fade: true,
        arrows: false,
    };

    const bannerPosts = [
      {
          photo: null,
          tag: this.props.listing.listings[0].smart_location,
          taganimation: '.6s',
          title:this.props.listing.title,
          titleanimation:'.9s',
          btn1:'Book',
          btn1animation:'1.1s',
          btn1url:'/room-details',
          btn2:'Take a tour',
          btn2animation:'1.3s',
          btn2url:'/about',
      },
      {
          photo: null,
          tag: this.props.listing.listings[0].smart_location,
          taganimation: '.6s',
          title:this.props.listing.title,
          titleanimation:'.9s',
          btn1:'Book',
          btn1animation:'1.1s',
          btn1url:'/room-details',
          btn2:'Take a tour',
          btn2animation:'1.3s',
          btn2url:'/about',
      },
    ];

    return (
    <Slider className="banner-area banner-style-two" id="bannerSlider" {...settings}>
      {bannerPosts.map((item, i) => (
        <div key={i} className="single-banner d-flex align-items-center justify-content-center">
          <div className="container">
            <div className="row justify-content-center">
              <div className="col-lg-8">
                <div className="banner-content text-center">
                <ReactWOW animation="fadeInDown" data-delay={item.taganimation}>
                  <span className="promo-tag">{item.tag}</span></ReactWOW>
                    <ReactWOW animation="fadeInLeft" data-delay={item.titleanimation}>
                  <h1 className="title">{item.title}
                  </h1></ReactWOW>
                  <ul>
                  <ReactWOW animation="fadeInUp" data-delay={item.btn1animation}>
                    <li>
                      <Link href={item.btn1url}><a className="main-btn btn-filled" target="_blank" rel="noopener noreferrer">{item.btn1}</a></Link>
                    </li></ReactWOW>
                    <ReactWOW animation="fadeInUp" data-delay={item.btn2animation}>
                    <li>
                      <Link href={item.btn2url}><a className="main-btn btn-filled" target="_blank" rel="noopener noreferrer">{item.btn2}</a></Link>
                    </li></ReactWOW>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          {/* banner bg */}
          <div className="banner-bg" style={{backgroundImage: 'url('+this.props.listing.listings[0].picture_xl+')'}} />
          <div className="banner-overly" />
        </div>
      ))}
      </Slider>
    );
  }
}

export default Banner;
