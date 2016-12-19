import React from 'react';
import { OnlineAgentContainer } from './Agent/OnlineAgentContainer';
import { ControlsPaneContainer } from './Controls/ControlsPaneContainer';

export class Header extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-chat-header-wrapper">
        <OnlineAgentContainer />
        <ControlsPaneContainer />
      </div>
    );
  }
}
