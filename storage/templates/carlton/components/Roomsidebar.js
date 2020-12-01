import React, { Component } from 'react';
import moment from 'moment';
import Link from 'next/link'

class Roomsidebar extends Component {

  constructor(props) {
      super(props);
      this.state = {
        startDate : null,
        endDate: null
      };
  }

  render() {
    return (
      <div className="room-booking-form">
        <h5 className="title">Check Availability</h5>
        <form action="#">
          <div className="input-group input-group-two left-icon mb-20">
            <label htmlFor="arrival-date">Check In</label>
            <input type="date" placeholder="20-6-2020" name="arrival-date" id="arrival-date" onChange={(e) => this.setState({startDate:e.target.value})}/>
          </div>
          <div className="input-group input-group-two left-icon mb-20">
            <label htmlFor="departure-date">Check Out</label>
            <input type="date" placeholder="30-6-2020" name="departure-date" id="departure-date" onChange={(e) => this.setState({endDate:e.target.value})} />
          </div>
          <div className="input-group">
            <Link href={this.state.startDate && this.state.endDate && (this.state.startDate < this.state.endDate) ? `/booking?room=${this.props.roomId}&start=${moment(this.state.startDate).format('YYYY-MM-DD')}&end=${moment(this.state.endDate).format('YYYY-MM-DD')}` : '/booking'}><button className="main-btn btn-filled">check availability</button></Link>
          </div>
        </form>
      </div>
    );
  }
}

export default Roomsidebar;
