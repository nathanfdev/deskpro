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
    const dateEnded = call.get('date_ended');
    const agentParticipants = call.get('participants').filter(participant => participant.get('type') === 'agent');
    const userParticipants = call.get('participants').filter(participant => participant.get('type') === 'user');

    let status = call.get('status');
    if (status === 'ended') {
      if ((call.get('type') === 'inbound' && !agentParticipants.size)
        || (call.get('type') === 'outbound' && !userParticipants.size)) {
        status = 'missed';
      }
    }

    return (
      <td
        className={classNames({
          success: status === 'ended',
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
