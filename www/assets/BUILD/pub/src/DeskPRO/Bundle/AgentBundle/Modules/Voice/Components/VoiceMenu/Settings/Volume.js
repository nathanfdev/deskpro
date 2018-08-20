import PropTypes from 'prop-types';
import React from 'react';
import { Range } from 'DeskPRO/Component/Semantic/Form';
import IncomingCallAudio from '../IncomingCall/IncomingCallAudio';

class Volume extends React.Component {

  static propTypes = {
    value:    PropTypes.number,
    onChange: PropTypes.func
  };

  onBlur = () => {
    this.audio.playSound();
    setTimeout(() => this.audio.stopSound(), 1000);
  };

  render() {
    const { value, onChange } = this.props;

    return (
      <div className="volume">
        <IncomingCallAudio
          ref={(c) => { this.audio = c; }}
          ringingVolume={value}
        />
        <Range
          value={value}
          onChange={onChange}
          onBlur={this.onBlur}
        />
      </div>
    );
  }
}

export default Volume;
