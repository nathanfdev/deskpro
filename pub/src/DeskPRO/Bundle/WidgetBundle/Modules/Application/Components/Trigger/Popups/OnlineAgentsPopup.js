import React, { PropTypes } from 'react';
import { AgentAvatars } from './AgentAvatars';

export class OnlineAgentsPopup extends React.Component {

  static propTypes = {
    onlineAgents: PropTypes.object,
    onClick: PropTypes.func,
    onClose: PropTypes.func
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
    const { onlineAgents } = this.props;

    return (
      <div className="dpdesignportal-state-buttons dpdesignportal-online-agents">
        <div className="preemtive-chat small">
          <span className="close-panel" onClick={this.onClose}>
            <i className="fa fa-close" />
          </span>
            <div>
              <div className="preemtive-chat-content" onClick={this.onClick}>
                <h1>
                  <span>Agents Online</span>
                </h1>

                <div className="dpdesignportal-chat-header">
                  <AgentAvatars onlineAgents={onlineAgents} multiple/>
                </div>
              </div>
              <hr/>
            </div>
          <div className="preemtive-chat-content">
            <div className="preemtive-chat-footer">
              <div className="preemtive-chat-footer-button">
                <a href="#" onClick={this.onClick} className="wide">
                  Start a conversation
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
