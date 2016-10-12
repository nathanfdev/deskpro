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

  static sortList(list) {
    return list.sort((first, second) => {
      const fDate = Date.parse(first.get('date_last_message'));
      const sDate = Date.parse(second.get('date_last_message'));
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
    return RecentList.sortList(this.props.chats).map(this.getItem.bind(this));
  }

  getAgents(container) {
    return container.get('agents').map(
      (agentId) => {
        if (agentId === this.props.me.get('id')) {
          return null;
        }

        const className = ['ui avatar image im'];
        const agent = this.props.agents.get(agentId);
        if (!agent.get('online')) {
          className.push('offline');
        }

        return (<PersonAvatar
          key={`agent_${agentId}`}
          person={agent}
          size={24}
          className={classNames(className)}
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
    const className = ['im', 'agent', 'recent'];
    const notificationCount = this.props.notifications.get(`${agent.get('id')}`);
    if (!agent.get('online')) {
      className.push('offline');
    }
    return (
      <ListElement
        key={`agent_${agentId}`}
        className={classNames(className)}
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
    return (
      <ListElement
        onClick={() => this.props.onRecentClick(chat.get('id'))}
        key={`department_${department.get('id')}`}
        className="im department recent"
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
    return (
      <ListElement
        onClick={() => this.props.onRecentClick(chat.get('id'))}
        key={`team_${team.get('id')}`}
        className="im team recent"
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

  renderGroup(chat) {
    return (
      <ListElement
        onClick={() => this.props.onRecentClick(chat.get('id'))}
        key={`chat_${chat.get('id')}`}
        className="im team recent"
        imageNode={AvatarHelper.renderGroupAvatar(chat)}
      >
        <div className="content team" onClick={() => this.props.onRecentClick(chat.get('id'))}>
          <div className="header">
            {chat.get('id')}
          </div>
        </div>
        <div className="timestamp content right floated">
          {AbstractList.getTimestamp(chat.get('date_last_message'))}
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
        </div>
        <div className="timestamp content right floated">
          {AbstractList.getTimestamp(chat.get('date_last_message'))}
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
