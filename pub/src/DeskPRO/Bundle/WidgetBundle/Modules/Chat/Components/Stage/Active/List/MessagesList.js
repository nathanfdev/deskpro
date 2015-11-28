import React from 'react';
import { AgentMessage } from './AgentMessage';

export class MessagesList extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-content">
        <AgentMessage />
        <AgentMessage />
      </div>
    );
  }
}
