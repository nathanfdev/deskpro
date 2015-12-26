import React from 'react';
import { HeaderContainer } from './Header/HeaderContainer';
import { ChatContentContainer } from './ChatContentContainer';
import { MessagesListContainer } from './List/MessagesListContainer';
import { ReplyFormContainer } from './Reply/ReplyFormContainer';
import { RateAgentContainer } from './Feedback/RateAgentContainer';
import { AgentDisconnectedContainer } from './Disconnect/AgentDisconnectedContainer';
import { TypingEventContainer } from './List/Event/TypingEventContainer';

export class ChatActive extends React.Component {

  render() {
    return (
      <div>
        <HeaderContainer />
        <ChatContentContainer>
          <MessagesListContainer />
          <TypingEventContainer />
          <AgentDisconnectedContainer />
          <RateAgentContainer />
          <ReplyFormContainer />
        </ChatContentContainer>
      </div>
    );
  }
}
