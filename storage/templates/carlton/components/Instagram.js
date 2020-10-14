import React, { Component } from 'react';
import Link from 'next/link'
import Slider from "react-slick"; 

// import instaimg1 from '../assets/img/instagram/01.jpg';
// import instaimg2 from '../assets/img/instagram/02.jpg';
// import instaimg3 from '../assets/img/instagram/03.jpg';
// import instaimg4 from '../assets/img/instagram/04.jpg';
// import instaimg5 from '../assets/img/instagram/05.jpg';
// import instaimg6 from '../assets/img/instagram/06.jpg';

const instaPosts = [
{/*}    {
        photo: instaimg1,
    },
*/}
];
class Instagram extends Component {
  render() {
    const settings = {
        slidesToShow: 6,
        slidesToScroll: 1,
        fade: false,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 4000,
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
    return (
        <section className="instagram-feed-section">
            <div className="container-fluid p-0">
            <Slider className="instagram-slider" {...settings}>
            {instaPosts.map((item, i) => (
                <div key={i} className="image">
                <Link href={item.photo} className="insta-popup">
                    <img src={item.photo} alt="" />
                </Link>
                </div>
            ))}
            </Slider>
            </div>
        </section>
    );
  }
}

export default Instagram;
