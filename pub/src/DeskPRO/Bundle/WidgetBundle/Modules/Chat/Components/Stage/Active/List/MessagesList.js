import React from 'react';
import { AgentMessage } from './Message/AgentMessage';
import { UserMessage } from './Message/UserMessage';
import { TypingMessage } from './Message/TypingMessage';
import ScrollArea from 'react-scrollbar';
import Immutable from 'immutable';

export class MessagesList extends React.Component {

  render() {
    return (
      <ScrollArea className="dpdesignportal-content" vertical>
        <AgentMessage />
        <AgentMessage />
        <AgentMessage />
        <AgentMessage />
        <AgentMessage />
        <AgentMessage />
        <UserMessage />
        <TypingMessage user={Immutable.fromJS({name: 'Noelle'})} />
      </ScrollArea>
    );
  }
}
