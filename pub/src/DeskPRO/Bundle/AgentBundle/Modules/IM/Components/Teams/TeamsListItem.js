import React, { PropTypes } from 'react';
import * as actions from '../../Actions/chatsActions';

export class TeamsListItem extends React.Component {
  static propTypes = {
    team: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    handleClickParticipant: PropTypes.func.isRequired
  };

  startChat = (id, type, callback) => {
    this.props.dispatch(actions.startChat(id, type));
    callback();
  };

  render() {
    return (
      <li>
        <a href="#"
           onClick={this.startChat.bind(null, this.props.team.get('id'), 'team', this.props.handleClickParticipant)}
          >
          <span className="chat-avatar" style={{'backgroundImage': 'url(http://lorempixel.com/20/20/animals)'}}></span>
          <span className="agent">{this.props.team.get('name')}</span>
        </a>
      </li>
    );
  }
}

