import PropTypes from 'prop-types';
import React from 'react';
import '../../../../../Resources/sounds/outgoing-ringtone.mp3';

class OutgoingCallAudio extends React.Component {

  static propTypes = {
    loop:          PropTypes.bool,
    ringingVolume: PropTypes.string,
    onSoundEnded:  PropTypes.func
  };

  componentWillUnmount() {
    this.stopSound();
  }

  onSoundEnded = () => {
    const { onSoundEnded, loop } = this.props;
    if (onSoundEnded) {
      onSoundEnded();
    }
    if (loop) {
      this.playSound();
    }
  };

  playSound() {
    const { ringingVolume } = this.props;

    this.stopSound();

    this.sound.addEventListener('ended', this.onSoundEnded);
    this.sound.volume = ringingVolume / 100;
    this.sound.play();
  }

  stopSound() {
    if (this.sound && this.sound.readyState) {
      this.sound.removeEventListener('ended', this.onSoundEnded);
      this.sound.pause();
      this.sound.currentTime = 0;
    }
  }

  render() {
    const soundsPath = `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/sounds`;

    return (
      <audio ref={(c) => { this.sound = c; }} preload="preload">
        <source src={`${soundsPath}/outgoing-ringtone.mp3`} />
      </audio>
    );
  }
}

export default OutgoingCallAudio;
