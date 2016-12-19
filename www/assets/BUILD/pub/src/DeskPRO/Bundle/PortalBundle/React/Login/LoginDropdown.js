import React from 'react';
import { LoginPanel } from './LoginPanel';

export class LoginDropdown extends React.Component {

  render() {
    return (
      <div className="active-button-dropdown language-dropdown" id="top-login-dropdown" style={{ minWidth: '300px' }}>
        <div className="small-form">
          <LoginPanel {...this.props} />
        </div>
      </div>
    );
  }
}
