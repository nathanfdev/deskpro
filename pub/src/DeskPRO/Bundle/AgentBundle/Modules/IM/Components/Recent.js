import React from 'react';
import * as actions from '../Actions/chatsActions';
import { connect } from 'react-redux';

@connect()
export class Recent extends React.Component {
  render() {
    const entity = this.getEntity();
    const style = {
      backgroundImage: 'url("' + entity.picture_url + '")'
    };
    return (
      <a
        href="#"
        title={entity.name}
        onClick={this.startChat.bind(null, entity.id, entity.type, this.props.handleClickParticipant)}
        className="chat-avatar"
        style={style}>
      </a>
    );
  }

  getEntity = () =>
  {
    let entity;

    const { agents, teams, departments, me, chat } = this.props;

    switch (chat.get('chat_type')) {
      case 'agent':
        const notMe = chat.get('agents').filter((agent) => agent != me.get('id'));
        const agent = agents.get(notMe.get(0));
        entity = {
          picture_url: agent.get('gravatar_url'),
          name: agent.get('name'),
          id: agent.get('id'),
          type: chat.get('chat_type')
        };
        break;
      case 'team':
        const team = teams.get(chat.getIn(['agent_teams', 0]));
        entity = {
          picture_url: 'http://lorempixel.com/20/20/animals',
          name: team.get('name'),
          id: team.get('id'),
          type: chat.get('chat_type')
        };
        break;
      case 'department':
        const department = departments.get(chat.getIn(['departments', 0]));
        entity = {
          picture_url: 'http://lorempixel.com/20/20/people',
          name: department.get('title'),
          id: department.get('id'),
          type: chat.get('chat_type')
        };
        break;
    }
    return entity;
  };

  startChat = (id, type, callback) => {
    this.props.dispatch(actions.startChat(id, type));
    callback();
  };
}
