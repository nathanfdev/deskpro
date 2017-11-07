import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class PlayButton extends React.Component {

  static propTypes = {
    label:    PropTypes.string,
    iconOnly: PropTypes.bool,
    disabled: PropTypes.bool,
    playing:  PropTypes.bool,
    onClick:  PropTypes.func
  };

  static defaultProps = {
    label: 'Preview'
  };

  onClick = (event) => {
    event.preventDefault();
    const { disabled, onClick } = this.props;

    if (disabled) {
      return;
    }

    onClick();
  };

  render() {
    const { label, iconOnly, disabled, playing } = this.props;

    return (
      <button
        className={classNames('ui basic button preview-button', { disabled, icon: iconOnly || !label })}
        onClick={this.onClick}
      >
        <i className={classNames(playing ? 'stop' : 'play', 'icon')} />
        {!iconOnly && label}
      </button>
    );
  }
}

export class UploadPlayButton extends React.Component {

  static propTypes = {
    downloadUrl: PropTypes.string,
    children:    PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      playing: false
    };
  }

  componentDidMount() {
    this.audio.addEventListener('ended', () => {
      this.setState({
        playing: false
      });
    });
  }

  componentWillUnmount() {
    this.stopPlaying();
  }

  onPlay = () => {
    const { downloadUrl } = this.props;
    const { playing } = this.state;

    if (!downloadUrl) {
      return;
    }

    if (!playing) {
      this.audio.src = downloadUrl;
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

  stopPlaying() {
    this.audio.pause();
    this.audio.currentTime = 0;
    this.setState({
      playing: false
    });
  }

  render() {
    const { downloadUrl } = this.props;
    const { children = <PlayButton /> } = this.props;
    const { playing } = this.state;

    return (
      <span>
        {React.cloneElement(children, {
          ...children.props,
          ...this.props,

          playing,
          disabled: !downloadUrl,
          onClick:  this.onPlay
        })}
        <audio ref={(c) => { this.audio = c; }} />
      </span>
    );
  }
}

export class AssetPlayButton extends React.Component {

  static propTypes = {
    value: PropTypes.object
  };

  render() {
    const { value } = this.props;
    const type = value && value.get('type');

    if (type === 'text') {
      return null;
    }

    return (
      <UploadPlayButton
        {...this.props}
        downloadUrl={value.getIn(['blob', 'download_url'])}
      />
    );
  }
}
