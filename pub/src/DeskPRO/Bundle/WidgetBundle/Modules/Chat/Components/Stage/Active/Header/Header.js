import React from 'react';
import { OnlineAgent } from './OnlineAgent';
import { ControlsContainer } from './Controls/ControlsContainer';

export class Header extends React.Component {

  render() {
    return (
      <div>
        <OnlineAgent />
        <ControlsContainer />
      </div>
    );
  }
}
