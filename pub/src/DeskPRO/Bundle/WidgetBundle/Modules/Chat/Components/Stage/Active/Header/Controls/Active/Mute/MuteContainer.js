import React from 'react';
import { MuteButton } from './MuteButton';

export class MuteContainer extends React.Component {

  onClick = () => {
    console.log('mute on click');
  };

  render() {
    return <MuteButton onClick={this.onClick} />;
  }
}
