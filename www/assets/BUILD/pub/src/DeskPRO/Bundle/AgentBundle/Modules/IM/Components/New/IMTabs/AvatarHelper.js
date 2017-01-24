import React from 'react';
import {
  Avatar,
  DepartmentAvatar,
  PersonAvatar,
  AgentTeamAvatar
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import { chooseColor, darkerColor, colorLuminance } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';

class AvatarHelper
{
  static renderDepartmentAvatar(department) {
    return <DepartmentAvatar department={department} size={24} className="ui avatar image im" />;
  }

  static renderAgentTeamAvatar(team) {
    return <AgentTeamAvatar agentTeam={team} size={24} className="ui avatar image im" />;
  }

  static renderAgentAvatar(agent, size = 24) {
    return (<PersonAvatar
      color={chooseColor(agent.get('id'))}
      person={agent} size={size}
      className="ui avatar image im"
    />);
  }

  static renderEveryoneAvatar(notificationsCount = false) {
    const props = {
      size:        24,
      color:       '#DD00AA',
      borderColor: colorLuminance('#DD00AA', -0.2),
      urlPattern:  null,
      gravatar:    null,
      text:        'EO',
      className:   'ui avatar image im'
    };
    if (notificationsCount !== false) {
      props.title = `Everyone chat. ${notificationsCount} unread message${notificationsCount === 1 ? '' : 's'}.`;
    }

    return <Avatar {...props} />;
  }

  static renderGroupAvatar(chat, notificationsCount = false) {
    const name = chat.get('name');
    const text = (name && name.length ? name.substr(0, 2) : '');
    const props = {
      size:        24,
      color:       chooseColor(chat.get('id')),
      borderColor: darkerColor(chat.get('id')),
      urlPattern:  null,
      gravatar:    null,
      text:        text || '?',
      className:   'ui avatar image im'
    };

    if (notificationsCount !== false) {
      const participantsCount = chat.get('agents').size;
      props.title = `${chat.get('name')} (${participantsCount} participant${participantsCount === 1 ? '' : 's'}). ${notificationsCount} unread message${notificationsCount === 1 ? '' : 's'}.`;
    }

    return <Avatar {...props} />;
  }

}

export default AvatarHelper;
