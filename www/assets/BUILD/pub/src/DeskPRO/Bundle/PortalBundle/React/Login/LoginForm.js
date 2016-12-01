import React from 'react';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import classNames from 'classnames';
import $ from 'jquery';

export class LoginForm extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      failed:             false,
      captcha:            '',
      captcha_public_key: '6LcWL8YSAAAAAJu1CrtS9RdOJyKd_NbArNgUFWV9',
      reset_path:         portalUrlGenerator.path('/login/reset-password'),
      reason:             null
    };
  }

  onSubmit = (event) => {
    event.preventDefault();

    const $username = $(this.refUsername);
    const $password = $(this.refPassword);
    const $rememberMe = $(this.refRememberMe);
    const loginUrl = portalUrlGenerator.path('/login/authenticate-password');

    this.onResetFailed();

    portalHttp.sendPost(
      loginUrl,
      {
        username:    $username.val(),
        password:    $password.val(),
        remember_me: $rememberMe.val()
      },
      {
        jsonPayload: false
      }
    ).then((r) => {
      if (r.data.success) {
        if ('redirect' in r.data) {
          window.location.href = r.data.redirect;
        } else {
          window.location.reload();
        }
      } else {
        this.setState({
          failed: true,
          reason: r.data.reason
        });
        $username.focus();
        this.addCaptchaIfNecessary();
      }
    });
  };

  onEmailBlur = () => {
    const $username = $(this.refUsername);

    if (this.refUsername && $username.val()) {
      this.setState({
        reset_path: `${portalUrlGenerator.path('/login/reset-password')}?email=${$username.val()}`
      });
    } else {
      this.setState({
        reset_path: portalUrlGenerator.path('/login/reset-password')
      });
    }
  };

  onResetFailed = () => {
    this.setState({
      failed: false,
      reason: null
    });
  };

  addCaptchaIfNecessary() {
    portalHttp.sendGet(portalUrlGenerator.path('/captcha-html?action=login')).then((r) => {
      if (r.data.captcha_required) {
        // for now we are not displaying the captcha, and instead are just redirecting the user to login page
        window.location.href = portalUrlGenerator.path('/login');
      } else {
        this.setState({
          captcha: false
        });
      }
    });
  }

  renderFailedReason() {
    if (!this.state.failed) {
      return null;
    }
    const phrase = this.state.reason || 'portal.account.login-invalid';
    return <div className="message">{portalPhrases.get(phrase)}</div>;
  }

  render() {
    const failurePath = portalUrlGenerator.path('/login?retry=auth');

    return (
      <form method="post" id="login-sidebar" onSubmit={this.onSubmit}>
        <input type="hidden" name="_failure_path" value={failurePath} />
        <label className={classNames({ error: this.state.failed })} htmlFor="login-form-username">
          <span>{portalPhrases.get('portal.account.login-email')}</span>
          <input
            ref={(node) => { this.refUsername = node; }}
            type="text"
            placeholder="email@example.com"
            name="username"
            onBlur={this.onEmailBlur}
            onChange={this.onResetFailed}
            id="login-form-username"
            tabIndex="-1"
          />
        </label>

        <label className={classNames({ error: this.state.failed })} htmlFor="login-form-password">
          {this.renderFailedReason()}
          <span>{portalPhrases.get('portal.account.login-password')}</span>
          <input
            ref={(node) => { this.refPassword = node; }}
            type="password"
            placeholder={portalPhrases.get('portal.account.login-password')}
            name="password"
            onChange={this.onResetFailed}
            id="login-form-password"
            tabIndex="-1"
          />
        </label>

        <div className="permanent-login">
          <label htmlFor="login-form-remember-me">
            <input
              ref={(node) => { this.refRememberMe = node; }}
              type="checkbox"
              name="remember_me"
              id="login-form-remember-me"
              tabIndex="-1"
            />
            {portalPhrases.get('portal.account.login-stay-logged-in')}
          </label>
        </div>

        <button type="submit">{portalPhrases.get('portal.account.login-btn')}</button>

        {window.DESKPRO_IS_FORGOT_PASSWORD_VISIBLE &&
          <div className="secondary-action">
            <a href={this.state.reset_path}>
              {portalPhrases.get('portal.account.login-password-reminder')}
            </a>
          </div>}
      </form>
    );
  }
}
