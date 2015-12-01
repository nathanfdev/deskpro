import React from 'react';
import { ControlsPane } from '../ControlsPane';
import { ControlItem } from '../ControlItem';
import { ReopenChatContainer } from '../../../../ReopenChatContainer';
import { ReopenChatButton } from './ReopenChatButton';

export class DonePane extends React.Component {

  render() {
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
}
