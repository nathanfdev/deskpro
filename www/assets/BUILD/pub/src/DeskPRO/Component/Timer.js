import PropTypes from 'prop-types';
import React from 'react';

class Timer extends React.Component {

  static propTypes = {
    paused: PropTypes.bool,
    format: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      time: 0
    };
  }

  componentDidMount() {
    const { paused } = this.props;

    if (!paused) {
      this.startTimer();
    }
  }

  componentWillReceiveProps(newProps) {
    const { paused } = this.props;

    if (paused !== newProps.paused) {
      if (newProps.paused) {
        this.stopTimer();
      } else {
        this.startTimer();
      }
    }
  }

  componentWillUnmount() {
    this.stopTimer();
  }

  startTimer() {
    this.interval = setInterval(() => {
      this.setState({
        time: this.state.time + 1
      });
    }, 1000);
  }

  stopTimer() {
    clearInterval(this.interval);
  }

  render() {
    const { format } = this.props;
    const { time } = this.state;

    if (format === 'waiting_time') {
      return <WaitingFormat value={time} />;
    }

    return <TimerFormat value={time} />;
  }
}

export class WaitingFormat extends React.Component {

  static propTypes = {
    value: PropTypes.number
  };

  render() {
    const { value } = this.props;

    const seconds = parseInt(value % 60, 10);
    const minutes = parseInt(value / 60, 10) % 60;
    const hours = parseInt(value / 60 / 60, 10);

    return (
      <span>
        {hours > 0 && `${hours}h `}
        {minutes > 0 && `${minutes}m `}
        {`${seconds}s`}
      </span>
    );
  }
}

export class TimerFormat extends React.Component {

  static propTypes = {
    value: PropTypes.number
  };

  render() {
    const { value } = this.props;

    let seconds = parseInt(value % 60, 10);
    let minutes = parseInt(value / 60, 10) % 60;
    let hours = parseInt(value / 60 / 60, 10);

    if (hours < 10) {
      hours = `0${hours}`;
    }
    if (minutes < 10) {
      minutes = `0${minutes}`;
    }
    if (seconds < 10) {
      seconds = `0${seconds}`;
    }

    return (
      <span>
        {hours}:{minutes}:{seconds}
      </span>
    );
  }
}

export default Timer;
