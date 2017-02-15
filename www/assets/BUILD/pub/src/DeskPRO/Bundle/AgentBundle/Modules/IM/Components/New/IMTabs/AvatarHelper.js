import React from 'react';
import classNames from 'classnames';
import {
  Avatar,
  DepartmentAvatar,
  PersonAvatar,
  AgentTeamAvatar
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import { chooseColor, darkerColor, colorLuminance } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';

class AvatarHelper {
  static renderDepartmentAvatar(department) {
    return <DepartmentAvatar department={department} size={24} className="ui avatar image im" />;
  }

  static renderAgentTeamAvatar(team) {
    return <AgentTeamAvatar agentTeam={team} size={24} className="ui avatar image im" />;
  }

  static renderAgentAvatar(agent, size = 24, className = []) {
    return (<PersonAvatar
      color={chooseColor(agent.get('id'))}
      borderColor={darkerColor(agent.get('id'), 0.2)}
      person={agent} size={size}
      className={classNames('ui avatar image im', className)}
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

  static renderAvatar(text, id, size = 24, className = []) {
    const split = text.split(' ');
    let avatarText;
    if (split.length > 1) {
      avatarText = split[0].toUpperCase() + split[1].toUpperCase();
    } else {
      avatarText = text.substr(0, 2);
    }
    const props = {
      size,
      color:       chooseColor(id),
      borderColor: darkerColor(id),
      urlPattern:  null,
      gravatar:    null,
      text:        avatarText || '?',
      className:   classNames('ui avatar image im', className)
    };

    return <Avatar {...props} />;
  }
}

export default AvatarHelper;
