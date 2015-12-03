import React, { PropTypes } from 'react';
import TimeAgo from 'react-timeago';

export class MessageFooter extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  render() {
    const { message } = this.props;

    return (
      <div className="dpdesignportal-message-footer">
        {false && <a href="#" className="dpdesignportal-message-footer-assets-link"><i className="fa fa-copy"></i> Chat Assets (4)</a>}
        <TimeAgo className="dpdesignportal-message-footer-timer" minPeriod={60000} date={message.get('date_created')} />
      </div>
    );
  }
}
