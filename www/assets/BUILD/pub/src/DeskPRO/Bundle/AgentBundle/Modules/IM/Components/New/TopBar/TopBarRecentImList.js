import React, { PropTypes } from 'react';
import Loader from 'react-loader';
import Immutable from 'immutable';
import {
  DepartmentAvatar,
  PersonAvatar,
  AgentTeamAvatar
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import classNames from 'classnames';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import { RecentList } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs';
import AvatarHelper from '../IMTabs/AvatarHelper';

class TopBarRecentImList extends RecentList {

  static propTypes = {
    chats:             PropTypes.object.isRequired,
    children:          PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
    onRecentClick:     PropTypes.func.isRequired,
    me:                PropTypes.object.isRequired,
    agents:            PropTypes.object.isRequired,
    departments:       PropTypes.object.isRequired,
    teams:             PropTypes.object.isRequired,
    recentLoaded:      PropTypes.bool.isRequired,
    teamsLoaded:       PropTypes.bool.isRequired,
    departmentsLoaded: PropTypes.bool.isRequired,
    agentsLoaded:      PropTypes.bool.isRequired,
    counts:            PropTypes.object
  };

  static defaultProps = {
    counts: {
      nested: {}
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
      RecentList.sortList(props.chats).forEach((chat) => {
        const chatId = chat.get('id');
        const path   = [chatId, 'added'];
        chats = chats.set(chatId, chat.set('added', chats.hasIn(path) ? chats.getIn(path) : Date.now()));
      });
    }
    chats = chats.sort((a, b) => b.get('added') - a.get('added'));
    this.setState({ chats });
  }

  getItems() {
    return this.state.chats.slice(0, 10).map(agent => this.getItem(agent));
  }

  renderNotificationsBalloon(chat) {
    const notificationCount = this.getNotificationCount(chat);
    return notificationCount ? <div className="ui knuckles label message-counter">{notificationCount}</div> : null;
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
    if (!agent.get('online')) {
      className.push('offline');
    }

    return (
      <span
        className="im wrapper"
        id={`chat-${chat.get('id')}`}
        onClick={() => this.props.onRecentClick(chat.get('id'))}
      >
        <PersonAvatar
          color={chooseColor(agent.get('id'))}
          person={agent} size={24}
          className="ui avatar image im"
        />
        {this.renderNotificationsBalloon(chat)}
      </span>
    );
  }

  renderDepartment(chat) {
    const department = this.props.departments.getIn(chat.get('departments', 0));

    return (
      <span
        className="im wrapper"
        id={`chat-${chat.get('id')}`}
        onClick={() => this.props.onRecentClick(chat.get('id'))}
      >
        <DepartmentAvatar department={department} size={24} className="ui avatar image im" />
        {this.renderNotificationsBalloon(chat)}
      </span>
    );
  }

  renderTeam(chat) {
    const team = this.props.teams.get(chat.getIn(['agent_teams', 0]));

    return (
      <span
        className="im wrapper"
        id={`chat-${chat.get('id')}`}
        onClick={() => this.props.onRecentClick(chat.get('id'))}
      >
        <AgentTeamAvatar agentTeam={team} size={24} className="ui avatar image im" />
        {this.renderNotificationsBalloon(chat)}
      </span>
    );
  }

  renderEveryone(chat) {
    return (
      <span
        className="im wrapper"
        id={`chat-${chat.get('id')}`}
        onClick={() => this.props.onRecentClick(chat.get('id'))}
      >
        {AvatarHelper.renderEveryoneAvatar()}
        {this.renderNotificationsBalloon(chat)}
      </span>
    );
  }

  renderGroup(chat) {
    return (
      <span
        className="im wrapper"
        id={`chat-${chat.get('id')}`}
        onClick={() => this.props.onRecentClick(chat.get('id'))}
      >
        {AvatarHelper.renderGroupAvatar(chat)}
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
