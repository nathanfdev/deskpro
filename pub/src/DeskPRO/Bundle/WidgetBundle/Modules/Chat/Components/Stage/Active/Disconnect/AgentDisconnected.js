import React, { PropTypes } from 'react';

export class AgentDisconnected extends React.Component {

  static propTypes = {
    children: PropTypes.any,
    agentAvatar: PropTypes.string
  };

  render() {
    const { agentAvatar, children } = this.props;
    const style = {};

    if (agentAvatar) {
      style.backgroundImage = `url(${agentAvatar})`;
    }

    return (
      <div className="dpdesignportal-chat-header">
        <div className="dpdesignportal-chat-header-avatar-container">
          <ul>
            <li>
              <div className="dpdesignportal-chat-header-avatar" style={style}>
                <i className="fa fa-user"></i>
                <span className="dpdesignportal-chat-header-avatar-disconnected">
                  <i className="fa fa-plug"></i>
                </span>
              </div>
            </li>
          </ul>
        </div>
        <hr />
        <div className="dpdesignportal-agent-state-info">
          {children}
        </div>
      </div>
    );
  }
}
