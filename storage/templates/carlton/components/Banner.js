import React, { Component } from 'react';
import Link from 'next/link'
import Slider from "react-slick"
import ReactWOW from 'react-wow'

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
          tag: this.props.siteData.listings[0].smart_location,
          taganimation: '.6s',
          title:this.props.siteData.title,
          titleanimation:'.9s',
          btn1:'Book',
          btn1animation:'1.1s',
          btn1url:'/booking',
          btn2:'Take a tour',
          btn2animation:'1.3s',
          btn2url:'/rooms',
      }
    ];

    return (
    <Slider className="banner-area banner-style-two" id="bannerSlider" {...settings}>
      {bannerPosts.map((item, i) => (
        <div key={i} className="single-banner d-flex align-items-center justify-content-center">
          <div className="container">
            <div className="row justify-content-center">
              <div className="col-lg-8">
                <div className="banner-content text-center">
                  <ReactWOW animation="fadeInDown" data-wow-delay={item.taganimation}>
                    <span className="promo-tag">{item.tag}</span>
                  </ReactWOW>
                  <ReactWOW animation="fadeInLeft" data-wow-delay={item.titleanimation}>
                    <h1 className="title">{item.title}</h1>
                  </ReactWOW>
                  <ul className="mt-5">
                      <ReactWOW animation="fadeInUp" data-wow-delay={item.btn2animation}>
                      <li>
                        <Link href={item.btn2url}><a className="main-btn btn-filled" rel="noopener noreferrer">{item.btn2}</a></Link>
                      </li>
                    </ReactWOW>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          {/* banner bg */}
          <div className="banner-bg" style={{backgroundImage: 'url('+this.props.siteData.listings[0].picture_xl+')'}} />
          <div className="banner-overly" />
        </div>
      ))}
      </Slider>
    );
  }
}

export default Banner;
