import React from 'react';
import AudioWidgetFormContainer from './AudioWidgetFormContainer';

class Voicemail extends React.Component {

  render() {
    return (
      <div className="voice-voicemail">
        <div className="label">Select voicemail asset</div>
        <AudioWidgetFormContainer />
      </div>
    );
  }
}

export default Voicemail;
