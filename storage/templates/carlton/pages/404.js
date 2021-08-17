import React, { Component } from "react";
import router from "next/router";

export default class Error404 extends Component {
  componentDidMount = () => {
    window.location.href = "/"
  };

  render() {
    return <div />;
  }
}
