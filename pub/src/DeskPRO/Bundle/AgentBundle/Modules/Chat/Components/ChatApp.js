import React from 'react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import { MyChatsGroupingControls } from './MyChatsGroupingControls';
import { ChatConversationsNavFrame } from './ChatConversationsNavFrame';

export default class ChatApp extends React.Component {  
  render() {
    return (
      <AppContainer thisAppId="chat">
        <MyChatsGroupingControls />
        <ChatConversationsNavFrame />
      </AppContainer>
    );
  }
}
