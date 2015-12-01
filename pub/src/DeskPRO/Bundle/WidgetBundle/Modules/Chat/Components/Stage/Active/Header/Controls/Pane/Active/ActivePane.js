import React from 'react';
import { ControlsPane } from '../ControlsPane';
import { ControlItem } from '../ControlItem';
import { EndChatContainer } from '../../../../EndChat/EndChatContainer';
import { EndChatButton } from './EndChatButton';
import { TranscriptContainer } from './TranscriptContainer';
import { TranscriptButton } from './TranscriptButton';

export class ActivePane extends React.Component {

  render() {
    return (
      <ControlsPane>
        <ControlItem>
          <i className="fa fa-angle-double-left"></i>Assets
        </ControlItem>
        <ControlItem className="dpdesignportal-chat-header-control-mute">
          <i className="fa fa-volume-up"></i>Mute
        </ControlItem>

        <TranscriptContainer>
          <TranscriptButton />
        </TranscriptContainer>

        <EndChatContainer confirmPosition="bottom">
          <EndChatButton />
        </EndChatContainer>
      </ControlsPane>
    );
  }
}
