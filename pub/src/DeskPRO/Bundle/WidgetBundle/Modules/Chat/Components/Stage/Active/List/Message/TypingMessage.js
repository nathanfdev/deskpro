import React from 'react';
import { Message } from './Message';

export class TypingMessage extends React.Component {

  render() {
    return (
      <Message type="agent" typing>
        <div className="dpdesignportal-message-avatar"></div>
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
