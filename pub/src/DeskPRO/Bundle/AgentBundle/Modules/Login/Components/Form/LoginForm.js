import React from 'react';
import { DpLogo } from '../DpLogo';
import { LoginFormHeader } from './LoginFormHeader';
import { LoginFormField } from './LoginFormField';
import { Password } from './Password';
import { LoginFormFooter } from './LoginFormFooter';
import { WarningMajor } from './WarningMajor';

export class LoginForm extends React.Component {

  render() {
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

              <WarningMajor />

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
                  <LoginFormField iconClass="fa-user" label="Account name / Email">
                    <input type="text" placeholder="example@email.com" />
                  </LoginFormField>

                  <Password />

                  <div className="dpw-login-form-options">
                    <a href="#" className="password-reminder">Forgotten your password?</a>

                    <span className="dpw-login-form-remember-me">
                      <span className="dpw--checkbox-boxy"><i className="fa fa-check"></i></span>
                      <span>Remember me</span>
                    </span>
                  </div>

                  <input type="submit" value="Log in to DeskPRO" />
                </form>
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
