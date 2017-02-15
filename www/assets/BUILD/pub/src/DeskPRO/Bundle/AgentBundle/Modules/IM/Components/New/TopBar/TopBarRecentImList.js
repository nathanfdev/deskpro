import React, { PropTypes } from 'react';
import Loader from 'react-loader';
import Immutable from 'immutable';
import {
  DepartmentAvatar,
  PersonAvatar,
  AgentTeamAvatar
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import classNames from 'classnames';
import { chooseColor, darkerColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import { RecentList } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs';
import AvatarHelper from '../IMTabs/AvatarHelper';
import HeaderHelper from '../ChatWindow/HeaderHelper';
import * as chatActions from '../../../Actions/chatsActions';

class TopBarRecentImList extends RecentList {

  static propTypes = {
    chats:             PropTypes.object.isRequired,
    children:          PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
    onRecentClick:     PropTypes.func.isRequired,
    me:                PropTypes.object.isRequired,
    agents:            PropTypes.object.isRequired,
    people:            PropTypes.object.isRequired,
    departments:       PropTypes.object.isRequired,
    teams:             PropTypes.object.isRequired,
    recentLoaded:      PropTypes.bool.isRequired,
    teamsLoaded:       PropTypes.bool.isRequired,
    departmentsLoaded: PropTypes.bool.isRequired,
    agentsLoaded:      PropTypes.bool.isRequired,
    counts:            PropTypes.object,
    onHideChat:        PropTypes.func,
    hiddenChats:       PropTypes.object
  };

  static defaultProps = {
    onHideChat(chat) {
      chatActions.hideChat(chat.get('id'), Date.now());
    },
    counts: {
      nested: {}
    },
    hiddenChats: {

    }
  };

  constructor(props) {
    super(props);
    this.state = {
      chats: Immutable.OrderedMap({})
    };
  }

  componentWillReceiveProps(props) {
    let { chats } = this.state;
    if (props.chats) {
      RecentList
        .sortList(props.chats)
        .forEach((chat) => {
          const chatId = chat.get('id');
          const path   = [chatId, 'added'];
          chats = chats.set(chatId, chat.set('added', chats.hasIn(path) ? chats.getIn(path) : Date.now()));
        });
    }
    let sorted = chats.sort((a, b) => b.get('added') - a.get('added'));
    if (props.hiddenChats) {
      sorted = sorted.filter((chat) => {
        const date = chat.get('date_last_message') || chat.get('date_created');

        return !props.hiddenChats[chat.get('id')] || props.hiddenChats[chat.get('id')] < (Date.parse(date));
      });
    }
    this.setState({ chats: sorted });
  }

  getItems() {
    return this.state.chats.slice(0, 10).map(agent => this.getItem(agent));
  }

  getHeaderHelper(chat) {
    const { agents, people, departments, teams, me } = this.props;
    const props = { agents, people, departments, teams, me, current: chat };

    if (!this.headerHelper) {
      this.headerHelper = new HeaderHelper(props);
    } else {
      this.headerHelper.setProps(props);
    }

    return this.headerHelper;
  }

  renderNotificationsBalloon(chat) {
    const notificationCount = this.getNotificationCount(chat);
    return notificationCount ? <div className="ui knuckles label message-counter">{notificationCount}</div> : null;
  }

  renderRemoveButton(chat) { // eslint-disable-line class-methods-use-this
    return (
      <i
        className="close icon remove"
        onClick={(event) => {
          event.stopPropagation();
          this.props.onHideChat(chat);
        }
    } />);
  }

  renderAgent(chat) {
    const { me, agents, people, onRecentClick } = this.props;
    let agentId;
    for (const id of chat.get('agents')) {
      if (id !== me.get('id')) {
        agentId = id;
        break;
      }
    }
    let agent = agents.get(agentId);
    let active = true;
    const person = people.get(agentId);
    if (!agent && !person) {
      return null;
    }

    if (!agent) {
      agent = person;
      active = false;
    }

    const className = ['ui avatar image im'];

    if (!active || !agent.get('online')) {
      className.push('offline');
    }

    const header = this.getHeaderHelper(chat).getHeaderText(true);
    const notificationsCount = this.getNotificationCount(chat);

    return (
      <span
        className="im wrapper"
        id={`chat-${chat.get('id')}`}
        onClick={() => onRecentClick(chat.get('id'))}
      >
        <PersonAvatar
          title={`${header}. ${notificationsCount} unread message${notificationsCount === 1 ? '' : 's'}.`}
          color={chooseColor(agent.get('id'))}
          borderColor={darkerColor(agent.get('id'))}
          person={agent} size={24}
          className={classNames(className)}
        />
        {(active && agent.get('online')) ? <span className="agent-online" /> : null}
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </span>
    );
  }

  renderDepartment(chat) {
    const department = this.props.departments.getIn(chat.get('departments', 0));
    const header = this.getHeaderHelper(chat).getHeaderText(true);
    const notificationsCount = this.getNotificationCount(chat);
    const participantsCount = department.get('agents').size;
    const title = `${header} (${participantsCount} participant${participantsCount === 1 ? '' : 's'}). ${notificationsCount} unread message${notificationsCount === 1 ? '' : 's'}.`;

    return (
      <span
        className="im wrapper"
        id={`chat-${chat.get('id')}`}
        onClick={() => this.props.onRecentClick(chat.get('id'))}
      >
        <DepartmentAvatar
          department={department}
          size={24}
          className="ui avatar image im"
          title={title}
        />
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </span>
    );
  }

  renderTeam(chat) {
    const team = this.props.teams.get(chat.getIn(['agent_teams', 0]));

    const header = this.getHeaderHelper(chat).getHeaderText(true);
    const notificationsCount = this.getNotificationCount(chat);
    const participantsCount = team.get('agents').size;
    const title = `${header} (${participantsCount} participant${participantsCount === 1 ? '' : 's'}). ${notificationsCount} unread message${notificationsCount === 1 ? '' : 's'}.`;

    return (
      <span
        className="im wrapper"
        id={`chat-${chat.get('id')}`}
        onClick={() => this.props.onRecentClick(chat.get('id'))}
      >
        <AgentTeamAvatar agentTeam={team} size={24} className="ui avatar image im" title={title} />
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </span>
    );
  }

  renderEveryone(chat) {
    const notificationsCount = this.getNotificationCount(chat);

    return (
      <span
        className="im wrapper"
        id={`chat-${chat.get('id')}`}
        onClick={() => this.props.onRecentClick(chat.get('id'))}
      >
        {AvatarHelper.renderEveryoneAvatar(notificationsCount)}
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </span>
    );
  }

  renderGroup(chat) {
    const notificationsCount = this.getNotificationCount(chat);

    return (
      <span
        className="im wrapper"
        id={`chat-${chat.get('id')}`}
        onClick={() => this.props.onRecentClick(chat.get('id'))}
      >
        {AvatarHelper.renderGroupAvatar(chat, notificationsCount)}
        {this.renderRemoveButton(chat)}
        {this.renderNotificationsBalloon(chat)}
      </span>
    );
  }

  render() {
    const { agentsLoaded, teamsLoaded, departmentsLoaded, recentLoaded } = this.props;
    const loaded = agentsLoaded && teamsLoaded && departmentsLoaded && recentLoaded;
    return (
      <Loader loaded={loaded} opacity={0} width={3} scale={0.5} color="#4696dc">
        <div className={classNames(['im', 'recent', { empty: this.state.chats.size < 1 }])}>
          {this.getItems()}
          {this.props.children}
        </div>
      </Loader>
    );
  }
}

export default TopBarRecentImList;
