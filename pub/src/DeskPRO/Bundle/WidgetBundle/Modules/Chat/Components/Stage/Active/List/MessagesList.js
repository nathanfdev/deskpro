import React from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
import { TypingMessage } from './Message/TypingMessage';
import ScrollArea from 'react-scrollbar';
import Immutable from 'immutable';

export class MessagesList extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-content" speed={0.8} style={{height: 300, overflowY: 'hidden'}}>
        <ScrollArea vertical horizontal={false}>
          <AgentMessage />
          <AgentMessage />
          <AgentMessage />
          <AgentMessage />
          <AgentMessage />
          <AgentMessage />
          <UserMessage />
          <TypingMessage user={Immutable.fromJS({name: 'Noelle'})} />
        </ScrollArea>
      </div>
    );
  }
}
