import React from 'react';
import { ControlsPane } from '../ControlsPane';
import { ControlItem } from '../ControlItem';
import { EndChatContainer } from '../../../../EndChat/EndChatContainer';
import { EndChatButton } from './EndChatButton';

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
        <ControlItem>
          <span className="dpdesignportal-checkbox-container">
            <span className="dpdesignportal-checkbox"><i className="fa fa-check"></i></span>
            Chat Transcript <i className="fa fa-exclamation-circle"></i>
          </span>
        </ControlItem>

        <EndChatContainer confirmPosition="bottom">
          <EndChatButton />
        </EndChatContainer>
      </ControlsPane>
    );
  }
}
