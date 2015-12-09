import React from 'react';
import { ControlsPane } from './ControlsPane';
import { AssetsContainer } from './Assets/AssetsContainer';
import { ReopenChatContainer } from '../../ReopenChatContainer';
import { ReopenChatButton } from './EndChat/ReopenChatButton';

export class DonePane extends React.Component {

  render() {
    return (
      <ControlsPane>
        <li>
          <AssetsContainer />
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
