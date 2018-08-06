import PropTypes from 'prop-types';
import React from 'react';
import { TabButton, Tab } from 'DeskPRO/Component/Tab/Tab';
import SettingsContainer from './Settings/SettingsContainer';
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
    onAcceptCall:          PropTypes.func,
    onDeclineCall:         PropTypes.func,
    onHangup:              PropTypes.func,
    outboundCallsEnabled:  PropTypes.bool,
    outboundNumber:        PropTypes.string,
    outgoingCall:          PropTypes.object,
    ringingVolume:         PropTypes.number,
    agentVoicemailTimeout: PropTypes.number,
    voiceSynced:           PropTypes.bool
  };

  constructor(props) {
    super(props);

    const { incomingCall, outgoingCall, outboundNumber } = this.props;
    this.state = {
      tabName: incomingCall || outgoingCall || outboundNumber ? 'phone' : 'settings'
    };
  }

  componentWillReceiveProps(newProps) {
    if (newProps.incomingCall || newProps.outgoingCall || newProps.outboundNumber) {
      this.setState({
        tabName: 'phone'
      });
    }
  }

  onChangeTab = (tabName) => {
    this.setState({ tabName });
  };

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

  renderPhoneTab() {
    const { me, agents, people, queues, incomingCall, ringingVolume, agentVoicemailTimeout } = this.props;
    const { outgoingCall, onAcceptCall, onDeclineCall, onHangup } = this.props;

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
          onAccept={onAcceptCall}
          onDecline={onDeclineCall}
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

    return <DialpadContainer />;
  }

  render() {
    const { outboundCallsEnabled, incomingCall, outgoingCall, voiceSynced } = this.props;
    const { tabName } = this.state;
    const hasPhoneTab = incomingCall || outboundCallsEnabled;
    const pendingCall = incomingCall || outgoingCall;

    return (
      <div className="voice-menu">
        <div className="voice-header">
          Calls
        </div>
        {!voiceSynced &&
        <div className="voice-menu-alert">
           Changes to your Voice settings are still being applied. This may take a minute or two.
        </div>}
        <div className="tab-menu">
          {!pendingCall &&
          <TabButton
            tabName="voicemail"
            title="Voicemail"
            iconClass="fa-play-circle"
            onClick={this.onChangeTab}
            active={tabName === 'voicemail'}
          />}
          {!pendingCall &&
          <TabButton
            tabName="settings"
            title="Settings"
            iconClass="fa-cog"
            onClick={this.onChangeTab}
            active={tabName === 'settings'}
          />}
          {hasPhoneTab &&
          <TabButton
            tabName="phone"
            title={this.getPhoneTabName()}
            iconClass={this.getPhoneTabIcon()}
            onClick={this.onChangeTab}
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
