import React, { PropTypes } from 'react';
import { ControlsPane } from './ControlsPane';
import { ControlItem } from './ControlItem';
import { EndChatButtonContainer } from '../../EndChat/EndChatButtonContainer';
import { EndChatButton } from './EndChatButton';

export class Controls extends React.Component {

  static propTypes = {
    ended: PropTypes.bool
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

        <EndChatButtonContainer confirmPosition="bottom">
          <EndChatButton />
        </EndChatButtonContainer>
      </ControlsPane>
    );
  }

  renderDone() {
    return (
      <ControlsPane>
        <ControlItem>
          <i className="fa fa-angle-double-left"></i>Assets
        </ControlItem>
        <ControlItem>
          Reopen Chat <i className="fa fa-commenting-o"></i>
        </ControlItem>
      </ControlsPane>
    );
  }

  render() {
    return this.props.ended ? this.renderDone() : this.renderActive();
  }
}
