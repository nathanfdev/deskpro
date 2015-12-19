import React, { PropTypes } from 'react';
import { AgentAvatars } from '../AgentAvatars';
import Immutable from 'immutable';

export class AgentMessagePopup extends React.Component {

  static propTypes = {
    primaryAgent: PropTypes.object,
    onClick: PropTypes.func,
    children: PropTypes.node,
    helpPopupMessage: PropTypes.string
  };

  render() {
    const { primaryAgent = Immutable.fromJS({}), children, onClick, helpPopupMessage } = this.props;

    return (
      <div className="dpdesignportal-state-buttons dpdesignportal-agent-message">
        <div className="preemtive-chat">
          <div className="preemtive-chat-content" onClick={onClick}>
            <div className="dpdesignportal-chat-header">
              <AgentAvatars primaryAgent={primaryAgent} />

              <h1><span>{primaryAgent.get('name')}</span></h1>
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
