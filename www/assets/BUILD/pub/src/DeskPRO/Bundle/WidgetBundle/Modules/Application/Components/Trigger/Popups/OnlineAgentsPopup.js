import React, { PropTypes } from 'react';
import { AgentAvatars } from './AgentAvatars';
import { ChatPopup } from './ChatPopup';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class OnlineAgentsPopup extends React.Component {

  static propTypes = {
    backgroundColor: PropTypes.string,
    textColor:       PropTypes.string,
    popupStyle:      PropTypes.string,
    onlineAgents:    PropTypes.object,
    onClick:         PropTypes.func,
    onClose:         PropTypes.func,
    liveDemo:        PropTypes.bool
  };

  onClick = event => {
    event.preventDefault();
    this.props.onClick();
  };

  onClose = event => {
    event.preventDefault();
    this.props.onClose();
  };

  render() {
    const { backgroundColor, textColor, onlineAgents } = this.props;

    return (
      <ChatPopup small {...this.props}>
        <span className="close-panel" onClick={this.onClose}>
          <i className="fa fa-close" />
        </span>
        <div>
          <div className="preemtive-chat-content" onClick={this.onClick}>
            <h1>
              <span>Agents Online</span>
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
                href="#"
                onClick={this.onClick}
                className="wide"
                style={{
                  backgroundColor,
                  color: textColor
                }}
              >
                {portalPhrases.get('portal.chat.start_conversation')}
              </a>
            </div>
          </div>
        </div>
      </ChatPopup>
    );
  }
}
