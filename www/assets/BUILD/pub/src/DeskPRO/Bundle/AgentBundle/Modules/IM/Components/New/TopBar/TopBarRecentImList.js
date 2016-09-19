import React, { PropTypes } from 'react';
import Loader from 'react-loader';
import {
  Avatar,
  DepartmentAvatar,
  PersonAvatar,
  AgentTeamAvatar
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import classNames from 'classnames';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import { RecentList } from 'DeskPRO/Bundle/AgentBundle/Modules/IM/Components/New/IMTabs';

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
    agentsLoaded:      PropTypes.bool.isRequired
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
    return (<span className="im wrapper" id={`chat-${chat.get('id')}`} onClick={() => this.props.onRecentClick(chat.get('id'))}>
      <PersonAvatar
        color={chooseColor(agent.get('id'))}
        person={agent} size={24}
        classes={['ui avatar image im']}
      />
    </span>);
  }

  renderDepartment(chat) {
    const department = this.props.departments.getIn(chat.get('departments', 0));

    return (
      <span className="im wrapper" id={`chat-${chat.get('id')}`} onClick={() => this.props.onRecentClick(chat.get('id'))}>
        <DepartmentAvatar department={department} size={24} classes={['ui avatar image im']} />
      </span>
    );
  }

  renderTeam(chat) {
    const team = this.props.teams.get(chat.getIn(['agent_teams', 0]));

    return (
      <span className="im wrapper" id={`chat-${chat.get('id')}`} onClick={() => this.props.onRecentClick(chat.get('id'))}>
        <AgentTeamAvatar agentTeam={team} size={24} classes={['ui avatar image im']} />
      </span>
    );
  }

  renderEveryone(chat) {
    const props = {
      size:       24,
      color:      '#DD00AA',
      urlPattern: null,
      gravatar:   null,
      text:       'E',
      classes:    ['ui avatar image im']
    };
    return (
      <span className="im wrapper" id={`chat-${chat.get('id')}`} onClick={() => this.props.onRecentClick(chat.get('id'))}>
        <Avatar {...props} />
      </span>
    );
  }

  render() {
    const { agentsLoaded, teamsLoaded, departmentsLoaded, recentLoaded } = this.props;
    const loaded = agentsLoaded && teamsLoaded && departmentsLoaded && recentLoaded;
    return (
      <Loader loaded={loaded} opacity={0} width={3} scale={0.5} color="#4696dc">
        <div className={classNames(['im', 'recent', { empty: this.props.chats.size < 1 }])}>
            {this.getItems()}
            {this.props.children}
        </div>
      </Loader>
    );
  }
}

export default TopBarRecentImList;
