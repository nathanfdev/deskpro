import React, { PropTypes } from 'react';
import { TimerFormat } from './Timer';

class Duration extends React.Component {

  static propTypes = {
    value: PropTypes.number
  };

  render() {
    const { value } = this.props;

    const seconds = parseInt(value % 60, 10);
    const minutes = parseInt(value / 60, 10) % 60;
    const hours = parseInt(value / 60 / 60, 10);

    return (
      <TimerFormat
        seconds={seconds}
        minutes={minutes}
        hours={hours}
      />
    );
  }
}

export default Duration;
