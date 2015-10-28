import React, { PropTypes } from 'react';
import { connect } from 'react-redux';

import Spinner from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Spinner';

import { Item } from './Item';

// chats
import * as actions from '../../Actions/chatsActions';
import * as chatActions from '../../RecordStores/Actions/imChatsActions';
import * as messagesActions from '../../Actions/messagesActions';
import { recentChatsSelector, recentChatsStatusSelector } from '../../RecordStores/Selectors/chats';

// agents
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions';
import { agentsSelector, agentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { meSelector, meStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';

// teams
import { loadMyAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions';
import { myAgentTeamsSelector, myAgentTeamsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';

// departmetns
import { loadMyDepartments } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';
import { myDepartmentsSelector, myDepartmentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  me: meSelector(state),
  meStatus: meStatusSelector(state),
  agents: agentsSelector(state),
  teams: myAgentTeamsSelector(state),
  departments: myDepartmentsSelector(state),
  recentChats: recentChatsSelector(state),
  current: state.IM.chats.get('current'),
  bubbles: state.IM.messages.get('lastMessages'),
  teamsStatus: myAgentTeamsStatusSelector(state),
  agentsStatus: agentsStatusSelector(state),
  departmentsStatus: myDepartmentsStatusSelector(state),
  recentChatsStatus: recentChatsStatusSelector(state)
}))
export class List extends React.Component {

  static propTypes = {
    me: PropTypes.object.isRequired,
    meStatus: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    recentChats: PropTypes.object.isRequired,
    current: PropTypes.object.isRequired,
    teamsStatus: PropTypes.object.isRequired,
    agentsStatus: PropTypes.object.isRequired,
    departmentsStatus: PropTypes.object.isRequired,
    recentChatsStatus: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    //const interval = setInterval(() => this.props.dispatch(messagesActions.refreshCounts()), 1000);
    this.state = {
      //interval: interval,
      stickers: {}
    };
  }

  componentWillMount() {
    const { dispatch } = this.props;
    dispatch(chatActions.loadRecentChats());
    dispatch(loadAllAgents());
    dispatch(loadMyAgentTeams());
    dispatch(loadMyDepartments());
  }

  componentWillReceiveProps(newProps) {
    if (!(newProps.bubbles instanceof Promise)) {
      const oldState = this.state;
      const newState = {...oldState};
      newState.bubbles = newProps.bubbles;
      this.setState(newState);
    }
    this.props = newProps;
  }

  startChat = (id, type) => {
    this.props.dispatch(actions.startChat(id, type));
  };

  renderList = () => {
    const { agents, teams, departments, recentChats, me, dispatch } = this.props;
    const sortedChats = recentChats.sort((first, second) => {
      const fDate = Date.parse(first.get('date_last_message'));
      const sDate = Date.parse(second.get('date_last_message'));
      if (fDate === sDate) return 0;
      return sDate - fDate;
    }).slice(0, 5);
    return (
    <span>
      {
        sortedChats.map((chat, index) => {
          return (
            <Item
              startChat={this.startChat}
              me={me}
              bubbles={this.state.bubbles}
              key={index}
              chat={chat}
              teams={teams}
              agents={agents}
              departments={departments}
              dispatch={dispatch}
              />
            );
        })
      }
    </span>);
  };

  renderLoading = () => {
    return <span className="chat-avatar-loading"><Spinner width="20" height="20" assignClass="recent-spinner"/> Loading recent agents... </span>;
  };

  render() {
    const { recentChatsStatus, agentsStatus, teamsStatus, departmentsStatus, meStatus } = this.props;
    return (
      recentChatsStatus.get('isDone')
      && agentsStatus.get('isDone')
      && teamsStatus.get('isDone')
      && departmentsStatus.get('isDone')
      && meStatus.get('isDone')
    )
      ? this.renderList()
      : this.renderLoading();
  }
}
