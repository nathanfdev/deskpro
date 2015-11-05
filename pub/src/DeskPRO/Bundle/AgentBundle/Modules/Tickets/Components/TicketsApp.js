import React from 'react';
import { AppContainer } from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './List/ListContainer';

export class TicketsApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="tickets">
        <NavContainer />
        <ListContainer />
      </AppContainer>
    );
  }
}
