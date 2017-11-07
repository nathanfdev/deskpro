import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { AgentAvatars } from './AgentAvatars';
import ChatPopup from './ChatPopup';

export class OnlineAgentsPopup extends React.Component {

  static propTypes = {
    backgroundColor:      PropTypes.string,
    textColor:            PropTypes.string,
    onlineAgents:         PropTypes.object,
    onClick:              PropTypes.func,
    onClose:              PropTypes.func,
    helpPopupStartButton: PropTypes.string
  };

  onClick = (event) => {
    event.preventDefault();
    this.props.onClick();
  };

  onClose = (event) => {
    event.preventDefault();
    this.props.onClose();
  };

  render() {
    const { backgroundColor, textColor, onlineAgents, helpPopupStartButton } = this.props;

    return (
      <ChatPopup small {...this.props}>
        <span className="close-panel" onClick={this.onClose}>
          <i className="fa fa-close" />
        </span>
        <div>
          <div className="preemtive-chat-content" onClick={this.onClick}>
            <h1>
              <span>{portalPhrases.get('portal.widget.online_agents')}</span>
            </h1>
            <div className="dpdesignportal-chat-header">
              <AgentAvatars onlineAgents={onlineAgents} />
            </div>
          </div>
        </div>
        <div className="preemtive-chat-content">
          <div className="preemtive-chat-footer">
            <div className="preemtive-chat-footer-button">
              <a
                href="/"
                onClick={this.onClick}
                className="wide"
                style={{
                  backgroundColor,
                  color: textColor
                }}
              >
                {helpPopupStartButton}
              </a>
            </div>
          </div>
        </div>
      </ChatPopup>
    );
  }
}
