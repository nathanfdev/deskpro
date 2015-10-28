import React from 'react';
import { AppContainer } from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';

export class NewTicketsApp extends React.Component {

  render() {
    return (
      <AppContainer thisAppId="new-tickets">
        <NavContainer />
      </AppContainer>
    );
  }
}
