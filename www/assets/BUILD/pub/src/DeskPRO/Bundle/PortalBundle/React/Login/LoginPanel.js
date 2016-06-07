import React from 'react';
import { LoginForm } from './LoginForm';
import { LoginUsersources } from './LoginUsersources';

export class LoginPanel extends React.Component {

  render() {
    return (
      <div>
        {window.DESKPRO_HAS_LOGIN_FORM && <LoginForm />}
        <LoginUsersources {...this.props} />
      </div>
    );
  }
}
