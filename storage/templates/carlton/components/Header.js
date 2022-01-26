import React, { Component } from 'react';
import Link from 'next/link'
import classNames from 'classnames';
import $ from 'jquery';
import {findDOMNode } from 'react-dom'
import Image from 'next/image'

const navigationmenu = [
  {
    id: 1,
    link: '/',
    linkText:'Home',
    child: false,
  },
  {
    id: 2,
    link: '/rooms',
    linkText:'Rooms',
    child: false,
  },
  {
    id: 3,
    link: '/booking',
    linkText:'Book Now',
    child: false,
  }
]


class Header extends Component {
  constructor(props) {
    super(props);
    this.state = {
       redText: false
    };
    this.addClass = this.addClass.bind(this);
    this.removeClass = this.removeClass.bind(this);
    this.removeAll = this.removeAll.bind(this);
  }
  addClass() {
    this.setState({
      redText:true
    });
  }
  
  removeClass() {
    this.setState({
      redText:false
    });
  }
  removeAll() {
    this.setState({
      redText:false
    });
  }
  componentDidMount() {
    window.addEventListener('resize', () => {
        this.setState({
            isMobile: window.innerWidth < 1020
        });
    }, false);
    window.addEventListener('load', () => {
      this.setState({
          isMobile: window.innerWidth < 1020
      });
  }, false);
    window.addEventListener('scroll', () => {
      this.setState({
          isTop: window.scrollY > 150
      });
  }, false);
  }
  navToggle = () => {
    const nv = findDOMNode(this.refs.navmenu);
    const nvb = findDOMNode(this.refs.navbtn);
    $(nv).toggleClass('menu-on');
    $(nvb).toggleClass('active');
  }
  removenavToggle = () => {
    const nv = findDOMNode(this.refs.navmenu);
    const nvb = findDOMNode(this.refs.navbtn);
    $(nv).removeClass('menu-on');
    $(nvb).removeClass('active');
  }
  getNextSibling = function (elem, selector) {

    // Get the next sibling element
    var sibling = elem.nextElementSibling;
  
    // If there's no selector, return the first sibling
    if (!selector) return sibling;
  
    // If the sibling matches our selector, use it
    // If not, jump href the next sibling and continue the loop
    while (sibling) {
      if (sibling.matches(selector)) return sibling;
      sibling = sibling.nextElementSibling
    }
  
  }
  
