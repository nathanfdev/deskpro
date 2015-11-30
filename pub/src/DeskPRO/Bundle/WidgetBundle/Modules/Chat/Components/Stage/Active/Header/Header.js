import React from 'react';
import { OnlineAgent } from './OnlineAgent';
import { Controls } from './Controls/Controls';

export class Header extends React.Component {

  render() {
    return (
      <div>
        <OnlineAgent />
        <Controls />
      </div>
    );
  }
}
