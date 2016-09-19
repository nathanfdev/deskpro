import React from 'react';
import {
  Avatar,
  DepartmentAvatar,
  PersonAvatar,
  AgentTeamAvatar
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';

class AvatarHelper
{
  static renderDepartmentAvatar(department) {
    return <DepartmentAvatar department={department} size={24} classes={['ui avatar image im']} />;
  }

  static renderAgentTeamAvatar(team) {
    return <AgentTeamAvatar agentTeam={team} size={24} classes={['ui avatar image im']} />;
  }

  static renderAgentAvatar(agent) {
    return (<PersonAvatar
      color={chooseColor(agent.get('id'))}
      person={agent} size={24}
      classes={['ui avatar image im']}
    />);
  }

  static renderEveryoneAvatar() {
    const props = {
      size:       24,
      color:      '#DD00AA',
      urlPattern: null,
      gravatar:   null,
      text:       'E',
      classes:    ['ui avatar image im']
    };

    return <Avatar {...props} />;
  }

}

export default AvatarHelper;
