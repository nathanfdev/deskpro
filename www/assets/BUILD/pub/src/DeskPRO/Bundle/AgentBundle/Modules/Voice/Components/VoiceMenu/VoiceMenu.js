import React, { PropTypes } from 'react';
import { TabButton, Tab } from 'DeskPRO/Component/Tab/Tab';
import Settings from './Settings/Settings';
import DialpadContainer from './Dialpad/DialpadContainer';
import IncomingCall from './IncomingCall/IncomingCall';

class VoiceMenu extends React.Component {

  static propTypes = {
    callFrom:         PropTypes.object,
    callTarget:       PropTypes.object,
    onChangeSettings: PropTypes.onChange,
    onAcceptCall:     PropTypes.func,
    onDeclineCall:    PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      tabName: 'settings'
    };
  }

  onChangeTab = (tabName) => {
    this.setState({ tabName });
  };

  getPhoneTabName() {
    return this.hasIncomingCall() ? 'Incoming call ...' : 'Dialpad';
  }

  getPhoneTabIcon() {
    return this.hasIncomingCall() ? 'fa-phone' : 'fa-th';
  }

  hasIncomingCall() {
    const { callFrom, callTarget  } = this.props;
    return callFrom || callTarget;
  }

  renderPhoneTab() {
    const { callFrom, callTarget, onAcceptCall, onDeclineCall  } = this.props;
    if (this.hasIncomingCall()) {
      return (
        <IncomingCall
          callFrom={callFrom}
          callTarget={callTarget}
          onAccept={onAcceptCall}
          onDecline={onDeclineCall}
        />
      );
    }

    return <DialpadContainer />;
  }

  render() {
    const { onChangeSettings } = this.props;
    const { tabName } = this.state;

    return (
      <div className="voice-menu">
        <div className="voice-header">
          Calls
        </div>
        <div className="tab-menu">
          <TabButton
            tabName="settings"
            title="Settings"
            iconClass="fa-gear"
            onClick={this.onChangeTab}
            active={tabName === 'settings'}
          />
          <TabButton
            tabName="phone"
            title={this.getPhoneTabName()}
            iconClass={this.getPhoneTabIcon()}
            onClick={this.onChangeTab}
            active={tabName === 'phone'}
          />
        </div>
        <Tab active={tabName === 'settings'}>
          <Settings onChange={onChangeSettings} />
        </Tab>
        <Tab active={tabName === 'phone'}>
          {this.renderPhoneTab()}
        </Tab>
      </div>
    );
  }
}

export default VoiceMenu;
