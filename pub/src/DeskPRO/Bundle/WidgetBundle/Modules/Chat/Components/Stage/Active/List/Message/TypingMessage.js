import React from 'react';
import { Message } from './Message';
import { MessageAvatar } from './MessageAvatar';

export class TypingMessage extends React.Component {

  render() {
    return (
      <Message type="agent" typing>
        <MessageAvatar />
        <div className="dpdesignportal-message-content">
          <span className="dpdesignportal-user-typing">Noelle is typing a message
            <span className="dot1">.</span>
            <span className="dot2">.</span>
            <span className="dot3">.</span>
          </span>
        </div>
      </Message>
    );
  }
}
