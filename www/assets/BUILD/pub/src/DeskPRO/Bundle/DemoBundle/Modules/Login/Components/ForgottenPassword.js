import React, { PropTypes } from 'react';
import { injectIntl, FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { Fieldset } from 'react-forms';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Input, Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import * as actions from '../Actions/extendActions';

@connect()
export class ForgottenPasswordContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  onSubmit = data => this.props.dispatch(actions.forgotPassword(data));
  onBackToLogin = () => {
    this.context.router.push('/login');
  };

  render() {
    return (
      <ForgottenPassword
        onBackToLogin={this.onBackToLogin}
        onSubmit={this.onSubmit}
      />
    );
  }
}

@injectIntl
export class ForgottenPassword extends BaseForm {

  static propTypes = {
    submit:        PropTypes.bool,
    onBackToLogin: PropTypes.func,
    onChangeEmail: PropTypes.func,
    onSubmit:      PropTypes.func
  };

  getDefaultState() { // eslint-disable-line
    return { email: '' };
  }

  render() {
    const { formData, saving } = this.state;
    const { onBackToLogin } = this.props;

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
            defaultMessage="Enter your email address to receive a new password:"
          />
        </p>
        <Form onSubmit={this.onSubmit} formValue={formData}>
          <Fieldset>
            <Field select="email" label="Email">
              <Input id="username" type="email" icon="mail" iconPosition="left" />
            </Field>
          </Fieldset>
          <button className={classNames('ui button', { loading: saving })}>
            <FormattedMessage
              id="cloud.demo_expired.email_instructions"
              defaultMessage="Email me a new password"
            />
          </button>
        </Form>
        <p className="bottom-link">
          <a onClick={onBackToLogin}>
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
