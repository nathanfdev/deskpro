import React from 'react';
import { ControlsPane } from '../ControlsPane';
import { ControlItem } from '../ControlItem';
import { ReopenChatContainer } from '../../../../ReopenChatContainer';
import { ReopenChatButton } from './ReopenChatButton';

export class DonePane extends React.Component {

  render() {
    return (
      <ControlsPane>
        <li>
          <ControlItem>
            <i className="fa fa-angle-double-left"></i>Assets
          </ControlItem>
        </li>
        <li>
          <ReopenChatContainer>
            <ReopenChatButton />
          </ReopenChatContainer>
        </li>
      </ControlsPane>
    );
  }
}
