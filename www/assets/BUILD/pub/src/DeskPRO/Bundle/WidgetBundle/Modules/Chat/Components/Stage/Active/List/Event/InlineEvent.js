import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';

export class InlineEvent extends React.Component {

  static propTypes = {
    message:  PropTypes.object,
    children: PropTypes.any
  };

  render() {
    const { message, children } = this.props;

    return (
      <div className="dpdesignportal-event">
        <div className="dpdesignportal-event-content">
          <span className="dpdesignportal-event-time">
            {moment(message.get('date_created')).format('HH:mm')}
          </span>
          <hr />
          <span className="dpdesignportal-event-title">
            {children}
          </span>
        </div>
      </div>
    );
  }
}
