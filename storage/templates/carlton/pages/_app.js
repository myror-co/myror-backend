import Router from 'next/router';
import ReactDOM from "react-dom";

import '../styles/globals.css'
import '../assets/css/animate.min.css';
import '../node_modules/bootstrap/dist/css/bootstrap.min.css';
import '../assets/css/font-awesome.min.css';
import '../assets/css/flaticon.css';
import "../node_modules/slick-carousel/slick/slick.css"; 
import '../assets/css/default.css';
import '../assets/css/style.css';
import 'react-responsive-modal/styles.css';
import 'react-dates/lib/css/_datepicker.css';

function MyApp({ Component, pageProps }) {
  return <Component {...pageProps} />
}

export default MyApp
