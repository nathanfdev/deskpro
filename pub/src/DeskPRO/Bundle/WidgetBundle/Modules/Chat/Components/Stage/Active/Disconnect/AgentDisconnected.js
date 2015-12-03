import React, { PropTypes } from 'react';

export class AgentDisconnected extends React.Component {

  static propTypes = {
    agentName: PropTypes.string,
    agentAvatar: PropTypes.string
  };

  render() {
    const { agentAvatar, agentName } = this.props;
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
          <h1>{agentName} seems to have been disconnected</h1>
          <h2>If they don't return soon, we'll find another agent for you.</h2>
          <p><a href="#">Find another agent now</a></p>
        </div>
      </div>
    );
  }
}
