import React, { PropTypes } from 'react';
import TimeAgo from 'react-timeago';

export class StartChatEvent extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;

    return (
      <div className="dpdesignportal-event">
        <div className="dpdesignportal-event-content">
          <TimeAgo className="dpdesignportal-event-time" minPeriod={60000} date={message.get('date_created')} />
          <hr />
          <span className="dpdesignportal-event-title">
            <div className="dpdesignportal-message-avatar"></div>
            Chat was started by you
          </span>
        </div>
      </div>
    );
  }
}
