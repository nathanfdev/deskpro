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
    this.setState({
      message: ''
    });
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

        <div className="dpdesignportal-chat-form-button-row">
          <div className="dpdesignportal-chat-form-button-row-main">
            <a href="#"><i className="fa fa-upload"></i> Upload file</a>
            <a href="#"><i className="fa fa-camera"></i> Screen Share</a>
            <a href="#" className="dpdesignportal-chat-form-button-row-emoticons" title="Chat Emoticons">
              <span className="img" />
            </a>
          </div>

          <div className="dpdesignportal-chat-form-button-row-end-chat">
            <a href="#"><i className="fa fa-upload"></i>End Chat</a>
          </div>
        </div>
      </div>
    );
  }
}
