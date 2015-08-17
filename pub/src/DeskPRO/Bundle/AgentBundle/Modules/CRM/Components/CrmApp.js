import React from 'react';
import AppContainer from 'DeskPRO/Component/AppContainer';
import { NavFrameContainer } from './NavFrame/NavFrameContainer';

export class CrmApp extends React.Component {

  render() {
    return (
      <AppContainer thisAppId="crm">
        <NavFrameContainer />
      </AppContainer>
    );
  }
}
