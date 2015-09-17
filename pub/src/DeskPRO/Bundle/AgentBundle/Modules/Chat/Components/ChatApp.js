import React from 'react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import { NavContainer } from '../Containers/Nav/NavContainer';
import { ListContainer } from '../Containers/List/ListContainer';

export class ChatApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="chat">
        <NavContainer />
        <ListContainer />
      </AppContainer>
    );
  }
}
