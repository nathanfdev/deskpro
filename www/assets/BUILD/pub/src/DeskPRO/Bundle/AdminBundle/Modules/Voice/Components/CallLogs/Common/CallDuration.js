import PropTypes from 'prop-types';
import React from 'react';
import Duration from 'DeskPRO/Component/Duration';
import moment from 'moment';

class CallDuration extends React.Component {

  static propTypes = {
    call: PropTypes.object
  };

  render() {
    const { call } = this.props;
    const dateStarted = call.get('date_started');
    const dateEnded = call.get('date_ended');
    const duration = dateEnded && dateStarted ? moment(dateEnded).unix() - moment(dateStarted).unix() : 0;

    return <Duration value={duration} />;
  }
}

export default CallDuration;
