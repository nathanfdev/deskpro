import React, { PropTypes } from 'react';
import TimeAgo from 'react-timeago';

export class JoinedEvent extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;
    const content = JSON.parse(message.get('content'));

    return (
      <div className="dpdesignportal-event">
        <div className="dpdesignportal-event-content">
          <hr/>
          <span className="dpdesignportal-event-title">{content.name} has joined the chat</span>
          <TimeAgo className="dpdesignportal-event-time" minPeriod={60000} date={message.get('date_created')} />
        </div>
      </div>
    );
  }
}
