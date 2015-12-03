import React, { PropTypes } from 'react';
import moment from 'moment';

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
          <span className="dpdesignportal-event-time">{moment(message.get('date_created')).format('h:mma')}</span>
        </div>
      </div>
    );
  }
}
