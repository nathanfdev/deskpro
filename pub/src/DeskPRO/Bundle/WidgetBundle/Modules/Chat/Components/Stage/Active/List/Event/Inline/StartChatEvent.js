import React from 'react';
import { Event } from './Event';
import { MessageAvatar } from '../../Message/MessageAvatar';

export class StartChatEvent extends React.Component {

  render() {
    return (
      <Event {...this.props}>
        <span className="dpdesignportal-event-title">
          <MessageAvatar />
          Chat was started by you
        </span>
      </Event>
    );
  }
}
