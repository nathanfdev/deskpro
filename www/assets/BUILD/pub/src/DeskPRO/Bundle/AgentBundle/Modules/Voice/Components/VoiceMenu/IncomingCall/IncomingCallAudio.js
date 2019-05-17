import PropTypes from 'prop-types';
import React from 'react';

class IncomingCallAudio extends React.Component {

  static propTypes = {
    mp3:           PropTypes.string,
    wav:           PropTypes.string,
    ogg:           PropTypes.string,
    ringingVolume: PropTypes.number,
    onSoundEnded:  PropTypes.func
  };

  componentWillUnmount() {
    this.stopSound();
  }

  onSoundEnded = () => {
    if (this.props.onSoundEnded) {
      this.props.onSoundEnded();
    }
  };

  playSound() {
    this.stopSound();

    if (this.sound) {
      this.sound.addEventListener('ended', this.onSoundEnded);
      this.sound.volume = this.props.ringingVolume / 100;
    }
    const playPromise = this.sound.play();
    if (playPromise) {
      playPromise.catch(() => setTimeout(this.playSound, 100));
    }
  }

  stopSound() {
    if (this.sound && this.sound.readyState > 0) {
      this.sound.removeEventListener('ended', this.onSoundEnded);
      this.sound.pause();
      this.sound.currentTime = 0;
    }
  }

  render() {
    const { mp3, wav, ogg } = this.props;

    return (
      <audio ref={(c) => { this.sound = c; }} preload="preload">
        <source src={mp3} />
        <source src={ogg} />
        <source src={wav} />
      </audio>
    );
  }
}

export default IncomingCallAudio;
