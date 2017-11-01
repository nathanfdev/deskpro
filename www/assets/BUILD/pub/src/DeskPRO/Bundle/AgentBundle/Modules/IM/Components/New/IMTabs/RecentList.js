import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import AvatarHelper from './AvatarHelper';
import AbstractList from './AbstractList';
import { getDepartmentAgents } from '../../../../Application/Actions/departmentActions';

class RecentList extends React.Component {

  static propTypes = {
    me:               PropTypes.object.isRequired,
    agents:           PropTypes.object.isRequired,
    departments:      PropTypes.object.isRequired,
    teams:            PropTypes.object.isRequired,
    counts:           PropTypes.object.isRequired,
    chats:            PropTypes.object.isRequired,
    onRecentClick:    PropTypes.func.isRequired,
    startedByMeChats: PropTypes.object.isRequired
  };

  static sortList(list) {
    return list.toOrderedMap().sort((first, second) => {
      const fDate = Date.parse(first.get('date_last_message') ? first.get('date_last_message') : first.get('date_created'));
      const sDate = Date.parse(second.get('date_last_message') ? second.get('date_last_message') : second.get('date_created'));
      return sDate - fDate;
    });
  }

  getItem(chat) {
    switch (chat.get('chat_type')) {
      case 'agent':
        return this.renderAgent(chat);
      case 'department':
        return this.renderDepartment(chat);
      case 'team':
        return this.renderTeam(chat);
      case 'group':
        return this.renderGroup(chat);
      case 'everyone':
        return this.renderEveryone(chat);
      default:
        return null;
    }
  }

  getItems() {
    const { chats, startedByMeChats, me } = this.props;
    const filtered = chats.filter(chat => chat.get('date_last_message') || startedByMeChats.get(chat.get('id')) || me.get('id') === chat.get('admin'));

    return RecentList.sortList(filtered).map(this.getItem.bind(this));
  }

  getNotificationCount(chat) {
    const nestedCounts = this.props.counts.nested ? this.props.counts.nested[chat.get('id')] : false;
    return nestedCounts && nestedCounts.count ? nestedCounts.count : 0;
  }

  renderAgents(agents) {
    return agents.map(
      (agentId) => {
        const agent = this.props.agents.get(agentId);
        if (agentId === this.props.me.get('id') || !agent) {
          return null;
        }

        const className = ['ui avatar image im'];

        if (!agent.get('online')) {
          className.push('offline');
        }

        return AvatarHelper.renderAgentAvatar(agent, 20, classNames(className), agent.get('name'));
      }
    );
  }

  renderNotificationsBalloon(chat) {
    const notificationCount = this.getNotificationCount(chat);
    if (notificationCount < 1) {
      return null;
    }
    return (
      <div className="ui knuckles label message-counter">
        {notificationCount}
      </div>
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
    if (!agent) {
      return null;
    }
    const className = ['im', 'agent', 'recent', agent.get('online') ? 'online' : 'offline'];
    return (
      <ListElement
        key={`agent_${agentId}`}
        className={classNames(className)}
        imageNode={AvatarHelper.renderAgentAvatar(agent, 24, [], agent.get('name'))}
      >
        <div className="content agent recent" onClick={() => this.props.onRecentClick(chat.get('id'))}>
          <div className="header">{agent.get('name')}</div>
          {this.renderNotificationsBalloon(chat)}
        </div>
        <div className="timestamp content right floated">
          <span>{AbstractList.getTimestamp(chat.get('date_last_message'))}</span>
        </div>
      </ListElement>
    );
  }

  renderDepartment(chat) {
    const department = this.props.departments.get(chat.getIn(['departments', 0]));
    return (
      <ListElement
        onClick={() => this.props.onRecentClick(chat.get('id'))}
        key={`department_${department.get('id')}`}
        className="im department recent"
        imageNode={AvatarHelper.renderDepartmentAvatar(department, `${department.get('title')} department`)}
      >
        <div className="content department" onClick={() => this.props.onRecentClick(chat.get('id'))}>
          <div className="header">department</div>
          {this.renderNotificationsBalloon(chat)}
          <div className="description">
            {department.get('title')}
            <span className="agents-list">
              {this.renderAgents(getDepartmentAgents(department))}
            </span>
          </div>
        </div>
        <div className="timestamp content right floated">
          <span>{AbstractList.getTimestamp(chat.get('date_last_message'))}</span>
        </div>
      </ListElement>
    );
  }

  renderTeam(chat) {
    const team = this.props.teams.get(chat.getIn(['agent_teams', 0]));
    return (
      <ListElement
        onClick={() => this.props.onRecentClick(chat.get('id'))}
        key={`team_${team.get('id')}`}
        className="im team recent"
        imageNode={AvatarHelper.renderAgentTeamAvatar(team, `${team.get('name')} team`)}
      >
        <div className="content team" onClick={() => this.props.onRecentClick(chat.get('id'))}>
          <div className="header">
            {team.get('name')}
            <span className="agents-list">{this.renderAgents(team.get('agents'))}</span>
          </div>
          {this.renderNotificationsBalloon(chat)}
        </div>
        <div className="timestamp content right floated">
          <span>{AbstractList.getTimestamp(chat.get('date_last_message'))}</span>
        </div>
      </ListElement>
    );
  }

  renderGroup(chat) {
    return (
      <ListElement
        onClick={() => this.props.onRecentClick(chat.get('id'))}
        key={`group_${chat.get('id')}`}
        className="im team recent"
        imageNode={AvatarHelper.renderGroupAvatar(chat, null, `${chat.get('name')} group`)}
      >
        <div className="content team" onClick={() => this.props.onRecentClick(chat.get('id'))}>
          <div className="header">
            {chat.get('name')}
            <span className="agents-list">{this.renderAgents(chat.get('agents'))}</span>
          </div>
          {this.renderNotificationsBalloon(chat)}
        </div>
        <div className="timestamp content right floated">
          <span>{AbstractList.getTimestamp(chat.get('date_last_message'))}</span>
        </div>
      </ListElement>
    );
  }

  renderEveryone(chat) {
    return (
      <ListElement
        onClick={() => this.props.onRecentClick(chat.get('id'))}
        key="everyone"
        className="im team recent"
        imageNode={AvatarHelper.renderEveryoneAvatar()}
      >
        <div className="content team" onClick={() => this.props.onRecentClick(chat.get('id'))}>
          <div className="header">
            Everyone
          </div>
          {this.renderNotificationsBalloon(chat)}
        </div>
        <div className="timestamp content right floated">
          <span>{AbstractList.getTimestamp(chat.get('date_last_message'))}</span>
        </div>
      </ListElement>
    );
  }

  render() {
    return (
      <List className="im middle aligned selection">
        {this.getItems()}
      </List>
    );
  }
}

export default RecentList;
