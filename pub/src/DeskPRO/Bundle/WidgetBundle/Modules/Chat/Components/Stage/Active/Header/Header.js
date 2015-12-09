import React from 'react';
import { OnlineAgentContainer } from './Agent/OnlineAgentContainer';
import { ControlsPaneContainer } from './Controls/ControlsPaneContainer';

export class Header extends React.Component {

  render() {
    return (
      <div>
        <OnlineAgentContainer />
        <ControlsPaneContainer />
      </div>
    );
  }
}
