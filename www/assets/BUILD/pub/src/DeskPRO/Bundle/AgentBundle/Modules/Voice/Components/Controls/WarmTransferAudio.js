import React from 'react';
import '../../../..//Resources/sounds/outgoing-ringtone.mp3';
import '../../../../Resources/sounds/outgoing-ringtone.ogg';
import '../../../../Resources/sounds/outgoing-ringtone.wav';

class WarmTransferAudio extends React.Component {

  componentWillUnmount() {
    this.stopSound();
  }

  stopSound() {
    if (this.sound && this.sound.readyState > 0) {
      this.sound.pause();
      this.sound.currentTime = 0;
    }
  }

  render() {
    const soundsPath = `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/sounds`;

    return (
      <audio ref={(c) => { this.sound = c; }} preload="preload" autoPlay loop>
        <source src={`${soundsPath}/outgoing-ringtone.mp3`} />
        <source src={`${soundsPath}/outgoing-ringtone.ogg`} />
        <source src={`${soundsPath}/outgoing-ringtone.wav`} />
      </audio>
    );
  }
}

export default WarmTransferAudio;
