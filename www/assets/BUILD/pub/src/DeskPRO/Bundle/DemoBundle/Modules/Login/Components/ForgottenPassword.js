import React, { PropTypes } from 'react';
import { injectIntl, FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Field, Form, Input } from 'DeskPRO/Component/Semantic/Form';
import * as actions from '../Actions/loginActions';

@connect()
export class ForgottenPasswordContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
  };

  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      email:  '',
      submit: false,
      errors: null
    };
  }

  onChangeEmail = (event) => {
    this.setState({
      email:  event.target.value,
      errors: null
    });
  };

  onBackToLogin = () => {
    this.context.router.push('/login');
  };

  onEmailInstructions = () => {
    const { dispatch } = this.props;
    this.setState({
      submit: true
    });

    const promise = dispatch(actions.forgotPassword({
      email: this.state.email
    }));

    promise.then(
      () => {

      },
      (response) => {
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
      <ForgottenPassword
        onBackToLogin={this.onBackToLogin}
        onEmailInstructions={this.onEmailInstructions}
      />
    );
  }
}

@injectIntl
export class ForgottenPassword extends React.Component {
  static propTypes = {
    email:               PropTypes.string,
    submit:              PropTypes.bool,
    errors:              PropTypes.object,
    onBackToLogin:       PropTypes.func,
    onChangeEmail:       PropTypes.func,
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
        <p className="description">
          <FormattedMessage
            id="cloud.demo_expired.forgot_password_desc"
            defaultMessage="Enter your email address to receive instructions on how to reset your password:"
          />
        </p>
        <Form onSubmit={this.props.onEmailInstructions}>
          <Field>
            <label htmlFor="username">
              <FormattedMessage
                id="cloud.demo_expired.email"
                defaultMessage="Email"
              />
            </label>
            <Input
              id="username"
              icon="mail"
              iconPosition="left"
              type="email"
              errors={this.props.errors}
              value={this.props.email}
              onChange={this.props.onChangeEmail}
            />
          </Field>
        </Form>
        <Button onClick={this.props.onEmailInstructions}>
          <FormattedMessage
            id="cloud.demo_expired.email_instructions"
            defaultMessage="Email instructions"
          />
          {this.props.submit && <span> <i className="fa fa-spinner fa-pulse fa-fw margin-bottom" /></span>}
        </Button>
        <p className="bottom-link">
          <a onClick={this.props.onBackToLogin}>
            <FormattedMessage
              id="cloud.demo_expired.back_to_login"
              defaultMessage="Back to login form"
            />
          </a>
        </p>
      </Segment>
    );
  }
}
