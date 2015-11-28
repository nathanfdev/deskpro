import React, { PropTypes } from 'react';

export class ReplyForm extends React.Component {

  static propTypes = {
    onSendMessage: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      message: ''
    };
  }

  onChangeMessage = event => {
    this.setState({
      message: event.target.value
    });
  };

  onSubmit = event => {
    event.preventDefault();
    this.props.onSendMessage(this.state.message);
  };

  render() {
    return (
      <div className="dpdesignportal-chat-form">
        <form onSubmit={this.onSubmit}>
          <div className="message-container">
            <textarea placeholder="Type your message to Noelle"
                      value={this.state.message}
                      onChange={this.onChangeMessage} />
          </div>

          <button><i className="fa fa-angle-double-right"></i></button>
        </form>
      </div>
    );
  }
}
