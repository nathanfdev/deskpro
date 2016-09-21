import React, { PropTypes } from 'react';
import { FormattedMessage } from 'react-intl';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Field, Form, Input } from 'DeskPRO/Component/Semantic/Form';

export class ForgottenPasswordContainer extends React.Component {
  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  onBackToLogin = () => {
    this.context.router.push('/login');
  };

  render() {
    return (
      <ForgottenPassword
        onBackToLogin={this.onBackToLogin}
      />
    );
  }
}
export class ForgottenPassword extends React.Component {
  static propTypes = {
    onBackToLogin:       PropTypes.func,
    onEmailInstructions: PropTypes.func
  };

  render() {
    return (
      <Segment className="forgotten-password">
        <h3>
          <FormattedMessage
            id="cloud.demo_expired.forgot_password_title"
            defaultMessage="Forgotten your password?"
          />
        </h3>
        <p>
          <FormattedMessage
            id="cloud.demo_expired.forgot_password_desc"
            defaultMessage="Enter your email address to receive instructions on how to reset your password:"
          />
        </p>
        <Form>
          <Field>
            <label htmlFor="username">
              <FormattedMessage
                id="cloud.demo_expired.email"
                defaultMessage="Email"
              />
            </label>
            <Input id="username" icon="mail" iconPosition="left" />
          </Field>
        </Form>
        <Button onClick={this.props.onEmailInstructions}>
          <FormattedMessage
            id="cloud.demo_expired.email_instructions"
            defaultMessage="Email instructions"
          />
        </Button>
        <p><a onClick={this.props.onBackToLogin}>
          <FormattedMessage
            id="cloud.demo_expired.back_to_login"
            defaultMessage="Back to login form"
          />
        </a></p>
      </Segment>
    );
  }
}
