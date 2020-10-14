import React, { Component } from 'react';
import Link from 'next/link'
import classNames from 'classnames';
import $ from 'jquery';
import {findDOMNode } from 'react-dom'

const navigationmenu = [
//   {
//       id: 1,
//       linkText:'Home',
//       child: true,
//       submenu: [
//           {
//               id: 11,
//               link: '/',
//               linkText:'Home 1'
//           },
//           {
//               id: 12,
//               new: true,
//               link: '/',
//               linkText:'Home 2'
//           },
//           {
//               id: 13,
//               link: '/',
//               linkText:'Home 3'
//           },
//       ]
      
      
//   },
//   {
    
//     id: 2,
//     linkText:'Rooms',
//     child: true,
//     submenu:[
//       {
//         id: 21,
//         link:'/room-grid',
//         linkText:'Room Grid',
//       },
//       {
//         id: 22,
//         link:'/room-list',
//         linkText:'Room List',
//       },
//       {
//         id: 23,
//         link:'/room-details',
//         linkText:'Room Details',
//       },
//     ]
    
// },
// {
//   id: 3,
//   linkText: 'News',
//   child: true,
//   submenu:[
//     {
//       id: 31,
//       link:'/news',
//       linkText:'News',
//     },
//     {
//       id: 32,
//       link:'/news-details',
//       linkText:'News Details',
//     },
//   ]
  
// },
  {
    id: 4,
    link: '/contact',
    linkText:'Contact',
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
    const phone = this.props.listing.phone ? this.props.listing.phone : 'add phone';
    const email = this.props.listing.email ? this.props.listing.email : 'add email';
    
    return (
      <div>
        {/*====== HEADER START ======*/}
        <header className={`header-absolute header-two sticky-header ${classNamesss}`}>
          <div className="container container-custom-one">
            <div className={`nav-container d-flex align-items-center justify-content-between ${className}`}>
              {/* Main Menu */}
              <div className="nav-menu d-lg-flex align-items-center" ref="navmenu">
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
              <div className="site-logo">
                <Link href="#" className="main-logo"><img src="assets/img/logo-white.png" alt="" /></Link>
                <Link href="#" className="sticky-logo"><img src="assets/img/logo.png" alt="" /></Link>
              </div>
              {/* Header Info Pussed href Menu Wrap */}
              <div className={`nav-push-item ${classNamess}`}>
                {/* Header Info */}
                <div className="header-info d-lg-flex align-items-center">
                  <div className="item">
                    <i className="fal fa-phone" />
                    <span>Phone Number</span>
                    <Link href="/">
                      <h5 className="title">{phone}</h5>
                    </Link>
                  </div>
                  <div className="item">
                    <i className="fal fa-envelope" />
                    <span>Email Address</span>
                    <Link href="#">
                      <h5 className="title">{email}</h5>
                    </Link>
                  </div>
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
            {/* Search Widget */}
            <div className="widget search-widget">
              <h5 className="widget-title">Search room</h5>
              <form action="#">
                <input type="text" placeholder="Search your keyword..." />
                <button type="submit"><i className="far fa-search" /></button>
              </form>
            </div>
            {/* About Widget */}
            <div className="widget about-widget">
              <h5 className="widget-title">About us</h5>
              <p>
                {this.props.listing.summary}
              </p>
            </div>
            {/* Nav Widget */}
            <div className="widget nav-widget">
              <h5 className="widget-title">Our pages</h5>
              <ul>
                <li><Link href="/">About Us</Link></li>
                <li><Link href="/">Food Menu</Link></li>
                <li><Link href="/">Restaurant</Link></li>
                <li>
                  <Link href="/">Places</Link>
                  <ul className="submenu">
                    <li><Link href="/">Places Details</Link></li>
                  </ul>
                </li>
                <li><Link href="/">Offers</Link></li>
                <li><Link href="/">Our Gallery</Link></li>
              </ul>
            </div>
            {/* Social Link */}
            <div className="widget social-link">
              <h5 className="widget-title">Contact with us</h5>
              <ul>
                <li><Link href="#"><i className="fab fa-facebook-f" /></Link></li>
                <li><Link href="#"><i className="fab fa-twitter" /></Link></li>
                <li><Link href="#"><i className="fab fa-behance" /></Link></li>
                <li><Link href="#"><i className="fab fa-linkedin" /></Link></li>
                <li><Link href="#"><i className="fab fa-google" /></Link></li>
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
