import React from 'react';
import { AgentMessage } from './AgentMessage';
import { UserMessage } from './UserMessage';

export class MessagesList extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-content">
        <AgentMessage />
        <UserMessage />
      </div>
    );
  }
}
