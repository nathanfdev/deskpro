import React from 'react';

export class LoginFormFooter extends React.Component {

  render() {
    return (
      <div className="dpw-login-panels-footer">
        <hr style={{width: '20px'}} />

          <span className="meta">
            <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
          </span>

        <hr style={{width: '140px'}} />

          <span className="deskpro-mark">
            <a href="https://www.deskpro.com/" target="_blank">
              <span className="logo"></span>
              <span className="title">Helpdesk software by <strong>DeskPRO</strong></span>
            </a>
          </span>

        <hr style={{width: '260px'}} />

          <span className="social-media">
            <a href="#"><i className="fa fa-facebook"></i></a>
            <a href="#"><i className="fa fa-twitter"></i></a>
            <a href="#"><i className="fa fa-google-plus"></i></a>
          </span>

        <hr style={{width: '20px'}} />
      </div>
    );
  }
}
