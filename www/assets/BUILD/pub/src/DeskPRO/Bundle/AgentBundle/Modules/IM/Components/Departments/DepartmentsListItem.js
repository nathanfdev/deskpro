import PropTypes from 'prop-types';
import React from 'react';
import * as actions from '../../Actions/chatsActions';
import { DepartmentAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';

export class DepartmentsListItem extends React.Component {
  static propTypes = {
    department: PropTypes.object.isRequired,
    dispatch:   PropTypes.func.isRequired
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
          <DepartmentAvatar department={this.props.department} size={22} />
          <span className="agent">{this.props.department.get('title')}</span>
        </a>
      </li>
    );
  }
}

