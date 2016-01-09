import React from 'react';
import { LoginFormContainer } from './LoginFormContainer';

export class LeftPanel extends React.Component {

  render() {
    return (
      <div className="left-panel">
        <LoginFormContainer />

        <div className="dpw-left-panel-footer">
          <i className="fa fa-users"></i>
          <span className="text">No Account?</span>
          <a href="#">Request from admin</a>
          <hr />
          <a href="https://www.deskpro.com/signup/" target="_blank">Sign up for free</a>
        </div>
      </div>
    );
  }
}
