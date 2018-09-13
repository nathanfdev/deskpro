import PropTypes from 'prop-types';
import React from 'react';
import { TabButton, Tab } from 'DeskPRO/Component/Tab/Tab';
import SettingsContainer from './Settings/SettingsContainer';
import Dialpad from './Dialpad/Dialpad';
import DialpadContainer from './Dialpad/DialpadContainer';
import IncomingCall from './IncomingCall/IncomingCall';
import OutgoingCallContainer from './OutgoingCall/OutgoingCallContainer';
import VoicemailListContainer from './Voicemail/VoicemailListContainer';

class VoiceMenu extends React.Component {

  static propTypes = {
    me:                    PropTypes.object,
    agents:                PropTypes.object,
    people:                PropTypes.object,
    queues:                PropTypes.object,
    incomingCall:          PropTypes.object,
    acceptCall:            PropTypes.func,
    declineCall:           PropTypes.func,
    hideCall:              PropTypes.func,
    onHangup:              PropTypes.func,
    outboundCallsEnabled:  PropTypes.bool,
    outgoingCall:          PropTypes.object,
    ringingVolume:         PropTypes.number,
    agentVoicemailTimeout: PropTypes.number,
    callsEnabled:          PropTypes.bool,
    voiceEnabled:          PropTypes.bool,
    micEnabled:            PropTypes.bool,
    openUserMenu:          PropTypes.func
  };

  constructor(props) {
    super(props);

    const { incomingCall, outgoingCall, outboundCallsEnabled } = this.props;
    this.state = {
      tabName: incomingCall || outgoingCall || outboundCallsEnabled ? 'phone' : 'settings'
    };
  }

  componentWillReceiveProps(newProps) {
    if (newProps.incomingCall || newProps.outgoingCall) {
      this.setState({
        tabName: 'phone'
      });
    }
  }

  getPhoneTabName() {
    const { incomingCall, outgoingCall } = this.props;

    if (incomingCall) {
      return 'Incoming call ...';
    } else if (outgoingCall) {
      return 'Outgoing call ...';
    }

    return 'Dialpad';
  }

  getPhoneTabIcon() {
    const { incomingCall, outgoingCall } = this.props;

    return incomingCall || outgoingCall ? 'fa-phone' : 'fa-th';
  }

  changeTab = (tabName) => {
    this.setState({ tabName });
  };

  renderPhoneTab() {
    const { me, agents, people, queues, incomingCall, ringingVolume, agentVoicemailTimeout } = this.props;
    const { outgoingCall, acceptCall, declineCall, hideCall, onHangup } = this.props;

    if (incomingCall) {
      return (
        <IncomingCall
          me={me}
          agents={agents}
          people={people}
          queues={queues}
          incomingCall={incomingCall}
          ringingVolume={ringingVolume}
          agentVoicemailTimeout={agentVoicemailTimeout}
          onAccept={acceptCall}
          onDecline={declineCall}
          hideCall={hideCall}
        />
      );
    } else if (outgoingCall) {
      return (
        <OutgoingCallContainer
          me={me}
          outgoingCall={outgoingCall}
          onHangup={onHangup}
          ringingVolume={ringingVolume}
        />
      );
    }

    return (
      <DialpadContainer>
        <Dialpad ref={(c) => { this.dialpad = c; }} />
      </DialpadContainer>
    );
  }

  render() {
    const { outboundCallsEnabled, incomingCall, outgoingCall, openUserMenu } = this.props;
    const { voiceEnabled, micEnabled, callsEnabled } = this.props;
    const { tabName } = this.state;
    const hasPhoneTab = incomingCall || outboundCallsEnabled;
    const pendingCall = incomingCall || outgoingCall;

    return (
      <div className="voice-menu">
        <div className="header">
          Calls
          <a className="online-status" onClick={openUserMenu}>
            {voiceEnabled && micEnabled && callsEnabled ? 'You are online' : 'You are offline'}
          </a>
        </div>
        <div className="tab-menu">
          {!pendingCall &&
          <TabButton
            tabName="voicemail"
            title="Voicemail"
            iconClass="fa-play-circle"
            onClick={this.changeTab}
            active={tabName === 'voicemail'}
          />}
          {!pendingCall &&
          <TabButton
            tabName="settings"
            title="Settings"
            iconClass="fa-cog"
            onClick={this.changeTab}
            active={tabName === 'settings'}
          />}
          {hasPhoneTab &&
          <TabButton
            tabName="phone"
            title={this.getPhoneTabName()}
            iconClass={this.getPhoneTabIcon()}
            onClick={this.changeTab}
            active={tabName === 'phone'}
          />}
        </div>
        <Tab active={tabName === 'voicemail'}>
          <VoicemailListContainer />
        </Tab>
        <Tab active={tabName === 'settings'}>
          <SettingsContainer />
        </Tab>
        {hasPhoneTab &&
        <Tab active={tabName === 'phone'}>
          {this.renderPhoneTab()}
        </Tab>}
      </div>
    );
  }
}

export default VoiceMenu;
