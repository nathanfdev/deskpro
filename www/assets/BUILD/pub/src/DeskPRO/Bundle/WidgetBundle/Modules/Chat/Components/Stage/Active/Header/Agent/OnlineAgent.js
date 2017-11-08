import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { AvatarResolver } from 'DeskPRO/Component/Avatar';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { AgentAvatar } from '../../../../../../Application/Components/Trigger/Popups/AgentAvatar';

export class OnlineAgent extends React.Component {

  static propTypes = {
    agentName:          PropTypes.string,
    agentAvatar:        PropTypes.object,
    allChatDepartments: PropTypes.object,
    departmentId:       PropTypes.number
  };

  render() {
    const { agentAvatar, agentName, allChatDepartments, departmentId } = this.props;
    const department = allChatDepartments.get(departmentId) || Immutable.fromJS({});

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
        <hr />
        <h1 dangerouslySetInnerHTML={portalPhrases.getHtml('portal.chat.online_agent', { '{agentName}': agentName })} />
        <h2>{department.get('user_title')}</h2>
      </div>
    );
  }
}
