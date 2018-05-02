import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import moment from 'moment';

class CallStatus extends React.Component {

  static propTypes = {
    call:          PropTypes.object,
    showDateEnded: PropTypes.bool
  };

  render() {
    const { call, showDateEnded } = this.props;
    const dateStarted = call.get('date_started');
    const dateEnded = call.get('date_ended');
    const duration = dateEnded && dateStarted ? moment(dateEnded).unix() - moment(dateStarted).unix() : 0;

    let status = call.get('status');
    if (status === 'ended' && !duration) {
      status = 'missed';
    }

    return (
      <td
        className={classNames({
          success: status === 'ended' && duration > 0,
          warning: status === 'missed'
        })}
      >
        {status.toUpperCase()}
        {dateEnded && showDateEnded && ` (${moment(dateEnded).format('L LT')})`}
      </td>
    );
  }
}

export default CallStatus;
