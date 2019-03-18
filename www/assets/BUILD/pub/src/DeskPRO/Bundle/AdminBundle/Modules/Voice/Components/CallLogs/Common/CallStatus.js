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

    if (status === 'voicemail') {
      const recordings = call.get('recordings');
      const agentVoicemail = call.get('agent_voicemail');

      if (!recordings.size && !agentVoicemail) {
        // call was redirected to voicemail
        // but no message was actually recorded
        // so just display as 'missed' in this case
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
