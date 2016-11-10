import React, { PropTypes } from 'react';
import { TabButton, Tab } from 'DeskPRO/Component/Tab/Tab';
import Settings from './Settings/Settings';
import DialpadContainer from './Dialpad/DialpadContainer';
import IncomingCall from './IncomingCall/IncomingCall';

class VoiceMenu extends React.Component {

  static propTypes = {
    me:               PropTypes.object,
    reservation:      PropTypes.object,
    onChangeSettings: PropTypes.onChange,
    onAcceptCall:     PropTypes.func,
    onDeclineCall:    PropTypes.func,
    onHangup:         PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      tabName: props.reservation ? 'phone' : 'settings'
    };
  }

  componentWillReceiveProps(newProps) {
    if (newProps.reservation) {
      this.setState({
        tabName: 'phone'
      });
    }
  }

  onChangeTab = (tabName) => {
    this.setState({ tabName });
  };

  getPhoneTabName() {
    return this.props.reservation ? 'Incoming call ...' : 'Dialpad';
  }

  getPhoneTabIcon() {
    return this.props.reservation ? 'fa-phone' : 'fa-th';
  }

  renderPhoneTab() {
    const { me, reservation, onAcceptCall, onDeclineCall, onHangup } = this.props;
    if (reservation) {
      return (
        <IncomingCall
          me={me}
          reservation={reservation}
          onAccept={onAcceptCall}
          onDecline={onDeclineCall}
          onHangup={onHangup}
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
