import React from 'react';
import { ControlsPane } from './ControlsPane';
import { AssetsContainer } from './Assets/AssetsContainer';
import { MuteContainer } from './Mute/MuteContainer';
import { EndChatContainer } from '../../EndChat/EndChatContainer';
import { EndChatButton } from './EndChat/EndChatButton';
import { TranscriptContainer } from './Transcript/TranscriptContainer';
import { TranscriptButton } from './Transcript/TranscriptButton';

export class ActivePane extends React.Component {

  render() {
    return (
      <ControlsPane>
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
          <EndChatContainer confirmPosition="bottom">
            <EndChatButton />
          </EndChatContainer>
        </li>
      </ControlsPane>
    );
  }
}
