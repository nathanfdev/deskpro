import React from 'react';
import { DepartmentAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/DepartmentAvatar';
import { AgentTeamAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/AgentTeamAvatar';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';

export class AvatarHelper
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
}
