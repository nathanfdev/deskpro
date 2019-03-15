import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { allPeopleSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/people';
import { voiceAgentsSelector, voiceOnlineAgentsSelector, outboundCallsEnabledSelector, callsEnabledSelector } from '../../Selectors/agents';
import VoiceMenuDropdown from './VoiceMenuDropdown';
import { acceptPhoneCall, declinePhoneCall } from '../../Actions/clientActions';
import { incomingCallSelector, outboundNumberSelector, outgoingCallSelector, ringingVolumeSelector, agentVoicemailTimeoutSelector, isVoiceMicEnabled, isVoiceEnabledSelector, isSecure } from '../../Selectors/client';
import { allQueuesSelector } from '../../Selectors/queue';
import { loadVoicemailRecords } from '../../Actions/voicemailRecordActions';
import { allVoicemailRecordsSelector } from '../../Selectors/voicemailRecords';

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
  callsEnabled:          callsEnabledSelector(state),
  outgoingCall:          outgoingCallSelector(state),
  ringingVolume:         ringingVolumeSelector(state),
  agentVoicemailTimeout: agentVoicemailTimeoutSelector(state),
  micEnabled:            isVoiceMicEnabled(state),
  records:               allVoicemailRecordsSelector(state)
}))
class VoiceMenuContainer extends React.Component {

  static propTypes = {
    dispatch:     PropTypes.func,
    incomingCall: PropTypes.object,
    outgoingCall: PropTypes.object,
    records:      PropTypes.object
  };

  componentDidMount() {
    this.props.dispatch(loadVoicemailRecords());
  }

  componentWillReceiveProps(newProps) {
    const { incomingCall, outgoingCall } = this.props;

    if ((!newProps.incomingCall && incomingCall) || (!newProps.outgoingCall && outgoingCall)) {
      this.popup.closePopup();
    }
  }

  acceptCall = () => {
    const { incomingCall, dispatch } = this.props;

    dispatch(acceptPhoneCall(incomingCall));
    this.popup.closePopup();
  };

  declineCall = () => {
    const { incomingCall, dispatch } = this.props;

    dispatch(declinePhoneCall(incomingCall));
    this.popup.closePopup();
  };

  hideCall = () => {
    this.popup.closePopup();
  };

  render() {
    return (
      <VoiceMenuDropdown
        ref={(c) => { this.popup = c; }}
        {...this.props}
        isSecure={isSecure}
        acceptCall={this.acceptCall}
        declineCall={this.declineCall}
        hideCall={this.hideCall}
        recordsCount={this.props.records.filter(recording => !recording.get('is_listened')).size}
      />
    );
  }
}

export default VoiceMenuContainer;
