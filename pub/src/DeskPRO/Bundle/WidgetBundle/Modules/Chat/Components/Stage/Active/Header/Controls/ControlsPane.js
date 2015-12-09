import React, { PropTypes } from 'react';
import { AssetsContainer } from './Assets/AssetsContainer';
import { MuteContainer } from './Mute/MuteContainer';
import { TranscriptContainer } from './Transcript/TranscriptContainer';
import { TranscriptButton } from './Transcript/TranscriptButton';
import { EndChatButtonContainer } from './EndChat/EndChatButtonContainer';

export class ControlsPane extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="dpdesignportal-chat-header-controls">
        <ul>
          <li>
            <AssetsContainer />
          </li>
          <li>
            <MuteContainer />
          </li>
          <li>
            <TranscriptContainer>
              <TranscriptButton />
            </TranscriptContainer>
          </li>
          <li>
            <EndChatButtonContainer />
          </li>
        </ul>
      </div>
    );
  }
}
