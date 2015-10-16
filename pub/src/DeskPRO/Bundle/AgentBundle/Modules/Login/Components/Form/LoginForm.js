import React from 'react';
import { DpLogo } from '../DpLogo';
import { LeftPanelContainer } from './LeftPanelContainer';
import { RightPanel } from './RightPanel';
import { LoginFormFooter } from './LoginFormFooter';

export class LoginForm extends React.Component {

  render() {
    return (
      <div className="deskpro-loading">
        <DpLogo />

        <div className="dpw-login-panels">

          <LeftPanelContainer />
          <RightPanel />
        </div>

        <LoginFormFooter />
      </div>
    );
  }
}
