import React, { PropTypes } from 'react';
import { ControlsPane } from './ControlsPane';
import { ControlItem } from './ControlItem';
import { EndChatContainer } from '../../EndChat/EndChatContainer';
import { EndChatButton } from './EndChatButton';
import { ReopenChatContainer } from '../../ReopenChatContainer';
import { ReopenChatButton } from './ReopenChatButton';

export class Controls extends React.Component {

  static propTypes = {
    isEnded: PropTypes.bool
  };

  renderActive() {
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

  renderDone() {
    return (
      <ControlsPane>
        <ControlItem>
          <i className="fa fa-angle-double-left"></i>Assets
        </ControlItem>

        <ReopenChatContainer>
          <ReopenChatButton />
        </ReopenChatContainer>
      </ControlsPane>
    );
  }

  render() {
    return this.props.isEnded ? this.renderDone() : this.renderActive();
  }
}
