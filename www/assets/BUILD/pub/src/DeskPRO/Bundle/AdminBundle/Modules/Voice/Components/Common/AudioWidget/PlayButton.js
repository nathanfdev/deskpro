import React, { PropTypes } from 'react';
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

export class TextPlayButton extends React.Component {

  static propTypes = {
    language: PropTypes.string,
    text:     PropTypes.string,
    children: PropTypes.node
  };

  onPlay = () => {
    if (!window.speechSynthesis) {
      return;
    }

    const { text } = this.props;
    const voice = this.getLanguageVoice();
    if (!voice) {
      return;
    }

    const message = new SpeechSynthesisUtterance(text);
    message.voice = voice;
    window.speechSynthesis.speak(message);
  };

  getLanguageVoice = () => {
    const { language } = this.props;
    const previewVoices = {};

    if (!window.speechSynthesis) {
      return null;
    }

    window.speechSynthesis.getVoices().forEach((voice) => {
      previewVoices[voice.lang] = voice;
    });

    return language ? previewVoices[language] : null;
  };

  stopPlaying() { // eslint-disable-line class-methods-use-this
    window.speechSynthesis.cancel();
  }

  render() {
    const { children = <PlayButton /> } = this.props;
    if (!window.speechSynthesis) {
      return null;
    }

    return (
      <span>
        {React.cloneElement(children, {
          ...children.props,
          ...this.props,

          disabled: !this.getLanguageVoice(),
          onClick:  this.onPlay
        })}
      </span>
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
      return (
        <TextPlayButton
          {...this.props}
          language={value.get('language')}
          text={value.get('text')}
        />
      );
    }

    return (
      <UploadPlayButton
        {...this.props}
        downloadUrl={value.getIn(['blob', 'download_url'])}
      />
    );
  }
}
