import React, { PropTypes } from 'react';
import { Event } from './Event';

export class JoinedEvent extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;
    const content = JSON.parse(message.get('content'));

    return (
      <Event {...this.props}>
        <span className="dpdesignportal-event-title">{content.name} has joined the chat</span>
      </Event>
    );
  }
}
