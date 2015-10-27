import React, { PropTypes } from 'react';
import { Avatar, DepartmentAvatar, PersonAvatar, AgentTeamAvatar } from '../../../Common/Components/Avatar/index';
export class Item extends React.Component {

  static propTypes = {
    agents: PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    me: PropTypes.object.isRequired,
    chat: PropTypes.object.isRequired,
    bubbles: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    startChat: PropTypes.func.isRequired
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
          bubble: this.renderBubble(chat),
          name: agent.get('name'),
          id: agent.get('id'),
          type: chat.get('chat_type'),
          elementId: 'chat-with-' + this.props.chat.get('chat_type') + '-' + agent.get('id')
        };
        break;
      case 'team':
        const team = teams.get(chat.getIn(['agent_teams', 0]));
        entity = {
          avatar: this.renderTeamAvatar(team),
          bubble: this.renderBubble(chat),
          name: team.get('name'),
          id: team.get('id'),
          type: chat.get('chat_type'),
          elementId: 'chat-with-' + this.props.chat.get('chat_type') + '-' + team.get('id')
        };
        break;
      case 'department':
        const department = departments.get(chat.getIn(['departments', 0]));
        entity = {
          avatar: this.renderDepartmentAvatar(department),
          bubble: this.renderBubble(chat),
          name: department.get('title'),
          id: department.get('id'),
          type: chat.get('chat_type'),
          elementId: 'chat-with-' + this.props.chat.get('chat_type') + '-' + department.get('id')
        };
        break;
      case 'everyone':
        entity = {
          avatar: this.renderEveryoneAvatar(),
          bubble: this.renderBubble(chat),
          name: 'Everyone',
          id: chat.get('id'),
          type: chat.get('chat_type'),
          elementId: 'chat-with-' + this.props.chat.get('chat_type')
        };
        break;
      default:
        break;
    }
    return entity;
  };

  renderPersonAvatar(person) {
    return <PersonAvatar person={person} size="22"/>;
  }

  renderTeamAvatar(team) {
    return <AgentTeamAvatar agentTeam={team} size="22"/>;
  }

  renderDepartmentAvatar(department) {
    return <DepartmentAvatar department={department} size="22"/>;
  }

  renderEveryoneAvatar() {
    const props = {
      size: 22,
      color: '#DD00AA',
      urlPattern: null,
      gravatar: null,
      fallbackText: 'E'
    };

    return <Avatar {...props} />;
  }

  renderBubble(chat) {
    const current = this.props.bubbles[chat.get('id')];
    if (current && current.cnt > 0) {
      return (
        <span className="chat-bubble">
          {current.cnt}
        </span>
      );
    }
  }


  render() {
    const entity = this.getEntity();

    return (
      <a
        id={entity.elementId}
        href="#"
        title={entity.name}
        onClick={this.props.startChat.bind(null, entity.id, entity.type)}
        className="chat-avatar">
        {entity.avatar}
        {entity.bubble}
      </a>
    );
  }
}
