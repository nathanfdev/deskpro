import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { allPeopleSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/people';
import { voiceAgentsSelector, voiceOnlineAgentsSelector, outboundCallsEnabledSelector, callsEnabledSelector } from '../../Selectors/agents';
import VoiceMenuDropdown from './VoiceMenuDropdown';
import { acceptPhoneCall, declinePhoneCall } from '../../Actions/clientActions';
import { incomingCallSelector, outboundNumberSelector, outgoingCallSelector, ringingVolumeSelector, agentVoicemailTimeoutSelector, isVoiceMicEnabled, isVoiceEnabledSelector, isVoiceSyncedSelector, isSecure } from '../../Selectors/client';
import { allQueuesSelector } from '../../Selectors/queue';

@connect(state => ({
  me:                    meSelector(state),
  agents:                voiceAgentsSelector(state),
  onlineAgents:          voiceOnlineAgentsSelector(state),
  people:                allPeopleSelector(state),
  queues:                allQueuesSelector(state),
  incomingCall:          incomingCallSelector(state),
  outboundCallsEnabled:  outboundCallsEnabledSelector(state),
  outboundNumber:        outboundNumberSelector(state),
  voiceEnabled:          isVoiceEnabledSelector(state),
  voiceSynced:           isVoiceSyncedSelector(state),
  callsEnabled:          callsEnabledSelector(state),
  outgoingCall:          outgoingCallSelector(state),
  ringingVolume:         ringingVolumeSelector(state),
  agentVoicemailTimeout: agentVoicemailTimeoutSelector(state),
  micEnabled:            isVoiceMicEnabled(state)
}))
class VoiceMenuContainer extends React.Component {

  static propTypes = {
    dispatch:     PropTypes.func,
    incomingCall: PropTypes.object,
    outgoingCall: PropTypes.object
  };

  componentWillReceiveProps(newProps) {
    const { incomingCall, outgoingCall } = this.props;

    if ((!newProps.incomingCall && incomingCall) || (!newProps.outgoingCall && outgoingCall)) {
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
        isSecure={isSecure}
        onAcceptCall={this.onAcceptCall}
        onDeclineCall={this.onDeclineCall}
      />
    );
  }
}

export default VoiceMenuContainer;
