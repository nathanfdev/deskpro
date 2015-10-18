import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { LoginForm } from './Form/LoginForm';

@connect(state => ({
  loginState: state.Login.login
}))
export class LoginApp extends React.Component {

  static propTypes = {
    history: PropTypes.object.isRequired,
    loginState: PropTypes.object.isRequired
  };

  render() {
    const { loginState, history } = this.props;
    if (loginState.get('hasAuth')) {
      history.pushState(null, '/index.php/agent/');
    }

    return (
      <LoginForm />
    );
  }
}
