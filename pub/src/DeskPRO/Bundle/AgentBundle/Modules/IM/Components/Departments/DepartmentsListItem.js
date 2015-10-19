import React, { PropTypes } from 'react';
import * as actions from '../../Actions/chatsActions';

export class DepartmentsListItem extends React.Component {
  static propTypes = {
    department: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  startChat = (id, type) => {
    this.props.dispatch(actions.startChat(id, type));
  };

  render() {
    return (
      <li>
        <a href="#"
           onClick={this.startChat.bind(null, this.props.department.get('id'), 'department')}
          >
          <span className="chat-avatar" style={{'backgroundImage': 'url(http://lorempixel.com/20/20/people)'}}></span>
          <span className="agent">{this.props.department.get('title')}</span>
        </a>
      </li>
    );
  }
}

