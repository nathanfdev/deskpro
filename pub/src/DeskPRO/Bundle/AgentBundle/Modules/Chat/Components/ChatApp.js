import React from 'react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';

export class ChatApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="chat">
        <NavContainer />
      </AppContainer>
    );
  }
}
