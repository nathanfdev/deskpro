import React from 'react';

export class TypingMessage extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-message agent-message user-typing">
        <div className="dpdesignportal-message-avatar"></div>
        <div className="dpdesignportal-message-content">
          <span className="dpdesignportal-user-typing">Noelle is typing a message
            <span className="dot1">.</span>
            <span className="dot2">.</span>
            <span className="dot3">.</span>
          </span>
        </div>
      </div>
    );
  }
}
