import React from 'react';
import * as actions from '../../Actions/chatsActions';
import { connect } from 'react-redux';

@connect()
export class DepartmentsListItem extends React.Component {

  render() {
    return (
      <li>
        <a href="#"
           onClick={this.startChat.bind(null, this.props.department.get('id'), 'department', this.props.handleClickParticipant)}
          >
          <span className="chat-avatar" style={{"backgroundImage": "url(http://lorempixel.com/20/20/people)"}}></span>
          <span className="agent">{this.props.department.get('title')}</span>
        </a>
      </li>
    );
  }

  startChat = (id, type, callback) => {
    this.props.dispatch(actions.startChat(id, type));
    callback();
  }
}

