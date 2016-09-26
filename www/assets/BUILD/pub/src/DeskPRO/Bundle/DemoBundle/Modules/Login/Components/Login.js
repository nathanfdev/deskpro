import React, { PropTypes } from 'react';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Field, Form, Input } from 'DeskPRO/Component/Semantic/Form';

export class LoginContainer extends React.Component {
  render() {
    return <Login />;
  }
}
export class Login extends React.Component {
  static propTypes = {
    onForgotPassword: PropTypes.func,
    onLogin:          PropTypes.func
  };

  render() {
    return (
      <Segment classes="login">
        <h3>Your free trial has ended</h3>
        <p>Log in to find out how you can extend your DeskPRO trial by 7 days.</p>
        <Form>
          <Field>
            <label htmlFor="username">Email / Username</label>
            <Input id="username" icon="mail" iconPosition="left" />
          </Field>
          <Field>
            <label htmlFor="password">Password</label>
            <Input id="password" icon="lock" iconPosition="left" />
          </Field>
        </Form>
        <Button classes="positive" onClick={this.props.onLogin}>Log in</Button>
        <p><a onClick={this.props.onForgotPassword}>Forgot your password?</a></p>

      </Segment>
    );
  }
}
