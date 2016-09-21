import React, { PropTypes } from 'react';
import { FormattedMessage } from 'react-intl';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Field, Form, Input } from 'DeskPRO/Component/Semantic/Form';

export class LoginContainer extends React.Component {
  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  onForgotPassword = () => {
    this.context.router.push('/forgot-password');
  };

  onLogin = () => {
    // Check credential via API then
    this.context.router.push('/extend-trial');
  };

  render() {
    return (
      <Login
        onForgotPassword={this.onForgotPassword}
        onLogin={this.onLogin}
      />
    );
  }
}
export class Login extends React.Component {
  static propTypes = {
    onForgotPassword: PropTypes.func,
    onLogin:          PropTypes.func
  };

  render() {
    return (
      <Segment className="login">
        <h3>
          <FormattedMessage
            id="cloud.demo_expired.login_title"
            defaultMessage="Your free trial has ended"
          />
        </h3>
        <p>
          <FormattedMessage
            id="cloud.demo_expired.login_desc"
            defaultMessage="Log in to find out how you can extend your DeskPRO trial by 7 days."
          />
        </p>
        <Form>
          <Field>
            <label htmlFor="username">
              <FormattedMessage
                id="cloud.demo_expired.username_label"
                defaultMessage="Email / Username"
              />
            </label>
            <Input id="username" icon="mail" iconPosition="left" />
          </Field>
          <Field>
            <label htmlFor="password">
              <FormattedMessage
                id="cloud.demo_expired.password_label"
                defaultMessage="Password"
              />
            </label>
            <Input id="password" icon="lock" iconPosition="left" />
          </Field>
        </Form>
        <Button className="positive" onClick={this.props.onLogin}>
          <FormattedMessage
            id="cloud.demo_expired.login_button"
            defaultMessage="Log in"
          />
        </Button>
        <p><a onClick={this.props.onForgotPassword}>
          <FormattedMessage
            id="cloud.demo_expired.forgot_password"
            defaultMessage="Forgot your password?"
          />
        </a></p>

      </Segment>
    );
  }
}
