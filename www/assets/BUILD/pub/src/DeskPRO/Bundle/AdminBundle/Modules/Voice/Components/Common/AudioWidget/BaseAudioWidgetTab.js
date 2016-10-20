import React, { PropTypes } from 'react';

class BaseAudioWidgetTab extends React.Component {

  static propTypes = {
    value:    PropTypes.object,
    onChange: PropTypes.func
  };

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

  stopPlaying() {
    this.audio.pause();
    this.audio.currentTime = 0;
    this.setState({
      playing: false
    });
  }
}

export default BaseAudioWidgetTab;
