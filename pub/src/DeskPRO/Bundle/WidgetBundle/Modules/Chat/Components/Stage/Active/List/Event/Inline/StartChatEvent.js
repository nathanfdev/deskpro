import React from 'react';
import { Event } from './Event';

export class StartChatEvent extends React.Component {

  render() {
    return (
      <Event {...this.props}>
        <span className="dpdesignportal-event-title">
          <div className="dpdesignportal-message-avatar"></div>
          Chat was started by you
        </span>
      </Event>
    );
  }
}
