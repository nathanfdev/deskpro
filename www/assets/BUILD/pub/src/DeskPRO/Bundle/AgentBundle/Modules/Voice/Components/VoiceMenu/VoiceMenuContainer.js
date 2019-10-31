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
import { loadVoiceMissedAgentCalls } from '../../Actions/voiceMissedAgentCallActions';
import { allVoiceMissedAgentCallsSelector } from '../../Selectors/voicemailRecords';
import '../../../../Resources/sounds/incoming-call.mp3';
import '../../../../Resources/sounds/incoming-call.ogg';
import '../../../../Resources/sounds/incoming-call.wav';

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
  records:               allVoiceMissedAgentCallsSelector(state)
}))
class VoiceMenuContainer extends React.Component {

  static propTypes = {
    dispatch:     PropTypes.func,
    incomingCall: PropTypes.object,
    outgoingCall: PropTypes.object,
    records:      PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      mp3: null,
      wav: null,
      ogg: null
    };
  }

  componentDidMount() {
    this.props.dispatch(loadVoiceMissedAgentCalls());
    const soundsPath = `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/sounds`;

    this.preloadIncomingCalls(`${soundsPath}/incoming-call.mp3`, 'mp3');
    this.preloadIncomingCalls(`${soundsPath}/incoming-call.ogg`, 'ogg');
    this.preloadIncomingCalls(`${soundsPath}/incoming-call.wav`, 'wav');
  }

  componentWillReceiveProps(newProps) {
    const { incomingCall, outgoingCall } = this.props;

    if ((!newProps.incomingCall && incomingCall) || (!newProps.outgoingCall && outgoingCall)) {
      this.popup.closePopup();
    }
  }

  preloadIncomingCalls(sound, prop) {
    const req = new XMLHttpRequest();
    req.open('GET', sound, true);
    req.responseType = 'blob';
    req.onload = () => {
      if (req.status === 200) {
        const blob = req.response;
        const state = {};
        state[prop] = URL.createObjectURL(blob);
        this.setState(state);
      }
    };
    req.onerror = (e) => {
      console.error('Can\'t load an audio file!', e);
    };
    req.send();
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
        {...this.state}
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
