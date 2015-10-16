import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { emailChange, passwordChange, login } from '../../Actions/loginActions';
import { DpLogo } from '../DpLogo';
import { LoginFormHeader } from './LoginFormHeader';
import { Email } from './Fields/Email';
import { Password } from './Fields/Password';
import { Options } from './Fields/Options';
import { LoginFormFooter } from './LoginFormFooter';
import { WarningMajorWrapper } from './WarningMajor/WarningMajorWrapper';
import { NotAgentWarning } from './WarningMajor/NotAgentWarning';
import { TooManyAttempts } from './WarningMajor/TooManyAttempts';
import { WrongHelpdesk } from './WarningMajor/WrongHelpdesk';
import { TimeLocked } from './WarningMajor/TimeLocked';

@connect(state => ({
  loginState: state.Login.login
}))
export class LoginFormContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  onChangeEmail = (event) => {
    this.props.dispatch(emailChange(event.target.value));
  };

  onChangePassword = (event) => {
    this.props.dispatch(passwordChange(event.target.value));
  };

  submitForm = (event) => {
    event.preventDefault();
    this.props.dispatch(login());
  };

  render() {
    const { loginState } = this.props;
    const hasError = true;
    const loginClassNames = ['dpw-login'];
    if (hasError) {
      loginClassNames.push('error');
    }

    return (
      <div className="deskpro-loading">
        <DpLogo />

        <div className="dpw-login-panels">
          <div className="left-panel">
            <div className={loginClassNames.join(' ')}>

              <WarningMajorWrapper>
                <TimeLocked />
              </WarningMajorWrapper>

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
                  <Email value={loginState.get('email')} onChange={this.onChangeEmail} errorMessage="Looks like this isn't the correct password" />
                  <Password value={loginState.get('password')} onChange={this.onChangePassword} errorMessage="Wrong password" />
                  <Options />

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
                  <a href="#" className="dpw-login-form-alternate-login-button more">Other <i className="fa fa-caret-down"></i></a>
                </div>
              </div>

            </div>

            <div className="dpw-left-panel-footer">
              <i className="fa fa-users"></i> <span className="text">No Account?</span> <a href="#">Request from admin</a> <hr /> <a href="#">Sign up for free</a>
            </div>
          </div>

          <div className="right-panel">
            <div className="dpw-login-side">
              <a href="#" className="dpw-main-banner sample-banner"></a>

              <div className="dpw-login-side-banners">
                <div className="left">
                  <a href="#" className="dpw-login-side-small-banner quick-start"></a>
                </div>

                <div className="right">
                  <a href="#" className="dpw-login-side-small-banner mobile-apps"></a>
                </div>

              </div>
            </div>
          </div>
        </div>

        <LoginFormFooter />
      </div>
    );
  }
}
