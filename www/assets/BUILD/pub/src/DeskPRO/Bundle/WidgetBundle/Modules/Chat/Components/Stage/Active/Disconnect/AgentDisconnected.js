import PropTypes from 'prop-types';
import React from 'react';
import { AvatarResolver } from 'DeskPRO/Component/Avatar';
import { AgentAvatar } from '../../../../../Application/Components/Trigger/Popups/AgentAvatar';

export class AgentDisconnected extends React.Component {

  static propTypes = {
    children:    PropTypes.any,
    agentAvatar: PropTypes.object
  };

  render() {
    const { agentAvatar, children } = this.props;

    return (
      <div className="dpdesignportal-chat-header">
        <div className="dpdesignportal-chat-header-avatar-container">
          <ul>
            <li>
              {agentAvatar &&
                <AvatarResolver avatar={agentAvatar} size={150}>
                  <AgentAvatar disconnected />
                </AvatarResolver>
              }
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
