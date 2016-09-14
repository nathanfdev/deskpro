import React, { PropTypes } from 'react';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Field, Form, InputText } from 'DeskPRO/Component/Semantic/Form';

export class ForgottenPasswordContainer extends React.Component {
  render() {
    return <ForgottenPassword />;
  }
}
export class ForgottenPassword extends React.Component {
  static propTypes = {
    onBackToLogin: PropTypes.func
  };

  render() {
    return (
      <Segment classes="forgotten-password">
        <h3>Forgotten your password?</h3>
        <p>Enter your email address to receive instructions on how to reset your password:</p>
        <Form>
          <Field>
            <label htmlFor="username">Email</label>
            <InputText id="username" icon="mail" iconPosition="left" />
          </Field>
          <Button>Email instructions</Button>
        </Form>
        <p><a onClick={this.props.onBackToLogin}>Back to login form</a></p>
      </Segment>
    );
  }
}
