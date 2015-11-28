import React from 'react';
import { AgentMessage } from './AgentMessage';
import { UserMessage } from './UserMessage';
import { TypingMessage } from './TypingMessage';

export class MessagesList extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-content">
        <AgentMessage />
        <UserMessage />
        <TypingMessage />
      </div>
    );
  }
}
