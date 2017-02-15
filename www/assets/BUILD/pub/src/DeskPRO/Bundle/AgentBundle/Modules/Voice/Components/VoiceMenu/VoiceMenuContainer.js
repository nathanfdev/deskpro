import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { allPeopleSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/people';
import { voiceAgentsSelector, voiceOnlineAgentsSelector, outboundCallsEnabledSelector, callsEnabledSelector } from '../../Selectors/agents';
import VoiceMenuDropdown from './VoiceMenuDropdown';
import { acceptPhoneCall, declinePhoneCall } from '../../Actions/clientActions';
import { incomingCallSelector, outboundNumberSelector } from '../../Selectors/client';

@connect(state => ({
  me:                   meSelector(state),
  agents:               voiceAgentsSelector(state),
  onlineAgents:         voiceOnlineAgentsSelector(state),
  people:               allPeopleSelector(state),
  incomingCall:         incomingCallSelector(state),
  outboundCallsEnabled: outboundCallsEnabledSelector(state),
  outboundNumber:       outboundNumberSelector(state),
  voiceEnabled:         callsEnabledSelector(state)
}))
class VoiceMenuContainer extends React.Component {

  static propTypes = {
    dispatch:     PropTypes.func,
    incomingCall: PropTypes.object
  };

  componentWillReceiveProps(newProps) {
    const { incomingCall } = this.props;

    if (!newProps.incomingCall && incomingCall) {
      this.popup.closePopup();
    }
  }

  onAcceptCall = () => {
    const { incomingCall, dispatch } = this.props;

    dispatch(acceptPhoneCall(incomingCall));
    this.popup.closePopup();
  };

  onDeclineCall = () => {
    const { incomingCall, dispatch } = this.props;

    dispatch(declinePhoneCall(incomingCall));
    this.popup.closePopup();
  };

  render() {
    return (
      <VoiceMenuDropdown
        ref={(c) => { this.popup = c; }}
        {...this.props}
        onAcceptCall={this.onAcceptCall}
        onDeclineCall={this.onDeclineCall}
      />
    );
  }
}

export default VoiceMenuContainer;
