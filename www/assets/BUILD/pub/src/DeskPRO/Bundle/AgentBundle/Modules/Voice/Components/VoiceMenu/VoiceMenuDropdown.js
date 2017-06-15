import React, { PropTypes } from 'react';
import classNames from 'classnames';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import PopUp from 'DeskPRO/Component/Semantic/PopUp/PopUp';
import VoiceMenu from './VoiceMenu';

class VoiceMenuDropdown extends React.Component {

  static propTypes = {
    micEnabled:     PropTypes.bool,
    incomingCall:   PropTypes.object,
    outgoingCall:   PropTypes.object,
    onlineAgents:   PropTypes.object,
    voiceEnabled:   PropTypes.bool,
    outboundNumber: PropTypes.string
  };

  componentDidMount() {
    const { incomingCall } = this.props;

    if (incomingCall) {
      this.popup.openPopup();
    }
  }

  componentWillReceiveProps(newProps) {
    const { incomingCall, outboundNumber } = this.props;

    if ((!incomingCall && newProps.incomingCall) || (!outboundNumber && newProps.outboundNumber)) {
      this.popup.openPopup();
    }
  }

  getStatus() {
    const { onlineAgents, voiceEnabled, micEnabled } = this.props;
    const status = voiceEnabled ? agentPhrases.get('agent.general.on') : agentPhrases.get('agent.general.off');

    if (!micEnabled) {
      return (
        <div className="status">
          Mic disabled
        </div>
      );
    }

    return (
      <div className="status">
        {status} <span className="count">({onlineAgents.size})</span>
      </div>
    );
  }

  getIcon() {
    const { onlineAgents, voiceEnabled, micEnabled } = this.props;

    if (micEnabled) {
      if (voiceEnabled) {
        return <i className="ui call icon green voice-menu-icon" />;
      } else if (onlineAgents.size > 0) {
        return <i className="ui call icon yellow voice-menu-icon" />;
      }
    }

    return <i className="ui call icon red voice-menu-icon" />;
  }

  closePopup = () => {
    this.popup.closePopup();
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
          content={<VoiceMenu {...this.props} />}
          className={classNames('voice-menu-popup', { green: incomingCall })}
          allowClose={!incomingCall && !outgoingCall}
        >
          {this.getIcon()}
          {this.getStatus()}
        </PopUp>
      </div>
    );
  }
}

export default VoiceMenuDropdown;
