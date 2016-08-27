import React, { PropTypes } from 'react';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import classNames from 'classnames';
import TimeAgo from 'react-timeago';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import AgentList from './AgentsList';
import DepartmentsList from './DepartmentsList';

class RecentList extends React.Component {

  static propTypes = {
    me:            PropTypes.object.isRequired,
    agents:        PropTypes.object.isRequired,
    departments:   PropTypes.object.isRequired,
    notifications: PropTypes.object.isRequired,
    chats:         PropTypes.object.isRequired
  };

  getItem(chat) {
    switch (chat.get('chat_type')) {
      case 'agent':
        return this.renderAgentRow(chat);
      case 'department':
        return this.renderDepartmentRow(chat);
      default:
        return null;
    }
  }

  getItems() {
    return this.props.chats.map(agent => this.getItem(agent));
  }

  getAgents(department) {
    return department.get('agents').map(
      (agentId) => {
        if (agentId === this.props.me.get('id')) {
          return null;
        }

        const classes = ['ui avatar image im'];
        const agent = this.props.agents.get(`${agentId}`);
        if (!agent.get('online')) {
          classes.push('offline');
        }

        return (<PersonAvatar
          person={agent}
          size={12}
          classes={classes}
          color={chooseColor(agent)}
        />);
      }
    );
  }

  renderAgentRow(chat) {
    let agentId;
    for (const id of chat.get('agents')) {
      if (id !== this.props.me.get('id')) {
        agentId = id;
        break;
      }
    }
    const agent = this.props.agents.get(agentId);
    const classes = ['im', 'agent', 'recent'];
    const notificationCount = this.props.notifications.get(`${agent.get('id')}`);
    if (!agent.get('online')) {
      classes.push('offline');
    }
    return (
      <ListElement key={agent.get('id')} classes={classes} imageNode={AgentList.getAvatar(agent)}>
        <div className="content agent recent">
          <div className="header">{agent.get('name')}</div>
          <div
            className={classNames(
                ['ui', 'knuckles', 'label', 'message-counter'],
                { grey: notificationCount < 1 })
            }
          >
            {notificationCount}
          </div>
        </div>
        <div className="timestamp last-seen content right floated">
          {chat.get('date_last_message') ? <TimeAgo date={chat.get('date_last_message')} /> : 'never'}
        </div>

      </ListElement>
    );
  }

  renderDepartmentRow(chat) {
    const department = this.props.departments.get(chat.get('departments')[0]);
    const classes = ['im', 'department', 'recent'];
    return (<ListElement key={department.get('id')} classes={classes} imageNode={DepartmentsList.getAvatar(department)}>
      <div className="content department">
        <div className="header">DEPARTMENT</div>
        <div className="description">
          {department.get('title')}
          <span className="agents-list">{this.getAgents(department)}</span>
        </div>
      </div>
      <div className="timestamp content right floated">
        {chat.get('date_last_message') ? <TimeAgo date={chat.get('date_last_message')} /> : 'never'}
      </div>
    </ListElement>);
  }

  render() {
    return (
      <List classes={['im', 'middle', 'aligned', 'selection']}>
        {this.getItems()}
      </List>);
  }
}

export default RecentList;
