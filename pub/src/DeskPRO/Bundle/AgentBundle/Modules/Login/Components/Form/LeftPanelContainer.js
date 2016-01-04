import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import * as loginActions from '../../Actions/loginActions';
import { LoginFormHeader } from './LoginFormHeader';
import { Email } from './Fields/Email';
import { Password } from './Fields/Password';
import { Options } from './Fields/Options';
import { WarningMajorWrapper } from './WarningMajor/WarningMajorWrapper';
import classNames from 'classnames';

@connect(state => ({
  loginState: state.Login.login
}))
export class LeftPanelContainer extends React.Component {

  static propTypes = {
    loginState: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      email: null,
      password: null,
      rememberMe: null
    };
  }

  onChangeEmail = (event) => {
    this.resetErrorWarnings();
    this.setState({
      email: event.target.value
    });
  };

  onChangePassword = (event) => {
    this.resetErrorWarnings();
    this.setState({
      password: event.target.value
    });
  };

  onChangeRememberMe = () => {
    this.setState({
      rememberMe: !this.state.rememberMe
    });
  };

  hasError() {
    const { loginState } = this.props;
    return loginState.get('emailError') || loginState.get('passwordError');
  }

  resetErrorWarnings() {
    if (!this.hasError()) {
      return;
    }

    const { dispatch } = this.props;

    dispatch(loginActions.emailSetError(null));
    dispatch(loginActions.passwordSetError(null));
  }

  submitForm = (event) => {
    event.preventDefault();

    const { dispatch } = this.props;
    const email = this.state.email;
    const password = this.state.password;

    if (!email) {
      dispatch(loginActions.emailSetError('Email is empty'));
    }
    if (!password) {
      dispatch(loginActions.passwordSetError('Password is empty'));
    }

    if (email && password) {
      dispatch(loginActions.login({
        email: email,
        password: password
      }));
    }
  };

  render() {
    const { loginState } = this.props;

    return (
      <div className="left-panel">
        <div className={classNames('dpw-login', {'error': this.hasError()})}>

          <WarningMajorWrapper />

          <div className="dpw-login-language-controls">
                <span className="dpw-login-language-controls-button">
                  <span className="dpw-login-language-controls-flag uk"></span>
                  <hr />
                  <i className="fa fa-caret-down"></i>
                </span>
          </div>

          <LoginFormHeader />

          <div className="dpw-login-form">
            <form>
              <Email value={this.state.email}
                     errorMessage={loginState.get('emailError')}
                     onChange={this.onChangeEmail} />

              <Password value={this.state.password}
                        errorMessage={loginState.get('passwordError')}
                        onChange={this.onChangePassword} />

              <Options checked={this.state.rememberMe}
                       onChange={this.onChangeRememberMe} />

              <input type="submit" value="Log in to DeskPRO" onClick={this.submitForm} />
            </form>
          </div>

          <div className="dpw-login-form-divider">
            <span className="dpw-login-form-divider-title">Or Log in with</span>
            <hr />
          </div>

          <div className="dpw-login-form-alternate-login">
            <div className="dpw-login-form-main-button">
              <a href="#" className="dpw-login-form-alternate-login-button onelogin">&nbsp;</a>
            </div>

            <div className="dpw-login-form-extra-buttons">
              <a href="#" className="dpw-login-form-alternate-login-button more">
                Other <i className="fa fa-caret-down"></i>
              </a>
            </div>
          </div>

        </div>

        <div className="dpw-left-panel-footer">
          <i className="fa fa-users"></i>
          <span className="text">No Account?</span>
          <a href="#">Request from admin</a>
          <hr />
          <a href="https://www.deskpro.com/signup/" target="_blank">Sign up for free</a>
        </div>
      </div>
    );
  }
}
