import PropTypes from 'prop-types';
import React from 'react';
import Isvg from 'react-inlinesvg';
import classNames from 'classnames';
import {
  Avatar,
  DepartmentAvatar,
  PersonAvatar,
  AgentTeamAvatar
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import { chooseColor, darkerColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';

class AvatarHelper {

  static brandLogoUrl = '';

  static setBrandLogoUrl(logoUrl) {
    AvatarHelper.brandLogoUrl = logoUrl;
  }

  static renderDepartmentAvatar(department, title = null) {
    return <DepartmentAvatar department={department} size={24} className="ui avatar image im" title={title} />;
  }

  static renderAgentTeamAvatar(team, title = null) {
    return <AgentTeamAvatar agentTeam={team} size={24} className="ui avatar image im" title={title} />;
  }

  static renderAgentAvatar(agent, size = 24, className = [], title = null) {
    return (<PersonAvatar
      title={title}
      key={`agent_${agent.get('id')}`}
      color={chooseColor(agent.get('id'))}
      borderColor={darkerColor(agent.get('id'), 0.2)}
      person={agent} size={size}
      className={classNames('ui avatar image im', className)}
    />);
  }

  static renderEveryoneAvatar(notificationsCount = false) {
    let title = '';
    if (notificationsCount !== false) {
      title = `Everyone chat. ${notificationsCount} unread message${notificationsCount === 1 ? '' : 's'}.`;
    }

    if (AvatarHelper.brandLogoUrl) {
      const props = {
        size:      24,
        url:       AvatarHelper.brandLogoUrl,
        gravatar:  null,
        className: 'ui avatar image im',
        title,
        tooltipId: 'userphoto'
      };
      return <Avatar {...props} />;
    }

    return <EveryoneIM title={title} />;
  }

  static renderGroupAvatar(chat, notificationsCount = false, title = null) {
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
      props.tooltipId = 'userphoto';
    } else if (title) {
      props.title = title;
      props.tooltipId = 'userphoto';
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

function EveryoneIM(props) {
  return (
    <span className="ui avatar image im everyone" title={props.title}>
      <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/im/everyone-im.svg`} />
    </span>
  );
}

EveryoneIM.propTypes = {
  title: PropTypes.string
};

export default AvatarHelper;
