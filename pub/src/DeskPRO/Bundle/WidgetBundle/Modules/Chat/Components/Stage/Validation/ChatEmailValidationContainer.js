import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import { validateEmail, regenerateEmailValidationCode } from '../../../Actions/chatActions';
import { chatIdSelector } from '../../../Selectors/chat';
import { FieldErrors, hasErrors } from 'DeskPRO/Component/Form/FormErrors';
import classNames from 'classnames';
import $ from 'jquery';
import { history } from '../../../../../Services/history';

@connect(state => ({
  chatId: chatIdSelector(state)
}))
export class ChatEmailValidationContainer extends React.Component {

  static propTypes = {
    chatId: PropTypes.number,
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      code: '',
      submit: false,
      errors: null,
      anotherEmailSent: false
    };
  }

  componentDidMount() {
    this.mounted = true;

    const inputNode = ReactDOM.findDOMNode(this.refs.input);
    $(inputNode).focus();
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  onChangeCode = event => {
    this.setState({
      code: event.target.value,
      errors: null
    });
  };

  onRegenerateCode = event => {
    event.preventDefault();
    this.setState({
      errors: null,
      submit: true
    });

    const { chatId, dispatch } = this.props;
    const promise = dispatch(regenerateEmailValidationCode(chatId, {code: this.state.code}));
    promise.then(
      () => {
        if (this.mounted) {
          this.setState({
            submit: false,
            anotherEmailSent: true
          });
        }
      },
      result => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: result.getData(),
            anotherEmailSent: false
          });
        }
      }
    );
  };

  onSubmit = event => {
    event.preventDefault();
    this.setState({
      errors: null,
      submit: true
    });

    const { chatId, dispatch } = this.props;
    const promise = dispatch(validateEmail(chatId, {code: this.state.code}));
    promise.then(
      () => {
        if (this.mounted) {
          this.setState({
            submit: false,
            anotherEmailSent: false
          });

          history.replace('/chat/waiting');
        }
      },
      result => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: result.getData(),
            anotherEmailSent: false
          });
        }
      }
    );
  };

  render() {
    const hasError = hasErrors(this.state.errors, 'code');

    return (
      <div className={classNames('dpdesignportal-chat-email-validation', {'error': hasError})}>
        <span className="description">
          <p>We require you to validate your email address.</p>
          <br/>
          <p>We sent you an email with a validation code.</p>
          <p>Check your email then enter the code below</p>
        </span>

        <form onSubmit={this.onSubmit}>
          <input type="text" ref="input" onChange={this.onChangeCode} value={this.state.code} />
          {hasError && <FieldErrors errors={this.state.errors} name="code" />}

          {this.state.submit
            ? <div className="spinner"><i/></div>
            :
              <span>
                <a href="#" className="email-code-submit" onClick={this.onSubmit}>
                  Start Chat <i className="fa fa-chevron-right"></i>
                </a>
                <a href="#" className="email-code-resend" onClick={this.onRegenerateCode}>Send me another email</a>
                {this.state.anotherEmailSent && <span className="another-email-sent">Another email was successfully sent.</span>}
              </span>
          }
        </form>
      </div>
    );
  }
}
