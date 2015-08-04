import React from 'react';
import { connect } from 'redux/react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import ChatsNavFrame from './ChatsNavFrame';

export default class ChatsApp extends React.Component {  
  render() {
    return (
      <AppContainer thisAppId="chats">
        <ChatsNavFrame />
      </AppContainer>
    );
  }
}
