import React, { PropTypes } from 'react';
import { AgentAvatars } from '../AgentAvatars';

export class AgentMessagePopup extends React.Component {

  static propTypes = {
    onlineAgents: PropTypes.object,
    onClick: PropTypes.func,
    children: PropTypes.node,
    helpPopupMessage: PropTypes.string
  };

  render() {
    const { onlineAgents, children, onClick, helpPopupMessage } = this.props;

    return (
      <div className="dpdesignportal-state-buttons dpdesignportal-agent-message">
        <div className="preemtive-chat">
          <div className="preemtive-chat-content" onClick={onClick}>
            <div className="dpdesignportal-chat-header">
              <AgentAvatars onlineAgents={onlineAgents} />

              <h1><span>Noelle Gray</span></h1>
              <h2>DeskPRO Customer Support</h2>
              <p className="quote">
                {helpPopupMessage}
              </p>
            </div>
          </div>
          <hr/>
          <div className="preemtive-chat-content">
            <div className="preemtive-chat-footer">
              {children}
            </div>
          </div>
        </div>
      </div>
    );
  }
}
