import React, { Component } from 'react';

class Preloader extends Component {
  constructor(props) {
    super(props);
  }

  render() {
    return (
      <div id="page-transition" className="loader-css align-items-center justify-content-center ">
        <div className="cssload-container">
          <div className="cssload-loading"><i /><i /><i /><i /></div>
        </div>
      </div>
    );
  }
}

export default Preloader;
