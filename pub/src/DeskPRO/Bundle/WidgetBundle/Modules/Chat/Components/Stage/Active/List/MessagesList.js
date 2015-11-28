import React from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
import { TypingMessage } from './Message/TypingMessage';
import Immutable from 'immutable';

export class MessagesList extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-content">
        <AgentMessage />
        <UserMessage />
        <TypingMessage user={Immutable.fromJS({name: 'Noelle'})} />
      </div>
    );
  }
}
