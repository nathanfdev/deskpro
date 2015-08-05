import React from 'react';
import { connect } from 'redux/react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import ChatConversationsNavFrame from './ChatConversationsNavFrame';

export default class ChatApp extends React.Component {  
  render() {
    return (
      <AppContainer thisAppId="chat">
        <ChatConversationsNavFrame />
      </AppContainer>
    );
  }
}
