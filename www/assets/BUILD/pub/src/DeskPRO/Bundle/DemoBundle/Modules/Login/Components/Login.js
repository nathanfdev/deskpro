import PropTypes from 'prop-types';
import React from 'react';
import { defineMessages, injectIntl, intlShape, FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { Message } from 'DeskPRO/Component/Semantic/Message';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Field, Form, Input } from 'DeskPRO/Component/Semantic/Form';
import login from '../Actions/loginActions';

const messages = defineMessages({
  billing_credentials_error: {
    id:             'cloud.demo_expired.billing_credentials_error',
    defaultMessage: 'You need billing credentials to extend your demo'
  },
  credentials_error: {
    id:             'cloud.demo_expired.credentials_error',
    defaultMessage: 'Your email address or password is incorrect. Please try again.'
  }
});

@connect(state => ({
  me: meSelector(state)
}))
@injectIntl
export class LoginContainer extends React.Component {
  static propTypes = {
    intl:     intlShape.isRequired,
    me:       PropTypes.object,
    dispatch: PropTypes.func.isRequired
  };

  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      email:    '',
      password: '',
      submit:   false,
      errors:   null
    };
  }

  componentWillMount() {
    if (this.props.me && this.props.me.get('can_billing')) {
      this.context.router.push('/extend-trial');
    }
  }

  componentDidMount() {
    this.mounted = true;
  }

  componentWillReceiveProps = (newProps) => {
    if (newProps.me && newProps.me.get('can_billing')) {
      this.context.router.push('/extend-trial');
    }
  };

  componentWillUnmount() {
    this.mounted = false;
  }

  onChangeEmail = (value) => {
    this.setState({
      email:  value,
      errors: null
    });
  };

  onChangePassword = (value) => {
    this.setState({
      password: value,
      errors:   null
    });
  };

  onForgotPassword = () => {
    this.context.router.push('/forgot-password');
  };

  onLogin = () => {
    const { dispatch } = this.props;
    const { formatMessage } = this.props.intl;
    this.setState({
      submit: true
    });

    const promise = dispatch(login({
      email:    this.state.email,
      password: this.state.password
    }));

    promise.then(
      () => {
        if (this.mounted) {
          if (!this.props.me.get('can_billing')) {
            this.setState({
              submit: false,
              errors: { message: formatMessage(messages.billing_credentials_error) }
            });
          } else {
            this.setState({
              submit: false,
              errors: null
            });
          }
        }
      },
      () => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: { message: formatMessage(messages.credentials_error) }
          });
        }
      }
    );
  };

  render() {
    return (
      <Login
        onChangeEmail={this.onChangeEmail}
        onChangePassword={this.onChangePassword}
        onForgotPassword={this.onForgotPassword}
        onLogin={this.onLogin}
        {...this.state}
      />
    );
  }
}

@injectIntl
export class Login extends React.Component {
  static propTypes = {
    email:            PropTypes.string,
    password:         PropTypes.string,
    submit:           PropTypes.bool,
    errors:           PropTypes.object,
    onChangeEmail:    PropTypes.func,
    onChangePassword: PropTypes.func,
    onForgotPassword: PropTypes.func,
    onLogin:          PropTypes.func
  };

  getError = () => {
    if (this.props.errors && this.props.errors.message) {
      return (
        <Message className="negative">
          {this.props.errors.message}
        </Message>
      );
    }
    return null;
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
        <p className="description">
          <FormattedMessage
            id="cloud.demo_expired.login_desc"
            defaultMessage="Log in to find out how you can extend your DeskPRO trial by 14 days."
          />
        </p>
        {this.getError()}
        <Form onSubmit={this.props.onLogin}>
          <Field field="email" errors={this.props.errors}>
            <label htmlFor="email">
              <FormattedMessage
                id="cloud.demo_expired.username_label"
                defaultMessage="Email / Username"
              />
            </label>
            <Input
              id="email"
              icon="mail"
              iconPosition="left"
              value={this.props.email}
              onChange={this.props.onChangeEmail}
              onEnterKey={this.props.onLogin}
            />
          </Field>
          <Field field="password" errors={this.props.errors}>
            <label htmlFor="password">
              <FormattedMessage
                id="cloud.demo_expired.password_label"
                defaultMessage="Password"
              />
            </label>
            <Input
              id="password"
              icon="lock"
              type="password"
              iconPosition="left"
              value={this.props.password}
              onChange={this.props.onChangePassword}
              onEnterKey={this.props.onLogin}
            />
          </Field>
        </Form>
        <Button className={classNames('positive', { loading: this.props.submit })} onClick={this.props.onLogin}>
          <FormattedMessage
            id="cloud.demo_expired.login_button"
            defaultMessage="Log in"
          />
        </Button>
        <p className="bottom-link"><a onClick={this.props.onForgotPassword}>
          <FormattedMessage
            id="cloud.demo_expired.forgot_password"
            defaultMessage="Forgot your password?"
          />
        </a></p>

      </Segment>
    );
  }
}
