import React, { PropTypes } from 'react';
import classNames from 'classnames';

import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import AvatarHelper from './AvatarHelper';
import AbstractList from './AbstractList';

class RecentList extends React.Component {

  static propTypes = {
    me:            PropTypes.object.isRequired,
    agents:        PropTypes.object.isRequired,
    departments:   PropTypes.object.isRequired,
    teams:         PropTypes.object.isRequired,
    notifications: PropTypes.object.isRequired,
    chats:         PropTypes.object.isRequired,
    onRecentClick: PropTypes.func.isRequired
  };

  getItem(chat) {
    switch (chat.get('chat_type')) {
      case 'agent':
        return this.renderAgent(chat);
      case 'department':
        return this.renderDepartment(chat);
      case 'team':
        return this.renderTeam(chat);
      case 'everyone':
        return this.renderEveryone(chat);
      default:
        return null;
    }
  }

  getItems() {
    return this.props.chats.map(this.getItem.bind(this));
  }

  getAgents(container) {
    return container.get('agents').map(
      (agentId) => {
        if (agentId === this.props.me.get('id')) {
          return null;
        }

        const classes = ['ui avatar image im'];
        const agent = this.props.agents.get(agentId);
        if (!agent.get('online')) {
          classes.push('offline');
        }

        return (<PersonAvatar
          person={agent}
          size={24}
          classes={classes}
          color={chooseColor(agent)}
        />);
      }
    );
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
    const notificationCount = this.props.notifications.get(`${agent.get('id')}`);
    if (!agent.get('online')) {
      classes.push('offline');
    }
    return (
      <ListElement
        key={agent.get('id')}
        classes={classes}
        imageNode={AvatarHelper.renderAgentAvatar(agent)}
      >
        <div className="content agent recent" onClick={() => this.props.onRecentClick(chat.get('id'))}>
          <div className="header">{agent.get('name')}</div>
          <div
            className={classNames(
                ['ui', 'knuckles', 'label', 'message-counter'],
                { grey: !notificationCount || notificationCount < 1 })
            }
          >
            {notificationCount || 0}
          </div>
        </div>
        <div className="timestamp content right floated">
          {AbstractList.getTimestamp(chat.get('date_last_message'))}
        </div>
      </ListElement>
    );
  }

  renderDepartment(chat) {
    const department = this.props.departments.get(chat.getIn(['departments', 0]));
    const classes = ['im', 'department', 'recent'];
    return (
      <ListElement
        onClick={() => this.props.onRecentClick(chat.get('id'))}
        key={department.get('id')}
        classes={classes}
        imageNode={AvatarHelper.renderDepartmentAvatar(department)}
      >
        <div className="content department" onClick={() => this.props.onRecentClick(chat.get('id'))}>
          <div className="header">department</div>
          <div className="description">
            {department.get('title')}
            <span className="agents-list">{this.getAgents(department)}</span>
          </div>
        </div>
        <div className="timestamp content right floated">
          {AbstractList.getTimestamp(chat.get('date_last_message'))}
        </div>
      </ListElement>
    );
  }

  renderTeam(chat) {
    const team = this.props.teams.get(chat.getIn(['agent_teams', 0]));
    const classes = ['im', 'team', 'recent'];
    return (
      <ListElement
        onClick={() => this.props.onRecentClick(chat.get('id'))}
        key={team.get('id')}
        classes={classes}
        imageNode={AvatarHelper.renderAgentTeamAvatar(team)}
      >
        <div className="content team" onClick={() => this.props.onRecentClick(chat.get('id'))}>
          <div className="header">
            {team.get('name')}
            <span className="agents-list">{this.getAgents(team)}</span>
          </div>
        </div>
        <div className="timestamp content right floated">
          {AbstractList.getTimestamp(chat.get('date_last_message'))}
        </div>
      </ListElement>
    );
  }

  renderEveryone(chat) {
    const classes = ['im', 'team', 'recent'];
    return (
      <ListElement
        onClick={() => this.props.onRecentClick(chat.get('id'))}
        key="everyone"
        classes={classes}
        imageNode={AvatarHelper.renderEveryoneAvatar()}
      >
        <div className="content team" onClick={() => this.props.onRecentClick(chat.get('id'))}>
          <div className="header">
            Everyone
          </div>
        </div>
        <div className="timestamp content right floated">
          {AbstractList.getTimestamp(chat.get('date_last_message'))}
        </div>
      </ListElement>
    );
  }

  render() {
    return (
      <List classes={['im', 'middle', 'aligned', 'selection']}>
        {this.getItems()}
      </List>
    );
  }
}

export default RecentList;
