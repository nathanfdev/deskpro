import React from 'react';
import { HeaderContainer } from './Header/HeaderContainer';
import { ChatContentContainer } from './ChatContentContainer';
import { MessageListContainer } from './List/MessageListContainer';
import { ReplyFormContainer } from './Reply/ReplyFormContainer';
import { RateAgentContainer } from './Feedback/RateAgentContainer';

export class ChatActive extends React.Component {

  render() {
    return (
      <div>
        <HeaderContainer />
        <ChatContentContainer>
          <MessageListContainer />
        </ChatContentContainer>
        <div className="dpdesignportal-chat-footer">
          <RateAgentContainer />
          <ReplyFormContainer />
        </div>
      </div>
    );
  }
}
