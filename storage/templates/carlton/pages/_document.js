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
