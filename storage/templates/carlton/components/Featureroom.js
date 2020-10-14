import React, { Component } from 'react';
import Link from 'next/link'
import Slider from "react-slick";

{/*
import froomimg1 from '../assets/img/room/01.jpg';
import null from '../assets/img/room/02.jpg';
import froomimg3 from '../assets/img/room/03.jpg';
*/} 
const featurePosts = [

];

function SampleNextArrow(props) {
    const { onClick } = props;
    return (
      <div className="slick-arrow next-arrow" onClick={onClick}><i className="fal fa-arrow-right"></i></div>
    );
  }
  
  function SamplePrevArrow(props) {
    const { onClick } = props;
    return (
      <div className="slick-arrow prev-arrow" onClick={onClick}><i className="fal fa-arrow-left"></i></div>
    );
  }
class Featureroom extends Component {
  render() {
    const settings = {
        slidesToShow: 3,
        slidesToScroll: 1,
        fade: false,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 4000,
        arrows: false,
        dots: false,
        nextArrow: <SampleNextArrow />,
        prevArrow: <SamplePrevArrow />,
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 2,
                },
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 1,
                },
            },
        ],
    };
    return (
        <section className="feature-room-section pt-155 pb-115">
            <div className="container">
            <div className="row align-items-center">
                <div className="col-lg-6 col-md-8 col-sm-7">
                <div className="section-title">
                    <span className="title-tag">Room</span>
                    <h2>Featured Room</h2>
                </div>
                </div>
                <div className="col-lg-6 col-md-4 col-sm-5 d-none d-sm-block">
                <div className="feature-room-arrow arrow-style text-right">
                <SamplePrevArrow />
                <SampleNextArrow />
                </div>
                </div>
            </div>
            {/* Room Looop */}
            <Slider className="row room-gird-loop mt-80 feature-room-slider" {...settings}>
            {featurePosts.map((item, i) => (
                <div key={i} className="col-lg-12">
                <div className="room-box">
                    <div className="room-img-wrap">
                    <div className="room-img" style={{backgroundImage: "url("+ item.photo + ")"}} />
                    </div>
                    <div className="room-desc">
                    <ul className="icons">
                        <li><i className="fal fa-bed" /></li>
                        <li><i className="fal fa-wifi" /></li>
                        <li><i className="fal fa-car" /></li>
                        <li><i className="fal fa-coffee" /></li>
                        <li><i className="fal fa-concierge-bell" /></li>
                        <li><i className="fal fa-compress-arrows-alt" /></li>
                        <li><i className="fal fa-swimmer" /></li>
                    </ul>
                    <h4 className="title"><Link href={item.url}>{item.title}</Link></h4>
                    <p>{item.desc}</p>
                    <span className="price">{item.price}</span>
                    <Link href={item.url} className="book-btn">{item.book}</Link>
                    </div>
                </div>
                </div>
            ))}
            </Slider>
            </div>
        </section>
    );
  }
}

export default Featureroom;
