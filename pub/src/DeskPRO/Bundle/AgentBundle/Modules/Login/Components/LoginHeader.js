import React from 'react';

export class LoginHeader extends React.Component {
  render() {
    return (
      <div className="dpw-login-header">
        <div className="dpw-login-header-logo">
          <img src="../../img/samples/sample-logo.png" />
        </div>
        <h1>Log in to Acme Helpdesk</h1>
      </div>
    );
  }
}
