import React, { PropTypes } from 'react';

export class EndChatButton extends React.Component {

  static propTypes = {
    onOpenPopup: PropTypes.func
  };

  render() {
    return (
      <div className="dpdesignportal-chat-form-button-row-end-chat">
        <a href="#" className="dpdesignportal-chat-form-button" onClick={this.props.onOpenPopup}>
          <i className="fa fa-upload"></i>End Chat
        </a>
      </div>
    );
  }
}
