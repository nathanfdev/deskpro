import React from 'react';

export class LoginFormHeader extends React.Component {

  render() {
    const logoUrl = false;

    return (
      <div className="dpw-login-header">
        <div className="dpw-login-header-logo">
          {logoUrl
            ? <img src={logoUrl} />
            : <a href="#">
                <i className="fa fa-arrow-circle-o-up"></i>
                <span>Upload your logo</span>
             </a>
          }
        </div>
        <h1>Log in to Acme Helpdesk</h1>
      </div>
    );
  }
}
