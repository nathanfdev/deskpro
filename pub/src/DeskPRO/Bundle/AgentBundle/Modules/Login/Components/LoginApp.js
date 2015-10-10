import React from 'react';
import { AppContainer } from 'DeskPRO/Component/AppContainer';

export class LoginApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="login" {...this.props}>
        <div></div>
      </AppContainer>
    );
  }
}
