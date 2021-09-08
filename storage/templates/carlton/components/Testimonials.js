import React, { Component } from 'react';
import Slider from "react-slick";

{/*}
import tesimg1 from '../assets/img/testimonial/01.png';
import tesimg2 from '../assets/img/testimonial/02.png';
import tesimg3 from '../assets/img/testimonial/03.png';
*/}

class Testmonials extends Component {

  getTestimonials = () => {

    var testimonials = []

    this.props.siteData.listings.forEach(function(item){
      if(item.recent_review.length != 0 )
      {
        testimonials = testimonials.concat(item.recent_review)
      }
      
    })

    return testimonials
  }

  render() {
    const settings = {
      slidesToShow: 3,
      slidesToScroll: 1,
      fade: false,
      infinite: true,
      autoplay: true,
      autoplaySpeed: 4000,
      arrows: false,
      dots: true,
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
      <section className="testimonial-section pb-115 pt-115">
          <div className="container">
            <div className="section-title text-center mb-80">
              <span className="title-tag">testimonials</span>
              <h2>Guest Reviews</h2>
            </div>
            {/* testimonials loop  */}
            <Slider className="row testimonial-slider" {...settings}>
            {this.getTestimonials().map((item, i) => (
              <div key={i} className="col-lg-12">
                <div className="testimonial-box">
                  <div className="client-img">
                    <img src={item.reviewer.picture_url} alt="" />
                    <span className="check"><i className="fal fa-check" /></span>
                  </div>
                  <h3>{item.reviewer.first_name}</h3>
                  <span className="clinet-post">{item.localized_date}</span>
                  <p>
                  {item.comments.length > 400 ? item.comments.substring(0, 400)+'...' : item.comments}
                  </p>
                </div>
              </div>
              ))}
            </Slider>
          </div>
        </section>
    );
  }
}

export default Testmonials;
