import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { LoginForm } from './LoginForm';

@connect()
export class LoginFormContainer extends React.Component {

  render() {
    return (
      <LoginForm {...this.props} />
    );
  }
}
