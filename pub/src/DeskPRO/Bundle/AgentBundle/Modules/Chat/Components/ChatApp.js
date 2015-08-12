import React from 'react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import { MyChatsGroupingControls } from './MyChatsGroupingControls';
import { AllChatsGroupingControls } from './AllChatsGroupingControls';
import { ChatConversationsNavFrame } from './ChatConversationsNavFrame';

export default class ChatApp extends React.Component {  
  render() {
    return (
      <AppContainer thisAppId="chat">
        <MyChatsGroupingControls />
        <AllChatsGroupingControls />
        <ChatConversationsNavFrame />
      </AppContainer>
    );
  }
}
