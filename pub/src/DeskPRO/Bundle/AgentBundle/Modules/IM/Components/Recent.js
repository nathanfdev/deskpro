import React from 'react';
import * as actions from '../Actions/chatsActions';
import { connect } from 'react-redux';

@connect()
export class Recent extends React.Component {
  render() {
    let entity;

    switch(this.props.chat.type)
    {
      case 'agent':
         entity = {
          picture_url: this.props.chat.gravatar_url,
          name: this.props.chat.name,
          id: this.props.chat.id,
          type: 'agent'
        };
        break;
      case 'team':
        entity = {
          picture_url: 'http://lorempixel.com/20/20/animals',
          name: this.props.chat.name,
          id: this.props.chat.id,
          type: 'team'
        };
        break;
      case 'department':
        entity = {
          picture_url: 'http://lorempixel.com/20/20/animals',
          name: this.props.chat.name,
          id: this.props.chat.id,
          type: 'team'
        };
        break;
      default:
        entity = {
          picture_url: 'http://lorempixel.com/20/20/animals',
          name: 'unknown',
          id: 0,
          type: 'group'
        };
        name = 'unknown';
    }

    const style = {
      backgroundImage: 'url("' + entity.picture_url + '")'
    };
    return (
      <a
        href="#"
        title={entity.name}
        onClick={this.startChat.bind(null, entity.id, 'agent', this.props.handleClickParticipant)}
        className="chat-avatar"
        style={style}>
      </a>
    );
  }

  startChat = (id, type, callback) => {
    "use strict";
    this.props.dispatch(actions.startChat(id, type));
    callback();
  }
}