  triggerChild = (e) => {
    let subMenu = '';
  
    subMenu = ( this.getNextSibling(e.target, '.submenu') !== undefined ) ? this.getNextSibling(e.target, '.submenu') : null;

    if(subMenu !== null && subMenu !== undefined && subMenu !== ''){
      subMenu.classList = subMenu.classList.contains('open') ? 'submenu' : 'submenu open';
    }
  }
  render() {
    const className = this.state.isMobile ? 'breakpoint-on' : '';
    const classNamess = this.state.isMobile ? 'd-none' : '';
    const classNamesss = this.state.isTop ? 'sticky-active' : '';
    const google = this.props.siteData.google ? this.props.siteData.google : '#';
    const facebook = this.props.siteData.facebook ? this.props.siteData.facebook : '#';
    const instagram = this.props.siteData.instagram ? this.props.siteData.instagram : '#';
    
    return (
      <div>
        {/*====== HEADER START ======*/}
        <header className={`header-absolute header-two sticky-header ${classNamesss}`}>
          <div className="container container-custom-one">
            <div className={`nav-container d-flex row align-items-center ${className}`}>
              {/* Main Menu */}
              <div className="nav-menu d-lg-flex col-lg-4 align-items-center" ref="navmenu">
                {/* Navbar Close Icon */}
                <div className="navbar-close" onClick={this.removenavToggle}>
                  <div className="cross-wrap"><span className="top" /><span className="bottom" /></div>
                </div>
                {/* Off canvas Menu  */}
                <div className="toggle" onClick={this.addClass}>
                  <a href="#" id="offCanvasBtn"><i className="fal fa-bars" /></a>
                </div>
                {/* Mneu Items */}
                <div className="menu-items">
                <ul>
                  {navigationmenu.length > 0 ? navigationmenu.map((item, i) => (
                      <li key={i} className={` ${item.child ? 'menu-item-has-children' : ''} `} onClick={this.triggerChild}>
                      {item.child ? <Link onClick={e => e.preventDefault()} href="/">{item.linkText}</Link> : <Link href={item.link}>{item.linkText}</Link> }
                          {item.child ?
                              <ul className="submenu" role="menu">
                                  {item.submenu.map((sub_item, i) => (
                                      <li key={i} className={`${sub_item.child ? 'menu-item-has-child' : ''} `}>
                                      {sub_item.child ? <Link onClick={e => e.preventDefault()} href="/">{sub_item.linkText}</Link> : <Link href={sub_item.link}>{sub_item.linkText}</Link> }
                                          {sub_item.third_menu ?
                                              <ul className="submenu">
                                                  {sub_item.third_menu.map((third_item, i) => (
                                                      <li key={i}><Link href={third_item.link}>{third_item.linkText}</Link>
                                                      </li>
                                                  ))}
                                              </ul> : null}
                                      </li>
                                  ))}
                              </ul>
                              : null
                          }
                      </li>
                  )) : null}
                  </ul>
                </div>
                {/* from pushed-item */}
                <div className="nav-pushed-item" />
              </div>
              {/* Site Logo */}
              <div className="site-logo col-lg-4 text-center">
                {this.props.siteData.icon && <Link href="/"><img src={this.props.siteData.icon} alt="logo" width="100px" /></Link>}
              </div>
              {/* Header Info Pussed href Menu Wrap */}
              <div className={`nav-push-item col-lg-4`}>
                {/* Header Info */}
                <div className="header-info d-lg-flex align-items-center float-right d-none">
                    { /* this.props.siteData.email && (
                      <div className="item">
                        <i className="fal fa-envelope" />
                        <span>Email Address</span>
                        <Link href="#">
                          <h5 className="title">{this.props.siteData.email}</h5>
                        </Link>
                      </div>
                    ) */}
                    { this.props.siteData.whatsapp_number ?
                      (
                          <div className="item">
                            <a href={'https://wa.me/'+this.props.siteData.whatsapp_number.replace('+', '').replace('-', '').replace('(', '').replace(')', '').replace(' ', '')} target="_blank"><Image alt="myror logo" width={50} height={50} src="/whatsapp.png" /></a>
                          </div>
                      ) : 
                      (
                        this.props.siteData.phone && (
                          <div className="item">
                            <i className="fal fa-phone" />
                            <span>Phone Number</span>
                            <Link href="/">
                              <h5 className="title">{this.props.siteData.phone}</h5>
                            </Link>
                          </div>
                        )
                      )
                    }
                </div>
              </div>
              {/* Navbar Toggler */}
              <div className="navbar-toggler" onClick={this.navToggle}  ref="navbtn">
                <span /><span /><span />
              </div>
            </div>
          </div>
        </header>
        {/*====== HEADER END ======*/}
        {/*====== OFF CANVAS START ======*/}
        <div className={classNames("offcanvas-wrapper", {"show-offcanvas":this.state.redText})}>
        <div className={classNames("offcanvas-overly", {"show-overly":this.state.redText})}  onClick={this.removeAll}/>
          <div className="offcanvas-widget">
            <a href="#" className="offcanvas-close" onClick={this.removeClass}><i className="fal fa-times" /></a>
            {/* About Widget */}
            <div className="widget about-widget">
              {this.props.siteData.summary && (
                <>
                <h5 className="widget-title">About us</h5>
                <p>
                  {this.props.siteData.summary}
                </p>
                </>
              )}
            </div>
            {/* Social Link */}
            { google != '#' || facebook != '#' || instagram != '#' && (
              <div className="widget social-link">
                  <h5 className="widget-title">Find us on</h5>
                  <ul>
                    { google != '#' && <li><a target="_blank" href={google}><i className="fab fa-google" /></a></li> }
                    { facebook != '#' && <li><a target="_blank" href={facebook}><i className="fab fa-facebook-f" /></a></li> }
                    { instagram != '#' && <li><a target="_blank" href={instagram}><i className="fab fa-instagram" /></a></li> }
                  </ul>
              </div>
            )}
            {/* Sitemap */}
            <div className="widget nav-widget">
              <h5 className="widget-title">Sitemap</h5>
              <ul>
                <li><Link href="/">Home</Link></li>
                <li><Link href="/rooms">Rooms</Link></li>
                <li><Link href="/booking">Booking</Link></li>
              </ul>
            </div>
          </div>
        </div>
        {/*====== OFF CANVAS END ======*/}
      </div>
    );
  }
}

export default Header;
