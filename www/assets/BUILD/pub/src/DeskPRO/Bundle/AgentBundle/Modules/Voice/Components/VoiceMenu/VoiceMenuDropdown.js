import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import PopUp from 'DeskPRO/Component/Semantic/PopUp/PopUp';
import VoiceMenu from './VoiceMenu';

class VoiceMenuDropdown extends React.Component {

  static propTypes = {
    voiceSynced:  PropTypes.bool,
    isSecure:     PropTypes.bool,
    micEnabled:   PropTypes.bool,
    incomingCall: PropTypes.object,
    outgoingCall: PropTypes.object,
    onlineAgents: PropTypes.object,
    voiceEnabled: PropTypes.bool,
    callsEnabled: PropTypes.bool,
    openUserMenu: PropTypes.func
  };

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
    const { onlineAgents, callsEnabled, voiceSynced, isSecure, micEnabled } = this.props;
    const status = callsEnabled ? 'agent.general.on' : 'agent.general.off';

    if (!voiceSynced) {
      return (
        <div className="status">
          Syncing
          <i className="spinner-flat" />
        </div>
      );
    }

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
    const { onlineAgents, callsEnabled, voiceEnabled, micEnabled } = this.props;

    if (voiceEnabled && micEnabled) {
      if (callsEnabled) {
        return <i className="ui call icon green voice-menu-icon" />;
      } else if (onlineAgents.size > 0) {
        return <i className="ui call icon yellow voice-menu-icon" />;
      }
    }

    return <i className="ui call icon red voice-menu-icon" />;
  }

  openUserMenu = (event) => {
    event.preventDefault();

    this.closePopup();
    this.props.openUserMenu();
  };

  closePopup = () => {
    this.popup.closePopup();
  };

  openDialpad = (outgoingNumber, ticketId = null, ticketTitle = null) => {
    this.popup.openPopup();
    setTimeout(() => {
      this.voiceMenu.changeTab('phone');
      setTimeout(() => {
        this.voiceMenu.dialpad.setOutgoingNumber(outgoingNumber);
        if (ticketId) {
          this.voiceMenu.dialpad.setTicket(ticketId, ticketTitle);
        }
      }, 1);
    }, 1);
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
              ref={(c) => { this.voiceMenu = c; }}
              openUserMenu={this.openUserMenu}
            />
          }
          className={classNames('voice-menu-popup', { green: incomingCall })}
          allowCloseOnClickOut={!incomingCall && !outgoingCall}
        >
          {this.getIcon()}
          {this.getStatus()}
        </PopUp>
      </div>
    );
  }
}

export default VoiceMenuDropdown;
