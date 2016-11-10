import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import VoiceMenuDropdown from './VoiceMenuDropdown';
import { acceptPhoneCall, declinePhoneCall, hangup } from '../../Actions/clientActions';
import { reservationSelector } from '../../Selectors/client';

@connect(state => ({
  agents:      agentsSelector(state),
  reservation: reservationSelector(state)
}))
class VoiceMenuContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  onAcceptCall = () => {
    this.props.dispatch(acceptPhoneCall());
  };

  onDeclineCall = () => {
    this.props.dispatch(declinePhoneCall());
  };

  onHangup = () => {
    this.props.dispatch(hangup());
  };

  render() {
    return (
      <VoiceMenuDropdown
        {...this.props}
        onAcceptCall={this.onAcceptCall}
        onDeclineCall={this.onDeclineCall}
        onHangup={this.onHangup}
      />
    );
  }
}

export default VoiceMenuContainer;
