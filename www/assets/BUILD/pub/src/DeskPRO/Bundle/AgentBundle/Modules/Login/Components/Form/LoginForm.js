import PropTypes from 'prop-types';
import React from 'react';
import { LoginFormHeader } from './LoginFormHeader';
import { Email } from './Fields/Email';
import { Password } from './Fields/Password';
import { Options } from './Fields/Options';
import { WarningMajorWrapper } from './WarningMajor/WarningMajorWrapper';
import { login } from '../../Actions/loginActions';
import classNames from 'classnames';

export class LoginForm extends React.Component {

  static propTypes = {
    dispatch:     PropTypes.func.isRequired,
    submit:       PropTypes.bool,
    errors:       PropTypes.object,
    onSubmitForm: PropTypes.func
  };

  constructor(props) {
    super(props);

    this.state = {
      email:      null,
      password:   null,
      rememberMe: null,
      errors:     null
    };
  }

  componentDidMount() {
    this.mounted = true;
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  onChangeEmail = event => {
    this.setState({
      email:  event.target.value,
      errors: null
    });
  };

  onChangePassword = event => {
    this.setState({
      password: event.target.value,
      errors:   null
    });
  };

  onChangeRememberMe = () => {
    this.setState({
      rememberMe: !this.state.rememberMe,
      errors:     null
    });
  };

  onSubmitForm = event => {
    event.preventDefault();
    if (this.state.submit) {
      return;
    }

    const { dispatch } = this.props;
    const promise = dispatch(login({
      email:    this.state.email,
      password: this.state.password
    }));

    this.setState({
      submit: true
    });

    promise.then(
      () => {
        if (this.mounted) {
          this.setState({
            submit: false
          });
        }
      },
      response => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: response.getData().errors
          });
        }
      }
    );
  };

  render() {
    return (
      <div className={classNames('dpw-login', { 'error': !!this.state.errors })}>

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
            <Email value={this.state.email} errors={this.state.errors} onChange={this.onChangeEmail} />
            <Password value={this.state.password} errors={this.state.errors} onChange={this.onChangePassword} />
            <Options checked={this.state.rememberMe} onChange={this.onChangeRememberMe} />

            <input type="submit"
              value="Log in to DeskPRO"
              className={classNames({ 'locked': this.state.submit })}
              onClick={this.onSubmitForm}
            />

            {this.state.submit && <div className="dpw-spinner"><i /></div>}
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
