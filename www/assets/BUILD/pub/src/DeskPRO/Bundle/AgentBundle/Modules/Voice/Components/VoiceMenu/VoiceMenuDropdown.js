import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import PopUp from 'DeskPRO/Component/Semantic/PopUp/PopUp';
import Isvg from 'react-inlinesvg';
import VoiceMenu from './VoiceMenu';

class VoiceMenuDropdown extends React.Component {

  static propTypes = {
    me:           PropTypes.object,
    isSecure:     PropTypes.bool,
    micEnabled:   PropTypes.bool,
    incomingCall: PropTypes.object,
    outgoingCall: PropTypes.object,
    onlineAgents: PropTypes.object,
    voiceEnabled: PropTypes.bool,
    callsEnabled: PropTypes.bool,
    openUserMenu: PropTypes.func,
    recordsCount: PropTypes.number
  };

  constructor(props) {
    super(props);
    this.state = {
      defaultTab: null
    };
  }

  componentDidMount() {
    const { incomingCall } = this.props;
    window.AgentVoiceDropdown = this;

    if (incomingCall) {
      this.popup.openPopup();
    }
  }

  componentWillReceiveProps(newProps) {
    const { incomingCall } = this.props;

    if (!incomingCall && newProps.incomingCall) {
      this.popup.openPopup();
    }
  }

  getStatus() {
    const { onlineAgents, callsEnabled, isSecure, micEnabled } = this.props;
    const status = callsEnabled ? 'agent.general.on' : 'agent.general.off';

    if (!isSecure) {
      return (
        <div className="status">
          Insecure
        </div>
      );
    }

    if (!micEnabled) {
      return (
        <div className="status">
          Mic disabled
        </div>
      );
    }

    return (
      <div className="status">
        <FormattedMessage id={status} /> <span className="count">({onlineAgents.size})</span>
      </div>
    );
  }

  getIcon() {
    const { me, onlineAgents, callsEnabled, voiceEnabled, micEnabled } = this.props;
    const canUseForwarding = me.getIn(['agent_data', 'agent_can_use_forwarding']) && me.getIn(['agent_data', 'forwarding_number']);

    if (voiceEnabled && micEnabled) {
      if (callsEnabled) {
        if (canUseForwarding) {
          return (
            <Isvg
              className="voice-menu-icon"
              src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/call-forwarding.svg`}
            />
          );
        }

        return (
          <Isvg
            className="voice-menu-icon"
            src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/call.svg`}
          />
        );
      } else if (onlineAgents.size > 0) {
        return (
          <Isvg
            className="voice-menu-icon"
            src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/call-another-agent.svg`}
          />
        );
      }
    }

    return (
      <Isvg
        className="voice-menu-icon"
        src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/call-offline.svg`}
      />
    );
  }

  showProviderError = (outgoingNumber, errors) => {
    this.popup.openPopup();
    setTimeout(() => {
      this.voiceMenu.changeTab('phone');
      setTimeout(() => {
        this.voiceMenu.dialpad.setOutgoingNumber(outgoingNumber);
        setTimeout(() => { this.voiceMenu.dialpad.showProviderError(errors); }, 1);
      }, 1);
    }, 1);
  };

  openUserMenu = (event) => {
    event.preventDefault();

    this.closePopup();
    this.props.openUserMenu();
  };

  closePopup = () => {
    this.popup.closePopup();
  };

  openDialpad = (outgoingNumber, ticketId = null, ticketTitle = null) => {
    this.setState({
      defaultTab: 'phone'
    }, () => {
      this.popup.openPopup();
      setTimeout(() => {
        this.voiceMenu.changeTab('phone');
        setTimeout(() => {
          this.voiceMenu.dialpad.setOutgoingNumber(outgoingNumber);
          if (ticketId) {
            this.voiceMenu.dialpad.setTicket(ticketId, ticketTitle);
          }
        }, 1);

        setTimeout(() => {
          this.setState({
            defaultTab: null
          });
        }, 1);
      }, 1);
    });
  };

  openSettingsTab = () => {
    this.setState({
      defaultTab: 'settings'
    }, () => {
      this.popup.openPopup();
      setTimeout(() => {
        this.voiceMenu.settings.panels.setActiveKey(['0']);
        this.setState({
          defaultTab: null
        });
      }, 1);
    });
  };

  renderCount = () => {
    const { recordsCount } = this.props;
    if (parseInt(recordsCount, 10) > 0) {
      return <div className="ui knuckles label">{recordsCount}</div>;
    }
    return null;
  };

  render() {
    const { incomingCall, outgoingCall } = this.props;

    return (
      <div className="voice">
        <PopUp
          ref={(c) => { this.popup = c; }}
          positionMy="right top"
          positionAt="right bottom"
          zIndex={99999}
          content={
            <VoiceMenu
              {...this.props}
              {...this.state}
              ref={(c) => { this.voiceMenu = c; }}
              openUserMenu={this.openUserMenu}
            />
          }
          className={classNames('voice-menu-popup', { green: incomingCall })}
          allowCloseOnClickOut={!incomingCall && !outgoingCall}
        >
          {this.getIcon()}
          {this.renderCount()}
          {this.getStatus()}
        </PopUp>
      </div>
    );
  }
}

export default VoiceMenuDropdown;
