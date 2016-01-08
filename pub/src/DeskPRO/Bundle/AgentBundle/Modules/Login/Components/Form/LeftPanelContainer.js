import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { login } from '../../Actions/loginActions';
import { LoginFormHeader } from './LoginFormHeader';
import { Email } from './Fields/Email';
import { Password } from './Fields/Password';
import { Options } from './Fields/Options';
import { WarningMajorWrapper } from './WarningMajor/WarningMajorWrapper';
import classNames from 'classnames';

@connect()
export class LeftPanelContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      email: null,
      password: null,
      rememberMe: null,
      submit: false,
      errors: null
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

  onSubmitForm = (event) => {
    event.preventDefault();
    if (this.state.submit) {
      return;
    }

    const { dispatch } = this.props;
    const promise = dispatch(login({
      email: this.state.email,
      password: this.state.password
    }));

    this.setState({
      submit: true
    });

    promise.then(
      () => {
        this.setState({
          submit: false
        });
      },
      response => {
        this.setState({
          submit: false,
          errors: response.getData().errors
        });
      }
    );
  };

  render() {
    return (
      <div className="left-panel">
        <div className={classNames('dpw-login', {'error': !!this.state.errors})}>

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
                     className={classNames({'locked': this.state.submit})}
                     onClick={this.onSubmitForm} />

              {this.state.submit && <div className="dpw-spinner"><i/></div>}
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
