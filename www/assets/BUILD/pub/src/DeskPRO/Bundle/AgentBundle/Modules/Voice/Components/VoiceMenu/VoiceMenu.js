import React, { PropTypes } from 'react';
import { TabButton, Tab } from 'DeskPRO/Component/Tab/Tab';
import Settings from './Settings/Settings';
import DialpadContainer from './Dialpad/DialpadContainer';
import IncomingCall from './IncomingCall/IncomingCall';

class VoiceMenu extends React.Component {

  static propTypes = {
    me:            PropTypes.object,
    agents:        PropTypes.object,
    incomingCall:  PropTypes.object,
    onAcceptCall:  PropTypes.func,
    onDeclineCall: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      tabName: props.incomingCall ? 'phone' : 'settings'
    };
  }

  componentWillReceiveProps(newProps) {
    if (newProps.incomingCall) {
      this.setState({
        tabName: 'phone'
      });
    }
  }

  onChangeTab = (tabName) => {
    this.setState({ tabName });
  };

  getPhoneTabName() {
    return this.props.incomingCall ? 'Incoming call ...' : 'Dialpad';
  }

  getPhoneTabIcon() {
    return this.props.incomingCall ? 'fa-phone' : 'fa-th';
  }

  renderPhoneTab() {
    const { me, agents, incomingCall, onAcceptCall, onDeclineCall } = this.props;

    if (incomingCall) {
      return (
        <IncomingCall
          me={me}
          agents={agents}
          incomingCall={incomingCall}
          onAccept={onAcceptCall}
          onDecline={onDeclineCall}
        />
      );
    }

    return <DialpadContainer />;
  }

  render() {
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
          <Settings />
        </Tab>
        <Tab active={tabName === 'phone'}>
          {this.renderPhoneTab()}
        </Tab>
      </div>
    );
  }
}

export default VoiceMenu;
