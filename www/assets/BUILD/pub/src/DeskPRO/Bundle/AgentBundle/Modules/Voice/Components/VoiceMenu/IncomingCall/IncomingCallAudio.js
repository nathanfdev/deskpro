import PropTypes from 'prop-types';
import React from 'react';
import '../../../../../Resources/sounds/incoming-call.mp3';
import '../../../../../Resources/sounds/incoming-call.ogg';
import '../../../../../Resources/sounds/incoming-call.wav';

class IncomingCallAudio extends React.Component {

  static propTypes = {
    ringingVolume: PropTypes.number,
    onSoundEnded:  PropTypes.func
  };

  componentWillUnmount() {
    this.stopSound();
  }

  onSoundEnded = () => {
    const { onSoundEnded } = this.props;
    if (onSoundEnded) {
      onSoundEnded();
    }
  };

  playSound() {
    const { ringingVolume } = this.props;
    if (this.sound) {
      this.sound.addEventListener('ended', this.onSoundEnded);
      this.sound.volume = ringingVolume / 100;
    }
    this.stopSound();
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
    const soundsPath = `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/sounds`;

    return (
      <audio ref={(c) => { this.sound = c; }} preload="preload">
        <source src={`${soundsPath}/incoming-call.mp3`} />
        <source src={`${soundsPath}/incoming-call.ogg`} />
        <source src={`${soundsPath}/incoming-call.wav`} />
      </audio>
    );
  }
}

export default IncomingCallAudio;
