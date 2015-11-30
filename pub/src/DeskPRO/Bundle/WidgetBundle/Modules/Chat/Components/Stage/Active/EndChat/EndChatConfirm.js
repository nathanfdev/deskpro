import React, { PropTypes } from 'react';

export class EndChatConfirm extends React.Component {

  static propTypes = {
    onCancel: PropTypes.func,
    onConfirm: PropTypes.func
  };

  render() {
    const { onCancel, onConfirm } = this.props;

    return (
      <div className="dpdesignportal-popover dpdesignportal-popover-end-chat">
        <h1>Are you sure you want to end this chat?</h1>
        <p className="grey">Lorel ipsum dolor closing the chat</p>

        <div className="popover-buttons">
          <a href="#" className="dpdesignportal-button" onClick={onConfirm}>
            <i className="fa fa-power-off"></i> End Chat
          </a>
          <a href="#" className="dpdesignportal-button grey" onClick={onCancel}>
            <i className="fa fa-reply"></i> Cancel and return to chat
          </a>
        </div>
      </div>
    );
  }
}
