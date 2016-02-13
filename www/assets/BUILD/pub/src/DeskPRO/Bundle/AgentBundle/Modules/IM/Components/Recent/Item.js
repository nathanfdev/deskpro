import React, { PropTypes } from 'react';
import { Avatar, DepartmentAvatar, PersonAvatar, AgentTeamAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
export class Item extends React.Component {

  static propTypes = {
    agents: PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    me: PropTypes.object.isRequired,
    chat: PropTypes.object.isRequired,
    counts: PropTypes.object.isRequired,
    loadingCounts: PropTypes.bool.isRequired,
    dispatch: PropTypes.func.isRequired,
    startChat: PropTypes.func.isRequired,
    current: PropTypes.object.isRequired,
    chating: PropTypes.bool.isRequired
  };

  getEntity = () => {
    let entity;
    const { agents, teams, departments, me, chat } = this.props;
    switch (chat.get('chat_type')) {
      case 'agent':
        const notMe = chat.get('agents').filter((agent) => agent !== me.get('id'));
        const agent = agents.get(notMe.get(0));
        entity = {
          avatar: this.renderPersonAvatar(agent),
          name: agent.get('name'),
          id: agent.get('id'),
          chatId: chat.get('id'),
          type: chat.get('chat_type'),
          elementId: 'chat-with-' + this.props.chat.get('chat_type') + '-' + agent.get('id'),
          online: agent.get('online')
        };
        break;
      case 'team':
        const team = teams.get(chat.getIn(['agent_teams', 0]));
        entity = {
          avatar: this.renderTeamAvatar(team),
          name: team.get('name'),
          id: team.get('id'),
          chatId: chat.get('id'),
          type: chat.get('chat_type'),
          elementId: 'chat-with-' + this.props.chat.get('chat_type') + '-' + team.get('id'),
          online: false
        };
        break;
      case 'department':
        const department = departments.get(chat.getIn(['departments', 0]));
        entity = {
          avatar: this.renderDepartmentAvatar(department),
          name: department.get('title'),
          id: department.get('id'),
          chatId: chat.get('id'),
          type: chat.get('chat_type'),
          elementId: 'chat-with-' + this.props.chat.get('chat_type') + '-' + department.get('id'),
          online: false
        };
        break;
      case 'everyone':
        entity = {
          avatar: this.renderEveryoneAvatar(),
          name: 'Everyone',
          id: chat.get('id'),
          chatId: chat.get('id'),
          type: chat.get('chat_type'),
          elementId: 'chat-with-' + this.props.chat.get('chat_type'),
          online: false
        };
        break;
      default:
        break;
    }
    entity.count = this.renderCount(chat);
    return entity;
  };

  renderPersonAvatar(person) {
    return <PersonAvatar person={person} size={22}/>;
  }

  renderTeamAvatar(team) {
    return <AgentTeamAvatar agentTeam={team} size={22}/>;
  }

  renderDepartmentAvatar(department) {
    return <DepartmentAvatar department={department} size={22}/>;
  }

  renderEveryoneAvatar() {
    const props = {
      size: 22,
      color: '#DD00AA',
      urlPattern: null,
      gravatar: null,
      text: 'E'
    };

    return <Avatar {...props} />;
  }

  renderCount(chat) {
    const current = this.props.counts[chat.get('id')];
    // we gonna render count balloon counts are loaded, we have information about unread messages in current rendering
    // chat (chat entity), and current (entity current) chat is not opened (chating bool)
    if (
      !this.props.loadingCounts
      && current
      && current.cnt > 0
      && !(this.props.current.id === chat.get('id') && this.props.chating)
    ) {
      return (
        <span className="chat-bubble">
        {current.cnt}
      </span>
      );
    }
  }


  render() {
    const entity = this.getEntity();
    let className = 'chat-avatar';
    if (entity.type === 'agent' && entity.online) {
      className += ' chat-user-online';
    }

    return (
      <a
        id={entity.elementId}
        href="#"
        title={entity.name}
        onClick={this.props.startChat.bind(null, entity.id, entity.type, entity.chatId)}
        className={className}>
        {entity.avatar}
        {entity.count}
      </a>
    );
  }
}
