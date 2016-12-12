import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { voiceAgentsSelector } from '../../Selectors/agents';
import VoiceMenuDropdown from './VoiceMenuDropdown';
import { acceptPhoneCall, declinePhoneCall } from '../../Actions/clientActions';
import { incomingCallSelector } from '../../Selectors/client';

@connect(state => ({
  me:           meSelector(state),
  agents:       voiceAgentsSelector(state),
  people:       collectionSelectorFactory('Person', 'all')(state),
  incomingCall: incomingCallSelector(state)
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
