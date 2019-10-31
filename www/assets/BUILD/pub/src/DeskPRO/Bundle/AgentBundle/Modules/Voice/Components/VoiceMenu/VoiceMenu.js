import PropTypes from 'prop-types';
import React from 'react';
import { TabButton, Tab } from 'DeskPRO/Component/Tab/Tab';
import Settings from './Settings/Settings';
import Dialpad from './Dialpad/Dialpad';
import DialpadContainer from './Dialpad/DialpadContainer';
import IncomingCall from './IncomingCall/IncomingCall';
import OutgoingCallContainer from './OutgoingCall/OutgoingCallContainer';
import MissedCallsListContainer from './MissedCalls/MissedCallsListContainer';

class VoiceMenu extends React.Component {

  static propTypes = {
    mp3:                  PropTypes.string,
    wav:                  PropTypes.string,
    ogg:                  PropTypes.string,
    me:                   PropTypes.object,
    agents:               PropTypes.object,
    people:               PropTypes.object,
    queues:               PropTypes.object,
    incomingCall:         PropTypes.object,
    acceptCall:           PropTypes.func,
    declineCall:          PropTypes.func,
    hideCall:             PropTypes.func,
    onHangup:             PropTypes.func,
    outboundCallsEnabled: PropTypes.bool,
    outgoingCall:         PropTypes.object,
    ringingVolume:        PropTypes.number,
    callsEnabled:         PropTypes.bool,
    voiceEnabled:         PropTypes.bool,
    micEnabled:           PropTypes.bool,
    openUserMenu:         PropTypes.func,
    recordsCount:         PropTypes.number,
    defaultTab:           PropTypes.string
  };

  constructor(props) {
    super(props);
    const { incomingCall, outgoingCall, outboundCallsEnabled, defaultTab } = this.props;
    let tabName;
    if (defaultTab) {
      tabName = defaultTab;
    } else if (incomingCall || outgoingCall || outboundCallsEnabled) {
      tabName = 'phone';
    } else {
      tabName = 'settings';
    }

    this.state = { tabName };
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
    const { me, agents, people, queues, incomingCall, ringingVolume } = this.props;
    const { outgoingCall, acceptCall, declineCall, hideCall, onHangup } = this.props;
    const { mp3, wav, ogg } = this.props;

    if (incomingCall) {
      return (
        <IncomingCall
          mp3={mp3}
          wav={wav}
          ogg={ogg}
          me={me}
          agents={agents}
          people={people}
          queues={queues}
          incomingCall={incomingCall}
          ringingVolume={ringingVolume}
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
    const { me, outboundCallsEnabled, incomingCall, outgoingCall, openUserMenu, recordsCount } = this.props;
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
            tabName="missed_calls"
            title={recordsCount > 0 ? <span className="tab-item-title">Missed <span className="tab-item-count">{recordsCount}</span></span> : 'Missed'}
            iconClass="fa-play-circle"
            onClick={this.changeTab}
            active={tabName === 'missed_calls'}
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
        <Tab active={tabName === 'missed_calls'}>
          <MissedCallsListContainer />
        </Tab>
        <Tab active={tabName === 'settings'}>
          <Settings me={me} ref={(c) => { this.settings = c; }} />
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
