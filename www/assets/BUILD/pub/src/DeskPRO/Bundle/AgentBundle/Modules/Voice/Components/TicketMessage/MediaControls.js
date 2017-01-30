import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Range } from 'DeskPRO/Component/Semantic/Form';
import { TimerFormat } from 'DeskPRO/Component/Timer';

class MediaControls extends React.Component {

  static propTypes = {
    recording: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      duration:    null,
      playing:     false,
      currentTime: 0
    };
  }

  componentDidMount() {
    this.audio.addEventListener('ended', () => {
      this.setState({
        playing: false
      });
    });

    this.audio.addEventListener('loadedmetadata', () => {
      this.setState({
        duration: this.audio.duration
      });
    });

    this.audio.addEventListener('timeupdate', () => {
      this.setState({
        currentTime: this.audio.currentTime
      });
    });
  }

  onPlay = () => {
    const { playing } = this.state;

    if (!playing) {
      this.audio.play();
      this.setState({
        playing: true
      });
    } else {
      this.audio.pause();
      this.audio.currentTime = 0;
      this.setState({
        playing: false
      });
    }
  };

  onMove = (value) => {
    const { duration } = this.state;
    if (!duration) {
      return;
    }

    this.audio.currentTime = (value / 100) * duration;
    this.audio.play();
    this.setState({
      playing: true
    });
  };

  onStepBackward = () => {
    this.audio.currentTime = 0;
  };

  render() {
    const { recording } = this.props;
    const { playing, duration, currentTime } = this.state;
    const rangeValue = duration ? (currentTime / duration) * 100 : 0;

    return (
      <div className="voice-ticket-message-media-controls">
        <audio ref={(c) => { this.audio = c; }} src={recording.get('download_url')} />

        <Button className={classNames('basic icon', { disabled: !duration })} onClick={this.onStepBackward}>
          <i className="icon step backward" />
        </Button>
        <Button className={classNames('basic icon', { disabled: !duration })} onClick={this.onPlay}>
          <i className={classNames(playing ? 'stop' : 'play', 'icon')} />
        </Button>
        <div className="voice-ticket-message-timeline">
          <Range value={rangeValue} onChange={this.onMove} />
        </div>
        <span className="voice-ticket-message-time">
          <Time value={currentTime} /> / { duration ? <Time value={duration} /> : '??' }
        </span>
        <a
          className="voice-ticket-message-size"
          href={`${recording.get('download_url')}?dl=1`}
          target="_blank"
          rel="noopener noreferrer"
        >
          <i className="icon download" /> {recording.get('filesize_readable')}
        </a>
      </div>
    );
  }
}

class Time extends React.Component {

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

export default MediaControls;
