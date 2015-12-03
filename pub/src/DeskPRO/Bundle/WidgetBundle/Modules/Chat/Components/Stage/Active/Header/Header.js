import React from 'react';
import { OnlineAgentContainer } from './Agent/OnlineAgentContainer';
import { ControlsContainer } from './Controls/ControlsContainer';

export class Header extends React.Component {

  render() {
    return (
      <div>
        <OnlineAgentContainer />
        <ControlsContainer />
      </div>
    );
  }
}
