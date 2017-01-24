import React, { PropTypes } from 'react';
import { TabButton, Tab } from 'DeskPRO/Component/Tab/Tab';
import Settings from './Settings/Settings';
import DialpadContainer from './Dialpad/DialpadContainer';
import IncomingCall from './IncomingCall/IncomingCall';

class VoiceMenu extends React.Component {

  static propTypes = {
    me:              PropTypes.object,
    agents:          PropTypes.object,
    people:          PropTypes.object,
    incomingCall:    PropTypes.object,
    outboundNumbers: PropTypes.object,
    onAcceptCall:    PropTypes.func,
    onDeclineCall:   PropTypes.func
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
    const { me, agents, people, incomingCall, onAcceptCall, onDeclineCall } = this.props;

    if (incomingCall) {
      return (
        <IncomingCall
          me={me}
          agents={agents}
          people={people}
          incomingCall={incomingCall}
          onAccept={onAcceptCall}
          onDecline={onDeclineCall}
        />
      );
    }

    return <DialpadContainer />;
  }

  render() {
    const { me, incomingCall, outboundNumbers } = this.props;
    const { tabName } = this.state;
    const hasPhoneTab = incomingCall || (outboundNumbers.size > 0 && me.getIn(['agent_data', 'outbound_calls_enabled']));

    return (
      <div className="voice-menu">
        <div className="voice-header">
          Calls
        </div>
        {hasPhoneTab &&
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
        </div>}
        <Tab active={tabName === 'settings'}>
          <Settings />
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
