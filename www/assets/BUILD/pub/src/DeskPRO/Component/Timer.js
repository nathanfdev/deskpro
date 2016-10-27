import React, { PropTypes } from 'react';

class Timer extends React.Component {

  static propTypes = {
    format: PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {
      time: 0
    };
  }

  componentDidMount() {
    this.interval = setInterval(() => {
      this.setState({
        time: this.state.time + 1
      });
    }, 1000);
  }

  componentWillUnmount() {
    clearInterval(this.interval);
  }

  render() {
    const { format } = this.props;
    const { time } = this.state;

    const seconds = time % 60;
    const minutes = parseInt(time / 60, 10) % 60;
    const hours = parseInt(time / 60 / 60, 10);

    const formatProps = { hours, minutes, seconds };

    if (format === 'waiting_time') {
      return <WaitingFormat {...formatProps} />;
    }

    return <TimerFormat {...formatProps} />;
  }
}

class BaseFormat extends React.Component {

  static propTypes = {
    hours:   PropTypes.number,
    minutes: PropTypes.number,
    seconds: PropTypes.number
  };
}

class WaitingFormat extends BaseFormat {

  render() {
    const { hours, minutes, seconds } = this.props;

    return (
      <span>
        {hours > 0 && `${hours}h `}
        {minutes > 0 && `${minutes}m `}
        {`${seconds}s`}
      </span>
    );
  }
}

class TimerFormat extends BaseFormat {

  render() {
    let { hours, minutes, seconds } = this.props;

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
