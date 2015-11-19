import React, { PropTypes } from 'react';
import { connect } from 'react-redux';

import Loader from 'react-loader';

import { Item } from './Item';

// chats
import * as actions from '../../Actions/chatsActions';
import * as chatActions from '../../RecordStores/Actions/chatsActions';
// import * as messagesActions from '../../Actions/messagesActions';
import { recentChatsSelector, recentChatsStatusSelector } from '../../RecordStores/Selectors/chats';

// agents
import { agentsSelector, agentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { meSelector, meStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';

// teams
import { myAgentTeamsSelector, myAgentTeamsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';

// departmetns
import { myDepartmentsSelector, myDepartmentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  me: meSelector(state),
  meStatus: meStatusSelector(state),
  agents: agentsSelector(state),
  teams: myAgentTeamsSelector(state),
  departments: myDepartmentsSelector(state),
  recentChats: recentChatsSelector(state),
  current: state.IM.chats.get('current'),
  counts: state.IM.messages.get('counts'),
  countsLoading: state.IM.messages.get('countsLoading'),
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
    counts: PropTypes.object.isRequired,
    loadingCounts: PropTypes.object,
    countsLoading: PropTypes.bool.isRequired,
    teamsStatus: PropTypes.object.isRequired,
    agentsStatus: PropTypes.object.isRequired,
    departmentsStatus: PropTypes.object.isRequired,
    recentChatsStatus: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    // const interval = setInterval(() => this.props.dispatch(messagesActions.refreshCounts()), 1000);
    this.state = {
      // interval: interval,
      stickers: {}
    };
  }

  componentWillMount() {
    this.props.dispatch(chatActions.loadRecentChats());
  }

  startChat = (id, type) => {
    this.props.dispatch(actions.startChat(id, type));
  };

  render() {
    const { recentChatsStatus, agentsStatus, teamsStatus, departmentsStatus, meStatus } = this.props;
    const loaded = (
      recentChatsStatus.get('isDone')
      && agentsStatus.get('isDone')
      && teamsStatus.get('isDone')
      && departmentsStatus.get('isDone')
      && meStatus.get('isDone')
    );
    const { agents, teams, departments, recentChats, me, dispatch, counts, loadingCounts } = this.props;
    const sortedChats = recentChats.sort((first, second) => {
      const fDate = Date.parse(first.get('date_last_message'));
      const sDate = Date.parse(second.get('date_last_message'));
      if (fDate === sDate) return 0;
      return sDate - fDate;
    }).slice(0, 5);

    return (
      <Loader loaded={loaded} opacity={0} width={3} scale={0.5} left="125%" color="#fff" component="span">
        {
          sortedChats.map((chat, index) => {
            return (
              <Item
                startChat={this.startChat}
                me={me}
                counts={loadingCounts ? {} : counts}
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
      </Loader>
    );
  }
}
