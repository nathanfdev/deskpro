import React from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
import { TypingMessage } from './Message/TypingMessage';

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
