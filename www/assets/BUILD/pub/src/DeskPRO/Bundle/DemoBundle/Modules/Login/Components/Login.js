import React, { PropTypes } from 'react';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Field, Form, Input } from 'DeskPRO/Component/Semantic/Form';
import login from '../Actions/loginActions';

@connect()
export class LoginContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
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

  onForgotPassword = () => {
    this.context.router.push('/forgot-password');
  };

  onLogin = () => {
    const { dispatch } = this.props;
    this.setState({
      submit: true
    });

    const promise = dispatch(login({
      email:    this.form.username.input.value,
      password: this.form.password.input.value
    }));

    promise.then(
      () => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: null
          });
          this.context.router.push('/extend-trial');
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
      <Login
        onChangeEmail={this.onChangeEmail}
        onChangePassword={this.onChangePassword}
        onForgotPassword={this.onForgotPassword}
        onLogin={this.onLogin}
        {...this.state}
        ref={(c) => { this.form = c; }}
      />
    );
  }
}

export class Login extends React.Component {
  static propTypes = {
    submit:           PropTypes.bool,
    errors:           PropTypes.object,
    onChangeEmail:    PropTypes.func,
    onChangePassword: PropTypes.func,
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
              errors={this.props.errors}
              onChange={this.props.onChangeEmail}
              ref={(c) => { this.username = c; }}
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
              errors={this.props.errors}
              onChange={this.props.onChangePassword}
              ref={(c) => { this.password = c; }}
            />
          </Field>
        </Form>
        <Button className="positive" onClick={this.props.onLogin}>
          <FormattedMessage
            id="cloud.demo_expired.login_button"
            defaultMessage="Log in"
          />
          {this.props.submit && <span> <i className="fa fa-spinner fa-pulse fa-fw margin-bottom" /></span>}
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
