import React, { PropTypes } from 'react';
import { Message } from '../Message/Message';
import { MessageAvatar } from '../Message/MessageAvatar';

export class TypingMessageEvent extends React.Component {

  static propTypes = {
    user: PropTypes.object
  };

  render() {
    const { user } = this.props;

    return (
      <Message type="agent" typing>
        <MessageAvatar />
        <div className="dpdesignportal-message-content">
          <span className="dpdesignportal-user-typing">{user.get('name')} is typing a message
            <span className="dot1">.</span>
            <span className="dot2">.</span>
            <span className="dot3">.</span>
          </span>
        </div>
      </Message>
    );
  }
}
