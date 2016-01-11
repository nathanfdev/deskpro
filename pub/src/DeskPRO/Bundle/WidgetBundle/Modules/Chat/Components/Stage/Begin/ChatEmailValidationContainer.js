import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import { validateEmail } from '../../../Actions/chatActions';
import { chatIdSelector } from '../../../Selectors/chat';
import { FieldErrors } from 'DeskPRO/Component/Form/FormErrors';
import $ from 'jquery';

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
      errors: null
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

  onSubmit = event => {
    event.preventDefault();
    this.setState({
      submit: true
    });

    const { chatId, dispatch } = this.props;
    const promise = dispatch(validateEmail(chatId, {code: this.state.code}));
    promise.then(
      () => {
        if (this.mounted) {
          this.setState({
            submit: false
          });
        }
      },
      result => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: result.getData()
          });
        }
      }
    );
  };

  render() {
    return (
      <div className="dpdesignportal-chat-email-validation">
        <span className="description">
          <p>We require you to validate your email address.</p>
          <br/>
          <p>We sent you an email with a validation code.</p>
          <p>Check your email then enter the code below</p>
        </span>

        <form onSubmit={this.onSubmit}>
          <input type="text" ref="input" onChange={this.onChangeCode} value={this.state.code} />
          <FieldErrors errors={this.state.errors} field="code" />

          {this.state.submit
            ? <div className="spinner"><i/></div>
            : <a href="#" className="email-code-submit" onClick={this.onSubmit}>
                Start Chat <i className="fa fa-chevron-right"></i>
              </a>
          }
        </form>
      </div>
    );
  }
}
