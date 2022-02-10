import React from "react";
import Document, {Html, Head, Main, NextScript } from "next/document";

class MyDocument extends Document {

  render() {
    return (
      <Html lang="en">
        <Head>
          {/*Google Analytics*/}
          { typeof process.env.NEXT_PUBLIC_GOOGLE_ANALYTICS_ID !== 'undefined' && (
            <>
            <script
                async
                src={`https://www.googletagmanager.com/gtag/js?id=${process.env.NEXT_PUBLIC_GOOGLE_ANALYTICS_ID}`}
            />
            <script dangerouslySetInnerHTML={
                { __html: `
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){window.dataLayer.push(arguments)}
                    gtag("js", new Date());
                    gtag("config", "${process.env.NEXT_PUBLIC_GOOGLE_ANALYTICS_ID}");
                `}} 
            />
            </>
          )}
          {/*Facebook Pixel*/}
          { typeof process.env.NEXT_PUBLIC_FACEBOOK_PIXEL_ID !== 'undefined' && (
            <>
              <script dangerouslySetInnerHTML={
                { __html: `
                  !function(f,b,e,v,n,t,s)
                  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                  n.queue=[];t=b.createElement(e);t.async=!0;
                  t.src=v;s=b.getElementsByTagName(e)[0];
                  s.parentNode.insertBefore(t,s)}(window, document,'script',
                  'https://connect.facebook.net/en_US/fbevents.js');
                  fbq('init', '${process.env.NEXT_PUBLIC_FACEBOOK_PIXEL_ID}');
                  fbq('track', 'PageView');
                `}} 
              />
              <noscript dangerouslySetInnerHTML={
                { __html: `
                <img height="1" width="1" style={{display:'none'}} 
                     src="https://www.facebook.com/tr?id=${process.env.NEXT_PUBLIC_FACEBOOK_PIXEL_ID}&ev=PageView&noscript=1"/>
                `}} 
              />
            </>
          )}
          { typeof process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID !== 'undefined' && (
            <script src={`https://www.paypal.com/sdk/js?client-id=${process.env.NEXT_PUBLIC_PAYPAL_CLIENT_ID}&currency=${process.env.NEXT_PUBLIC_PAYPAL_CURRENCY}`}></script>
          )}
        </Head>
        <body>
          <div id="page-transition"></div>
          <Main />
          <NextScript />
        </body>
      </Html>
    );
  }
}

export default MyDocument;
