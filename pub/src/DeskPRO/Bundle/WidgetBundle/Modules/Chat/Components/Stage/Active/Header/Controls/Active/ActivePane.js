import React from 'react';
import { ControlsPane } from '../ControlsPane';
import { ControlItem } from '../ControlItem';
import { EndChatContainer } from '../../../EndChat/EndChatContainer';
import { EndChatButton } from './EndChatButton';
import { TranscriptContainer } from './Transcript/TranscriptContainer';
import { TranscriptButton } from './Transcript/TranscriptButton';

export class ActivePane extends React.Component {

  render() {
    return (
      <ControlsPane>
        <li>
          <ControlItem>
            <i className="fa fa-angle-double-left"></i>Assets
          </ControlItem>
        </li>
        <li>
          <ControlItem className="dpdesignportal-chat-header-control-mute">
            <i className="fa fa-volume-up"></i>Mute
          </ControlItem>
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
