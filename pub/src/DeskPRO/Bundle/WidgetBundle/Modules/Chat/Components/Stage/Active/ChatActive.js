import React from 'react';
import { Header } from './Header/Header';
import { MessagesListContainer } from './List/MessagesListContainer';
import { ReplyFormContainer } from './Reply/ReplyFormContainer';
import { RateAgentContainer } from './Feedback/RateAgentContainer';
import { AgentDisconnectedContainer } from './Disconnect/AgentDisconnectedContainer';
import { TypingEventContainer } from './List/Event/TypingEventContainer';

export class ChatActive extends React.Component {

  render() {
    return (
      <div>
        <Header />
        <MessagesListContainer />
        <div className="dpdesignportal-chat-footer">
          <TypingEventContainer />
          <AgentDisconnectedContainer />
          <RateAgentContainer />
          <ReplyFormContainer />
        </div>
      </div>
    );
  }
}
