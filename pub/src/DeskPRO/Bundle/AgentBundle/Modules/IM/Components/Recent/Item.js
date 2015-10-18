import React, { PropTypes } from 'react';

import { connect } from 'react-redux';

@connect()
export class Item extends React.Component {

  static propTypes = {
    agents: PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    me: PropTypes.object.isRequired,
    chat: PropTypes.object.isRequired,
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
          picture_url: agent.get('gravatar_url'),
          name: agent.get('name'),
          id: agent.get('id'),
          type: chat.get('chat_type'),
          elementId: 'chat-with-' + this.props.chat.get('chat_type') + '-' + agent.get('id')
        };
        break;
      case 'team':
        const team = teams.get(chat.getIn(['agent_teams', 0]));
        entity = {
          picture_url: 'http://lorempixel.com/20/20/animals',
          name: team.get('name'),
          id: team.get('id'),
          type: chat.get('chat_type'),
          elementId: 'chat-with-' + this.props.chat.get('chat_type') + '-' + team.get('id')
        };
        break;
      case 'department':
        const department = departments.get(chat.getIn(['departments', 0]));
        entity = {
          picture_url: 'http://lorempixel.com/20/20/people',
          name: department.get('title'),
          id: department.get('id'),
          type: chat.get('chat_type'),
          elementId: 'chat-with-' + this.props.chat.get('chat_type') + '-' + department.get('id')
        };
        break;
      default:
        break;
    }
    return entity;
  };



  render() {
    const entity = this.getEntity();
    const style = {
      backgroundImage: 'url("' + entity.picture_url + '")'
    };
    return (
      <a
        id={entity.elementId}
        href="#"
        title={entity.name}
        onClick={this.props.startChat.bind(null, entity.id, entity.type)}
        className="chat-avatar"
        style={style}>
      </a>
    );
  }
}
