import React, { PropTypes } from 'react';
import SampleAvatar from '../../../../../../Resources/img/sample-avatar.jpg';

export class AgentMessagePopup extends React.Component {

  static propTypes = {
    onClick: PropTypes.func,
    children: PropTypes.node,
    helpPopupMessage: PropTypes.string
  };

  render() {
    const { children, onClick, helpPopupMessage } = this.props;

    return (
      <div className="dpdesignportal-state-buttons dpdesignportal-agent-message">
        <div className="preemtive-chat">
          <div className="preemtive-chat-content" onClick={onClick}>
            <div className="dpdesignportal-chat-header">
              <div className="avatar-container">
                <ul>
                  <li>
                    <div className="dpdesignportal-chat-header-avatar" style={{backgroundImage: `url(${SampleAvatar})`}}></div>
                  </li>
                </ul>
                <hr/>
              </div>
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
