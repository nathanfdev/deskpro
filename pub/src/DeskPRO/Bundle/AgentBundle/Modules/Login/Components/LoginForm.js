import React from 'react';

export class LoginForm extends React.Component {
  render() {
    return (
      <div className="deskpro-loading">

        <div className="deskpro-loading-welcome-back">
          <div className="deskpro-loading-logo">
            <img width="149" height="44" alt="DeskPRO" src="../img/loading/loading-logo.png" srcset="../img/loading/loading-logo-2x.png 2x" />
          </div>
        </div>

          <div className="dpw-login-panels">

            <div className="left-panel">
              <div className="dpw-login">
                <div className="dpw-login-language-controls">
              <span className="dpw-login-language-controls-button">
                <span className="dpw-login-language-controls-flag uk"></span>
                <hr />
                <i className="fa fa-caret-down"></i>
              </span>

            </div>

            <div className="dpw-login-header">
              <div className="dpw-login-header-logo"><img src="../img/samples/sample-logo.png"></div>
                <h1>Log in to Acme Helpdesk</h1>
              </div>

              <div className="dpw-login-form">
                <form>
                  <div className="dpw-login-form-container">
                    <label>Account name / Email</label>
                    <span className="dpw-login-form-input-icon"><i className="fa fa-user"></i></span>
                    <input type="text" placeholder="example@email.com">
                    </div>

                    <div className="dpw-login-form-container password-container">
                      <label>Password</label>
                      <span className="dpw-login-form-input-icon"><i className="fa fa-lock"></i></span>
                      <input type="password">
                      </div>

                      <div className="dpw-login-form-options">
                        <a href="#" className="password-reminder">Forgotten your password?</a>

                      <span className="dpw-login-form-remember-me">
                        <span className="dpw--checkbox-boxy"><i className="fa fa-check"></i></span>
                        <span>Remember me</span>
                      </span>
                        </div>

                        <input type="submit" value="Log in to DeskPRO">
                        </form>
                      </div>
                    </div>

                    <div className="dpw-left-panel-footer">
                      <i className="fa fa-users"></i> <span className="text">No Account?</span> <a href="#">Request from admin</a> <hr> <a href="#">Sign up for free</a>
                    </div>
                    </div>

                    <div className="right-panel">
                      <div className="dpw-login-side">
                        <a href="#" className="dpw-main-banner" style="background-image: url(../img/samples/sample-banner.png);"></a>

                        <div className="dpw-login-side-banners">
                          <div className="left">
                            <a href="#" className="dpw-login-side-small-banner" style="background-image: url(../img/loading/banner-quickstart-guide.png);"></a>
                          </div>

                          <div className="right">
                            <a href="#" className="dpw-login-side-small-banner" style="background-image: url(../img/loading/banner-mobile-apps.png);"></a>
                          </div>

                        </div>
                      </div>
                    </div>

                  </div>

                  <div className="dpw-login-panels-footer">
                    <hr style="width: 20px;" />

                    <span className="meta">
                      <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
                    </span>

                    <hr style="width: 140px;" />

                    <span className="deskpro-mark">
                      <a href="#">
                        <img alt="Deskpro" width="27" height="27" src="../img/loading/footer-deskpro-logo.png" srcset="../img/loading/footer-deskpro-logo-2x.png 2x">
                        <span>Helpdesk software by <strong>DeskPRO</strong></span>
                      </a>
                    </span>

                  <hr style="width: 260px;" />

                  <span className="social-media">
                    <a href="#"><i className="fa fa-facebook"></i></a>
                    <a href="#"><i className="fa fa-twitter"></i></a>
                    <a href="#"><i className="fa fa-google-plus"></i></a>
                  </span>

                  <hr style="width: 20px;" />
          </div>

        </div>
    );
  }
}
