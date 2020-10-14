import React, { Component } from 'react';
import { Modal } from 'react-responsive-modal';
import ReactWOW from 'react-wow'

class Videowrap extends Component {
  state = {
    open: false,
    };
    onOpenModal = () => {
    this.setState({ open: true });
    };
    
    onCloseModal = () => {
    this.setState({ open: false });
    };
    render() {
    const { open } = this.state;
    return (
        <section className="video-wrap full-section" style={{backgroundImage: 'url(assets/img/bg/05.jpg)'}}>
            <ReactWOW animation="fadeInDown" data-wow-delay=".3s">
            <div  onClick={this.onOpenModal} className="popup-video">
            <img src="assets/img/icon/07.png" alt="" />
            </div>
            </ReactWOW>
            <Modal open={open} onClose={this.onCloseModal} center classNames={{
                modal: 'video-modal',
                }}>
                <iframe src="https://www.youtube.com/embed/EEJFMdfraVY" title="title"></iframe>
                </Modal>
        </section>
    );
  }
}

export default Videowrap;
