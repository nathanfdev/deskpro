import React from 'react';

export class Options extends React.Component {

  render() {
    return (
      <div className="dpw-login-form-options">
        <a href="#" className="password-reminder">Forgotten your password?</a>

        <span className="dpw-login-form-remember-me">
          <span className="dpw--checkbox-boxy"><i className="fa fa-check"></i></span>
          <span>Remember me</span>
        </span>
      </div>
    );
  }
}
