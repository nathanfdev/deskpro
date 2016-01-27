import React from 'react';
import PortalHttp from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export default class LoginForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      failed: false,
      captcha: '',
      captcha_public_key: '6LcWL8YSAAAAAJu1CrtS9RdOJyKd_NbArNgUFWV9'
    };
  }
  submitLogin(e) {
    e.preventDefault();
    const $username = $(this.refs.username);
    const $password = $(this.refs.password);
    const $remember_me = $(this.refs.remember_me);
    const login_url = portalUrlGenerator.path('/login/authenticate-password');

    this.setState({
      failed: false
    });

    PortalHttp.sendPost(login_url, {
      username: $username.val(),
      password: $password.val(),
      remember_me: $remember_me.val()
    }, {jsonPayload: false}).then((r) => {
      console.log('RESPONSE %o', r);
      if (r.data.success) {
        if ("redirect" in r.data) {
          window.location.href = r.data.redirect;
        } else {
          window.location.reload();
        }
      } else {
        this.setState({
          failed: true
        });
        $username.focus();
        this.addCaptchaIfNecessary();
      }
    });
  }
  addCaptchaIfNecessary() {
    PortalHttp.sendGet(portalUrlGenerator.path('/captcha-html?action=login')).then((r) => {
      console.log('CAPTCHA RESPONSE ', r);
        if (r.data.captcha_required) {
          // for now we are not displaying the captcha, and instead are just redirecting the user to login page
          window.location.href = portalUrlGenerator.path('/login');
          //this.setState({
          //  captcha: true
          //});
        } else {
          this.setState({
            captcha: false
          });
        }
    });
  }
  render() {
    const failure_path = portalUrlGenerator.path('/login?retry=auth');

    //if (this.state.captcha) {
    //  window.RecaptchaOptions = { theme: 'clean' };
    //}

    return (
      <form method="post" id="login-sidebar" onSubmit={this.submitLogin.bind(this)}>
        <input
          type="hidden"
          name="_failure_path"
          value={failure_path}
          />
        <label className={this.state.failed ? "error" : null}>
          <span>Your email</span>
          <input
            ref="username"
            type="text"
            tabIndex="2"
            placeholder="email@example.com"
            name="username"
            />
        </label>

        <label className={this.state.failed ? "error" : null}>
          {this.state.failed ? (<div className="message">{portalPhrases.get('portal.account.login-invalid')}</div>) : null}
          <span>Your password</span>
          <input
            ref="password"
            type="password"
            tabIndex="2"
            placeholder={portalPhrases.get('portal.account.login-password')}
            name="password"
            />
        </label>

        {/*<label className="error">
        //  <div className="message">This is a password error</div>
        //  <span>Your password</span>
        //  <input type="password" tabIndex="2" placeholder="Your password"
        //         name="password" />
        //</label>*/}

        <div className="permanent-login">
          <label>
            <input
              ref="remember_me"
              tabIndex="2"
              type="checkbox"
              name="remember_me"
              />
            Stay Logged In?
          </label>
        </div>

        <button type="submit" tabIndex="2">Login</button>

        <div className="secondary-action">
          <a href={portalUrlGenerator.path('/login/reset-password')}>{portalPhrases.get('portal.account.login-password-reminder')}</a>
        </div>
      </form>
    );
  }
}
