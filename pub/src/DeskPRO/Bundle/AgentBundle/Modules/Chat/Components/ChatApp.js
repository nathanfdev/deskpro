import React from 'react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import { NavFrameContainer } from './NavFrame/NavFrameContainer';

export class ChatApp extends React.Component {

  render() {
    return (
      <AppContainer thisAppId="chat">
        <NavFrameContainer />
      </AppContainer>
    );
  }
}
