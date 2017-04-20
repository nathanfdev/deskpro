import React from 'react';
import { TimerFormat } from './Timer';

class Duration extends React.Component {

  render() {
    return <TimerFormat {...this.props} />;
  }
}

export default Duration;
