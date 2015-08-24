import React from 'react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import { NavContainer } from './Nav/NavContainer';

export class PublishApp extends React.Component {

  render() {
    return (
      <AppContainer thisAppId="crm">
        <NavContainer />
      </AppContainer>
    );
  }
}
