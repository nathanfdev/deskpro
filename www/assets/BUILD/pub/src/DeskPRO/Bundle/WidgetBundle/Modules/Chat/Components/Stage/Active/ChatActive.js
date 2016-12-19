import React from 'react';
import { HeaderContainer } from './Header/HeaderContainer';
import { ChatContentContainer } from './ChatContentContainer';
import { MessageListContainer } from './List/MessageListContainer';
import { ReplyFormContainer } from './Reply/ReplyFormContainer';
import { RateAgentContainer } from './Feedback/RateAgentContainer';
import { LostConnectionContainer } from './Disconnect/LostConnectionContainer';

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
          <LostConnectionContainer />
          <ReplyFormContainer />
        </div>
      </div>
    );
  }
}
export default ChatActive;
