import React, { Component } from 'react';
import Link from 'next/link'
import Image from 'next/image'

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

    const google = this.props.siteData.google ? this.props.siteData.google : '#';
    const facebook = this.props.siteData.facebook ? this.props.siteData.facebook : '#';
    const instagram = this.props.siteData.instagram ? this.props.siteData.instagram : '#';
    return (
      <div>
        {/*====== Back to Top ======*/}
          <button onClick={() => this.scrollToTop()} className={`back-to-top ${className}`} style={{border:'none'}} id="backToTop"><i className="fal fa-angle-double-up" /></button>
        {/*====== FOOTER PART START ======*/}
        <footer>
          <div className="copyright-area pt-20 pb-20">
            <div className="container">
              <div className="row align-items-center">
                {this.props.siteData.branding && (
                  <div className="col-md-5 order-2 order-md-1">
                    <div className="copyright-text d-flex align-items-center">
                      <div className="mr-1">Powered by myror</div>
                      <a target="_blank" href="https://myror.co">
                        <Image alt="myror logo" width={40} height={40} src="/myror_m.png" />
                      </a>
                    </div>
                  </div>
                )}
                <div className="col-md-7 order-1 order-md-2">
                  <div className="social-links">
                    { google && <a target="_blank" href={google}><i className="fab fa-google" /></a> }
                    { facebook && <a target="_blank" href={facebook}><i className="fab fa-facebook-f" /></a> }
                    { instagram && <a target="_blank" href={instagram}><i className="fab fa-instagram" /></a> }
                  </div>
                </div>
              </div>
            </div>
          </div>
        </footer>
        {/*====== FOOTER PART END ======*/}
      </div>
    );
  }
}

export default Footer;
