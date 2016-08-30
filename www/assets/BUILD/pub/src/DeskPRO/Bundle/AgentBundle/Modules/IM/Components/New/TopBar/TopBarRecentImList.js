import React, { PropTypes } from 'react';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { DepartmentAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/DepartmentAvatar';
import classNames from 'classnames';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import RecentList from '../RecentList';

class TopBarRecentImList extends RecentList {

  static propTypes = {
    chats:    PropTypes.object.isRequired,
    children: PropTypes.oneOfType([PropTypes.object, PropTypes.array])
  };

  getItems() {
    return this.props.chats.map(agent => this.getItem(agent));
  }

  renderAgent(chat) {
    let agentId;
    for (const id of chat.get('agents')) {
      if (id !== this.props.me.get('id')) {
        agentId = id;
        break;
      }
    }
    const agent = this.props.agents.get(agentId);
    const classes = ['im', 'agent', 'recent'];
    if (!agent.get('online')) {
      classes.push('offline');
    }
    return (<span className="im wrapper" id={`chat-${chat.get('id')}`}>
      <PersonAvatar
        color={chooseColor(agent.get('id'))}
        person={agent} size={24}
        classes={['ui avatar image im']}
      />
    </span>);
  }

  renderDepartment(chat) {
    const department = this.props.departments.get(chat.get('departments')[0]);

    return (
      <span className="im wrapper" id={`chat-${chat.get('id')}`}>
        <DepartmentAvatar department={department} size={24} classes={['ui avatar image im']} />
      </span>
    );
  }

  render() {
    return (
      <div className={classNames(['im', { empty: this.props.chats.size < 1 }])}>
        {this.getItems()}
        {this.props.children}
      </div>);
  }
}

export default TopBarRecentImList;
