import React from 'react';
import { LoginForm } from './LoginForm';
import { LoginUsersources } from './LoginUsersources';

export class LoginPanel extends React.Component {

  render() {
    return (
      <div>
        <LoginForm />
        <LoginUsersources {...this.props} />
      </div>
    );
  }
}
