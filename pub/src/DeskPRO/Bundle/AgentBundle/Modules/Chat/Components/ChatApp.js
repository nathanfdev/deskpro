import React from 'react';
import { connect } from 'redux/react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import ChatRoomsNavFrame from './ChatRoomsNavFrame';

export default class ChatApp extends React.Component {  
  render() {
    return (
      <AppContainer thisAppId="chat">
        <ChatRoomsNavFrame />
      </AppContainer>
    );
  }
}
