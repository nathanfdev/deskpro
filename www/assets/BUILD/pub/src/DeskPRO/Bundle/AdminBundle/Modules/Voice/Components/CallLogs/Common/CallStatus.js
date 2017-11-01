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
    const status = call.get('status');
    const duration = dateEnded && dateStarted ? moment(dateEnded).unix() - moment(dateStarted).unix() : 0;

    return (
      <td
        className={classNames({
          success: status === 'ended' && duration > 0,
          warning: status === 'ended' && !duration
        })}
      >
        {status.toUpperCase()}
        {dateEnded && showDateEnded && ` (${moment(dateEnded).format('L LT')})`}
      </td>
    );
  }
}

export default CallStatus;
