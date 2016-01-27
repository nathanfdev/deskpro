import React, { PropTypes } from 'react';
import { connect } from 'react-redux';

import Loader from 'react-loader';

import { Item } from './Item';

// chats
import * as actions from '../../Actions/chatsActions';
import * as chatActions from '../../RecordStores/Actions/chatsActions';
import * as messagesActions from '../../Actions/messagesActions';
import { recentChatsSelector, recentChatsStatusSelector } from '../../RecordStores/Selectors/chats';

// agents
import { agentsSelector, agentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';

// teams
import { myAgentTeamsSelector, myAgentTeamsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';

// departmetns
import { myDepartmentsSelector, myDepartmentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  me: meSelector(state),
  agents: agentsSelector(state),
  teams: myAgentTeamsSelector(state),
  departments: myDepartmentsSelector(state),
  recentChats: recentChatsSelector(state),
  current: state.IM.chats.get('current'),
  chating: state.IM.chats.get('chating'),
  counts: state.IM.messages.get('counts'),
  loadingCounts: state.IM.messages.get('loadingCounts'),
  teamsStatus: myAgentTeamsStatusSelector(state),
  agentsStatus: agentsStatusSelector(state),
  departmentsStatus: myDepartmentsStatusSelector(state),
  recentChatsStatus: recentChatsStatusSelector(state)
}))
export class List extends React.Component {

  static propTypes = {
    me: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    recentChats: PropTypes.object.isRequired,
    current: PropTypes.object.isRequired,
    counts: PropTypes.object.isRequired,
    loadingCounts: PropTypes.bool.isRequired,
    teamsStatus: PropTypes.object.isRequired,
    agentsStatus: PropTypes.object.isRequired,
    departmentsStatus: PropTypes.object.isRequired,
    recentChatsStatus: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      stickers: {}
    };
  }

  componentWillMount() {
    this.props.dispatch(chatActions.loadRecentChats());
    this.refreshCounts();
  }

  componentWillReceiveProps(props) {
    const oldProps = this.props;
    if (!props.loadingCounts && props.counts && props.counts !== oldProps.counts) {
      const { dispatch } = this.props;
      const records = {};
      const ids = [];
      console.log(props.counts);
      Object.keys(props.counts).map((key) => {
        const item = props.counts[key];
        ids.push(parseInt(item.chat_id, 10));
        records[item.chat_id] = item.chat;
      });
      dispatch(chatActions.releaseChats('recent', ids));
      dispatch(chatActions.setChatsRequest('recent', records, ids));
    }
    this.props = props;
  }

  refreshCounts() {
    if (this.props.recentChatsStatus.get('isDone')) {
      this.props.dispatch(messagesActions.refreshCounts());
    } else {
      setTimeout(this.refreshCounts.bind(this), 2000);
    }
  }

  startChat = (id, type, chatId) => {
    this.props.dispatch(actions.startChat(id, type, chatId));
  };

  render() {
    const { recentChatsStatus, agentsStatus, teamsStatus, departmentsStatus, loadingCounts, chating } = this.props;
    const loaded = (
      recentChatsStatus.get('isDone')
      && agentsStatus.get('isDone')
      && teamsStatus.get('isDone')
      && departmentsStatus.get('isDone')
    );
    const { agents, teams, departments, recentChats, me, dispatch, counts, current } = this.props;
    const sortedChats = recentChats.sort((first, second) => {
      const fDate = Date.parse(first.get('date_last_message'));
      const sDate = Date.parse(second.get('date_last_message'));
      return sDate - fDate;
    }).slice(0, 5);

    return (
      <Loader loaded={loaded} opacity={0} width={3} scale={0.5} left="125%" color="#fff" component="span">
        {
          sortedChats.toList().map((chat) => {
            return (
              <Item
                current={current}
                chating={chating}
                startChat={this.startChat}
                me={me}
                counts={counts}
                loadingCounts={loadingCounts}
                key={chat.get('id')}
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
