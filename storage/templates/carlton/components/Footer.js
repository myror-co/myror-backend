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
                <div className="col-md-5 order-2 order-md-1">
                  <p className="copyright-text">
                    Powered by <a target="_blank" href="https://myror.co"><img src="/myror_m.png" width="40px"/></a>
                  </p>
                </div>
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
