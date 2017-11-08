import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Range } from 'DeskPRO/Component/Semantic/Form';
import Duration from 'DeskPRO/Component/Duration';

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
      <div className="media-controls">
        <audio ref={(c) => { this.audio = c; }} src={recording.get('download_url')} />

        <Button className={classNames('basic icon', { disabled: !duration })} onClick={this.onStepBackward}>
          <i className="icon step backward" />
        </Button>
        <Button className={classNames('basic icon', { disabled: !duration })} onClick={this.onPlay}>
          <i className={classNames(playing ? 'stop' : 'play', 'icon')} />
        </Button>
        <div className="media-timeline">
          <Range value={rangeValue} onChange={this.onMove} />
        </div>
        <span className="media-time">
          <Duration value={currentTime} /> / { duration ? <Duration value={duration} /> : '??' }
        </span>
        <a
          className="media-size"
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

export default MediaControls;
