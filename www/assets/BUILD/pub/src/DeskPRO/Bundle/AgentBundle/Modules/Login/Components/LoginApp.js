import React from 'react';
import { DpLogo } from './DpLogo';
import { LeftPanel } from './Form/LeftPanel';
import { RightPanel } from './Form/RightPanel';
import { LoginFormFooter } from './Form/LoginFormFooter';

export class LoginApp extends React.Component {

  render() {
    return (
      <div className="deskpro-loading">
        <DpLogo />

        <div className="dpw-login-panels">
          <LeftPanel />
          <RightPanel />
        </div>

        <LoginFormFooter />
      </div>
    );
  }
}
