import React, { PropTypes } from 'react';
import { connect } from 'react-redux';

import Spinner from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Spinner';

import { Item } from './Item';

// chats
import * as actions from '../../Actions/chatsActions';
import * as chatActions from '../../RecordStores/Actions/imChatsActions';
import { recentChatsSelector, recentChatsStatusSelector } from '../../RecordStores/Selectors/chats';

// agents
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions';
import { agentsSelector, agentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';

// teams
import { loadMyAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions';
import { myAgentTeamsSelector, myAgentTeamsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';

// departmetns
import { loadMyDepartments } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';
import { myDepartmentsSelector, myDepartmentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  me: meSelector(state),
  teams: myAgentTeamsSelector(state),
  agents: agentsSelector(state),
  current: state.IM.chats.get('current'),
  departments: myDepartmentsSelector(state),
  recentChats: recentChatsSelector(state),
  teamsStatus: myAgentTeamsStatusSelector(state),
  agentsStatus: agentsStatusSelector(state),
  departmentsStatus: myDepartmentsStatusSelector(state),
  recentChatsStatus: recentChatsStatusSelector(state)
}))
export class List extends React.Component {

  static propTypes = {
    agents: PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    me: PropTypes.object.isRequired,
    chat: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  componentWillMount() {
    const { dispatch } = this.props;
    dispatch(chatActions.loadRecentChats());
    dispatch(loadAllAgents());
    dispatch(loadMyAgentTeams());
    dispatch(loadMyDepartments());
  }

  startChat = (id, type) => {
    this.props.dispatch(actions.startChat(id, type));
  };

  renderList = () => {
    const { agents, teams, departments, recentChats, me } = this.props;
    return (
    <span>
      { recentChats.map((chat, index) => <Item startChat={this.startChat} me={me} key={index} chat={chat} teams={teams} agents={agents} departments={departments}/>) }
    </span>);
  };

  renderLoading = () => {
    return <span className="chat-avatar-loading"><Spinner width="20" height="20" assignClass="recent-spinner"/> Loading recent agents... </span>;
  };

  render() {
    const { recentChatsStatus, agentsStatus, teamsStatus, departmentsStatus } = this.props;
    return (
      recentChatsStatus.get('isDone')
      && agentsStatus.get('isDone')
      && teamsStatus.get('isDone')
      && departmentsStatus.get('isDone')
    )
      ? this.renderList()
      : this.renderLoading();
  }
}
