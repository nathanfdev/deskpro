import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Range } from 'DeskPRO/Component/Semantic/Form';
import Duration from 'DeskPRO/Component/Duration';
import Loader from '@deskpro/react-loader';

class MediaControls extends React.Component {

  static propTypes = {
    recording:       PropTypes.object,
    withResetButton: PropTypes.bool,
    withDuration:    PropTypes.bool,
    withDownload:    PropTypes.bool
  };

  static defaultProps = {
    withResetButton: true,
    withDuration:    true,
    withDownload:    true
  };

  constructor(props) {
    super(props);
    this.blob = null;
    this.state = {
      duration:    null,
      playing:     false,
      currentTime: 0
    };
  }

  componentDidMount() {
    this.loadAudio();
    this.audio.addEventListener('playing', this.setPlaying);
    this.audio.addEventListener('play', this.setPlaying);
    this.audio.addEventListener('pause', this.setPaused);
    this.audio.addEventListener('ended', this.setPaused);
    this.audio.addEventListener('loadedmetadata', this.setDuration);
    this.audio.addEventListener('timeupdate', this.setCurrentTime);
  }
  componentWillUnmount() {
    URL.revokeObjectURL(this.blob);
    this.audio.removeEventListener('playing', this.setPlaying);
    this.audio.removeEventListener('play', this.setPlaying);
    this.audio.removeEventListener('pause', this.setPaused);
    this.audio.removeEventListener('ended', this.setPaused);
    this.audio.removeEventListener('loadedmetadata', this.setDuration);
    this.audio.removeEventListener('timeupdate', this.setCurrentTime);

    if (this.audio && this.audio.readyState > 2) {
      this.audio.pause();
    }
  }

  onPlay = () => {
    const { playing } = this.state;
    if (!playing) {
      this.audio.play();
    } else {
      this.audio.pause();
    }
  };

  onMove = (value) => {
    const { duration } = this.state;
    if (!duration) {
      return;
    }

    const currentTime = parseInt(value, 10);

    this.setState({
      currentTime
    });

    this.audio.currentTime = currentTime;
  };

  onStepBackward = () => {
    this.audio.currentTime = 0;
  };

  setPlaying = () => {
    this.setState({ playing: true });
  };

  setPaused = () => {
    this.setState({ playing: false });
  };

  setDuration = () => {
    this.setState({ duration: this.audio.duration });
  };

  setCurrentTime = () => {
    this.setState({ currentTime: this.audio.currentTime });
  };

  setLoaded = () => {
    this.setState({ loaded: true });
  };

  loadAudio = () => {
    const req = new XMLHttpRequest();
    req.open('GET', this.props.recording.get('download_url').replace('https:', '').replace('http:', ''), true);
    req.responseType = 'blob';

    req.onload = () => {
      if (req.status === 200) {
        const blob = req.response;
        this.blob = URL.createObjectURL(blob);
        this.audio.src = this.blob;
        this.setLoaded();
      }
    };
    req.onerror = (e) => {
      console.error('Can\'t load an audio file!', e);
    };
    req.send();
  };

  render() {
    const { recording, withResetButton, withDuration, withDownload } = this.props;
    const { playing, duration, currentTime, loaded } = this.state;

    return (
      <div className="media-controls">
        <audio ref={(c) => { this.audio = c; }} preload="none" />
        {withResetButton && <Button className={classNames('basic icon', { disabled: !loaded })} onClick={this.onStepBackward}>
          {!loaded ? <Loader loaded={loaded} opacity={0} width={3} scale={0.5} color="#4696dc" /> : <i className="icon step backward" /> }
        </Button>}
        <Button className={classNames('basic icon', { disabled: !loaded })} onClick={this.onPlay}>
          {!loaded ? <Loader loaded={loaded} opacity={0} width={3} scale={0.5} color="#4696dc" /> : <i className={classNames(playing ? 'pause' : 'play', 'icon')} />}
        </Button>
        <div className="media-timeline">
          <Range value={currentTime} onChange={this.onMove} min={0} max={duration} />
        </div>
        {withDuration && <span className="media-time">
          <Duration value={currentTime} /> / { duration ? <Duration value={duration} /> : '??' }
        </span>}
        {withDownload && <a
          className="media-size"
          href={`${recording.get('download_url')}?dl=1`}
          target="_blank"
          rel="noopener noreferrer"
        >
          <i className="icon download" /> {recording.get('filesize_readable')}
        </a>}
      </div>
    );
  }
}

export default MediaControls;
