import React, { PropTypes } from 'react';
import { AvatarResolver } from 'DeskPRO/Component/Avatar';
import { AgentAvatar } from '../../../../../../Application/Components/Trigger/Popups/AgentAvatar';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class OnlineAgent extends React.Component {

  static propTypes = {
    agentName: PropTypes.string,
    agentAvatar: PropTypes.object,
    departmentName: PropTypes.string
  };

  render() {
    const { agentAvatar, agentName, departmentName } = this.props;

    return (
      <div className="dpdesignportal-chat-header">
        <div className="dpdesignportal-chat-header-avatar-container">
          <ul>
            <li>
              {agentAvatar &&
                <AvatarResolver avatar={agentAvatar} size={150}>
                  <AgentAvatar />
                </AvatarResolver>
              }
            </li>
          </ul>
        </div>
        <hr/>
        <h1>You are chatting with <span className="name">{agentName}</span></h1>
        <h2>{departmentName}</h2>
      </div>
    );
  }
}
