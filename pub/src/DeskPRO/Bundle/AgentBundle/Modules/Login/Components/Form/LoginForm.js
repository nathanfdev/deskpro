import React, { PropTypes } from 'react';
import { LoginFormHeader } from './LoginFormHeader';
import { Email } from './Fields/Email';
import { Password } from './Fields/Password';
import { Options } from './Fields/Options';
import { WarningMajorWrapper } from './WarningMajor/WarningMajorWrapper';
import classNames from 'classnames';

export class LoginForm extends React.Component {

  static propTypes = {
    submit: PropTypes.bool,
    errors: PropTypes.object,
    onSubmitForm: PropTypes.func
  };

  constructor(props) {
    super(props);

    this.state = {
      email: null,
      password: null,
      rememberMe: null
    };
  }

  onChangeEmail = event => {
    this.setState({
      email: event.target.value,
      errors: null
    });
  };

  onChangePassword = event => {
    this.setState({
      password: event.target.value,
      errors: null
    });
  };

  onChangeRememberMe = () => {
    this.setState({
      rememberMe: !this.state.rememberMe,
      errors: null
    });
  };

  onSubmitForm = event => {
    event.preventDefault();

    this.props.onSubmitForm({
      email: this.state.email,
      password: this.state.password
    });
  };

  render() {
    const { errors, submit } = this.props;

    return (
      <div className={classNames('dpw-login', {'error': !!errors})}>

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
            <Email value={this.state.email} errors={errors} onChange={this.onChangeEmail} />
            <Password value={this.state.password} errors={errors} onChange={this.onChangePassword} />
            <Options checked={this.state.rememberMe} onChange={this.onChangeRememberMe} />

            <input type="submit"
                   value="Log in to DeskPRO"
                   className={classNames({'locked': submit})}
                   onClick={this.onSubmitForm} />

            {submit && <div className="dpw-spinner"><i/></div>}
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
    );
  }
}
