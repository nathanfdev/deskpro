import PropTypes from 'prop-types';
import React from 'react';
import { AssetsContainer } from './Assets/AssetsContainer';
import { MuteContainer } from './Mute/MuteContainer';
import { TranscriptContainer } from './Transcript/TranscriptContainer';
import { TranscriptButton } from './Transcript/TranscriptButton';
import { EndChatButtonContainer } from './EndChat/EndChatButtonContainer';
import classNames from 'classnames';

export class ControlsPane extends React.Component {

  static propTypes = {
    isEnded: PropTypes.bool
  };

  render() {
    return (
      <div className={classNames('dpdesignportal-chat-header-controls', { ended: this.props.isEnded })}>
        <ul>
          {false /* disabled for now */ &&
            <li>
              <AssetsContainer />
            </li>
          }
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
