import React, { PropTypes } from 'react';
import TimeAgo from 'react-timeago';

export class InlineEvent extends React.Component {

  static propTypes = {
    message: PropTypes.object,
    children: PropTypes.any
  };

  render() {
    const { message, children } = this.props;

    return (
      <div className="dpdesignportal-event">
        <div className="dpdesignportal-event-content">
          <TimeAgo className="dpdesignportal-event-time" minPeriod={60000} date={message.get('date_created')} />
          <hr/>
          <span className="dpdesignportal-event-title">
            {children}
          </span>
        </div>
      </div>
    );
  }
}
