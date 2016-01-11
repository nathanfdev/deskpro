import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import { validateEmail } from '../../../Actions/chatActions';
import { chatIdSelector } from '../../../Selectors/chat';
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
      errors: null
    };
  }

  componentDidMount() {
    const inputNode = ReactDOM.findDOMNode(this.refs.input);
    $(inputNode).focus();
  }

  onChangeCode = event => {
    this.setState({
      code: event.target.value
    });
  };

  onSubmit = event => {
    event.preventDefault();
    const { chatId, dispatch } = this.props;

    dispatch(validateEmail(chatId, {code: this.state.code}));
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

        <form>
          <input type="text" ref="input" onChange={this.onChangeCode} value={this.state.code} />
          <a href="#" className="email-code-submit" onClick={this.onSubmit}>
            Start Chat <i className="fa fa-chevron-right"></i>
          </a>
        </form>
      </div>
    );
  }
}
