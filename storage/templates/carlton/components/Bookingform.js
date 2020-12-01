import React, { Component, useRef, useState } from 'react';
import Link from 'next/link'
import moment from 'moment';
import 'react-dates/initialize';
import { DateRangePicker } from 'react-dates';
import apiClient from '../services/api.js';

class Bookingform extends Component
{
    constructor(props) {
        super(props);
        this.state = {
          selectedListingId : props.selectedListingIdInit,
          startDate: props.startDateInit ? moment(props.startDateInit) : null,
          endDate: props.endDateInit ? moment(props.endDateInit) : null,
          focusedInput: null,
          calendar: props.calendar ? props.calendar : null,
          isLoadingCalendar: false
        };
    }

    isDayBlocked = momentDate => {

        if (!this.state.calendar) return false

        if(this.state.calendar.includes(momentDate.format('YYYY-MM-DD'))) return true
    }

    getCalendar = (id) => {
      this.setState({isLoadingCalendar: true})

      apiClient.get('site/'+process.env.NEXT_PUBLIC_WEBSITE_API_ID+'/rooms/'+id+'/calendar')
      .then(result => {
        this.setState({calendar: result.data.not_available})
        this.setState({isLoadingCalendar: false})
      })
      .catch(e =>{
        this.setState({isLoadingCalendar: false})
      });
    }

    changeListing = e => {
      this.setState({selectedListingId: e.target.value})
      this.getCalendar(e.target.value)
    }

    render() {
        return (
            <>
            <section className="booking-form">
                <div className="container">
                    <div className="booking-form-inner">
                        <form action="#">
                            <div className="row align-items-end">
                                <div className="col-lg-4">
                                  <select className="select-listing" type="text" onChange={(e) => this.changeListing(e)} required disabled={this.state.isLoadingCalendar}>
                                    <option value="" disabled selected={!this.state.selectedListingId ?? true}>Select a room</option>
                                    {this.props.siteData.listings.map((item, i) => (
                                        <option value={item.id} selected={this.state.selectedListingId && true}>{item.name}</option>
                                    ))}
                                  </select>
                                </div>
                                <div className="col-lg-5">
                                    <div className="inputs-filed mt-30 text-center">
                                        {!this.state.isLoadingCalendar ? (
                                          <DateRangePicker
                                            startDateId="startDate"
                                            endDateId="endDate"
                                            startDate={this.state.startDate}
                                            endDate={this.state.endDate}
                                            onDatesChange={({ startDate, endDate }) => { this.setState({ startDate, endDate })}}
                                            focusedInput={this.state.focusedInput}
                                            onFocusChange={(focusedInput) => { this.setState({ focusedInput })}}
                                            isDayBlocked={this.isDayBlocked}
                                            hideKeyboardShortcutsPanel 
                                            autoFocus 
                                            showClearDates
                                            reopenPickerOnClearDates
                                            showDefaultInputIcon
                                            autoFocus 
                                          />   
                                          ) : 
                                          (
                                          <h2 style={{color:'#bead8e'}}><i className="far fa-spinner fa-spin" /></h2>
                                          )
                                        }

                                    </div>

                                </div>
                                <div className="col-lg-3">
                                    <div className="inputs-filed mt-30" >
                                        {this.props.checkingAvailibility || this.state.isLoadingCalendar ? (
                                            <button className="btn-check-availibility" disabled><i className="far fa-spinner fa-spin" /></button>
                                          ) : 
                                          (
                                            <Link href={this.state.startDate && this.state.endDate ? `/booking?room=${this.state.selectedListingId}&start=${moment(this.state.startDate).format('YYYY-MM-DD')}&end=${moment(this.state.endDate).format('YYYY-MM-DD')}` : '/booking'}><button className="btn-check-availibility" >Check Availibility</button></Link>
                                          )
                                        }
                                        
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
            </>
        );
    }
}

export default Bookingform;