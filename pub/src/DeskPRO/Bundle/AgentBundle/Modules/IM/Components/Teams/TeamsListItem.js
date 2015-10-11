import React from 'react';
import * as actions from '../../Actions/chatsActions';
import { connect } from 'react-redux';

@connect()
export class TeamsListItem extends React.Component {
  render() {
    return (
      <li>
        <a href="#"
           onClick={this.startChat.bind(null, this.props.team.get('id'), 'team', this.props.handleClickParticipant)}
          >
          <span className="chat-avatar" style={{"backgroundImage": "url(http://lorempixel.com/20/20/animals)"}}></span>
          <span className="agent">{this.props.team.get('name')}</span>
        </a>
      </li>
    );
  }

  startChat = (id, type, callback) => {
    this.props.dispatch(actions.startChat(id, type));
    callback();
  }
}

