import React, { Component } from 'react';
import Link from 'next/link'

class Footer extends Component {
  constructor(props) {
    super(props);
    this.state = {
       redText: false
    };
  }
  componentDidMount() {
    window.addEventListener('scroll', () => {
      this.setState({
          isTop: window.scrollY > 300
      });
  }, false);
  }
  scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: "smooth"
    });
  }
  render() {
    const className = this.state.isTop ? 'active' : '';
    const textFooter = this.props.listing.listings[0].neighborhood ? this.props.listing.listings[0].neighborhood : this.props.listing.listings[0].space;
    return (
      <div>
        {/*====== Back to Top ======*/}
        <Link href="/" className={`back-to-top ${className}`} id="backToTop" onClick={() => this.scrollToTop()}>
          <i className="fal fa-angle-double-up" />
        </Link>
        {/*====== FOOTER START ======*/}
        <footer className="footer-two">
          <div className="footer-widget-area pt-100 pb-50">
            <div className="container">
              <div className="row">
                <div className="col-lg-3 col-sm-6 order-1">
                  {/* Site Info Widget */}
                  <div className="widget site-info-widget mb-50">
                    <div className="footer-logo mb-50">
                      <img src="assets/img/footer-logo.png" alt="" />
                    </div>
                    <p>
                      {textFooter}
                    </p>
                    <div className="social-links mt-40">
                      <Link href="/"><i className="fab fa-facebook-f" /></Link>
                      <Link href="/"><i className="fab fa-twitter" /></Link>
                      <Link href="/"><i className="fab fa-behance" /></Link>
                      <Link href="/"><i className="fab fa-linkedin" /></Link>
                      <Link href="/"><i className="fab fa-youtube" /></Link>
                    </div>
                  </div>
                </div>
                <div className="col-lg-6 order-3 order-lg-2">
                  {/* Nav Widget */}
                  <div className="widget nav-widget mb-50">
                    <div>
                      <h4 className="widget-title">Services.</h4>
                      <ul>
                        <li><Link href="/">Resturent &amp; Bar</Link></li>
                        <li><Link href="/">Gaming Zone</Link></li>
                        <li><Link href="/">Swimming Pool</Link></li>
                        <li><Link href="/">Marrige Party</Link></li>
                        <li><Link href="/">Restaurant</Link></li>
                        <li><Link href="/">Party Planning</Link></li>
                        <li><Link href="/">Conference Room</Link></li>
                        <li><Link href="/">Tour Consultancy</Link></li>
                        <li><Link href="/">Coctail Party Houses</Link></li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div className="col-lg-3 col-sm-6 order-2 order-lg-3">
                  {/* Contact Widget */}
                  <div className="widget contact-widget mb-50">
                    <h4 className="widget-title">Contact Us.</h4>
                    <div className="contact-lists">
                      <div className="contact-box">
                        <div className="icon">
                          <i className="flaticon-call" />
                        </div>
                        <div className="desc">
                          <h6 className="title">Phone Number</h6>
                          +987 876 765 76 577
                        </div>
                      </div>
                      <div className="contact-box">
                        <div className="icon">
                          <i className="flaticon-message" />
                        </div>
                        <div className="desc">
                          <h6 className="title">Email Address</h6>
                          <Link href="/">info@webmail.com</Link>
                        </div>
                      </div>
                      <div className="contact-box">
                        <div className="icon">
                          <i className="flaticon-location-pin" />
                        </div>
                        <div className="desc">
                          <h6 className="title">Office Address</h6>
                          {this.props.listing.address}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div className="copyright-area pt-30 pb-30">
            <div className="container">
              <div className="row align-items-center">
                <div className="col-lg-6 col-md-5 order-2 order-md-1">
                  <p className="copyright-text copyright-two">Copyright &copy; <Link href="/">Myror.co</Link> - 2020</p>
                </div>
                <div className="col-lg-6 col-md-7 order-1 order-md-2">
                  <div className="footer-menu text-center text-md-right">
                    <ul>
                      <li><Link href="/">Terms of use</Link></li>
                      <li><Link href="/">Privacy</Link></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </footer>
        {/*====== FOOTER END ======*/}
      </div>
    );
  }
}

export default Footer;
