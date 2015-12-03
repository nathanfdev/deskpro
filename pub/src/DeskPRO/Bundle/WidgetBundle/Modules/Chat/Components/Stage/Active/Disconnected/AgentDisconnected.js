import React from 'react';

export class AgentDisconnected extends React.Component {

  render() {
    return (
      <div className="dpdesignportal-chat-header">
        <div className="dpdesignportal-chat-header-avatar-container">
          <ul>
            <li>
              <div className="dpdesignportal-chat-header-avatar">
                <i className="fa fa-user"></i>
                <span className="dpdesignportal-chat-header-avatar-disconnected"><i className="fa fa-plug"></i></span>
              </div>
            </li>
          </ul>
        </div>
        <hr />
        <div className="dpdesignportal-agent-state-info">
          <h1>Noelle Gray seems to have been disconnected</h1>
          <h2>If they don't return soon, we'll find another agent for you.</h2>
          <p><a href="#">Find another agent now</a></p>
        </div>
      </div>
    );
  }
}
