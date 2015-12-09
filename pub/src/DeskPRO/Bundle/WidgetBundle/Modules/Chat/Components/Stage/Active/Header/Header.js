import React from 'react';
import { OnlineAgentContainer } from './Agent/OnlineAgentContainer';
import { ControlsPane } from './Controls/ControlsPane';

export class Header extends React.Component {

  render() {
    return (
      <div>
        <OnlineAgentContainer />
        <ControlsPane />
      </div>
    );
  }
}
