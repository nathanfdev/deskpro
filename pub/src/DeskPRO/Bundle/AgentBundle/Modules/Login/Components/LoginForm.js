import React from 'react';
import { DpLogo } from './DpLogo';
import { LoginHeader } from './LoginHeader';

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

              <div className="dpw-login-warning-major">
                <h1>Too many login attempts!</h1>
                <p>At your next failed login attempt, your account will be temporarily locked for security. Please check your login details carefully.</p>
              </div>

              <div className="dpw-login-language-controls">
                <span className="dpw-login-language-controls-button">
                  <span className="dpw-login-language-controls-flag uk"></span>
                  <hr />
                  <i className="fa fa-caret-down"></i>
                </span>
              </div>

              <LoginHeader />

              <div className="dpw-login-form">
                <form>
                  <div className="dpw-login-form-container">
                    <label>Account name / Email</label>
                    <span className="dpw-login-form-input-icon"><i className="fa fa-user"></i></span>
                    <input type="text" placeholder="example@email.com" />
                  </div>

                  <div className="dpw-login-form-container password-container">
                    <label>Password</label>
                    <span className="dpw-login-form-input-icon"><i className="fa fa-lock"></i></span>
                    <input type="password" />
                  </div>

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

        <div className="dpw-login-panels-footer">
          <hr style={{width: '20px'}} />

          <span className="meta">
            <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
          </span>

          <hr style={{width: '140px'}} />

          <span className="deskpro-mark">
            <a href="#">
              <span className="logo"></span>
              <span className="title">Helpdesk software by <strong>DeskPRO</strong></span>
            </a>
          </span>

          <hr style={{width: '260px'}} />

          <span className="social-media">
            <a href="#"><i className="fa fa-facebook"></i></a>
            <a href="#"><i className="fa fa-twitter"></i></a>
            <a href="#"><i className="fa fa-google-plus"></i></a>
          </span>

          <hr style={{width: '20px'}} />
        </div>
      </div>
    );
  }
}
