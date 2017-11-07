import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Loader from 'react-loader';
import { Item } from './Item';
import * as actions from '../../Actions/chatsActions';
import * as messagesActions from '../../Actions/messagesActions';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { myTicketsDepartmentsSelector, myAgentTeamsSelector, addToCollection, loadFromApi, isLoadedCollectionSelectorFactory,
  collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  me:            meSelector(state),
  agents:        agentsSelector(state),
  teams:         myAgentTeamsSelector(state),
  departments:   myTicketsDepartmentsSelector(state),
  recentChats:   collectionSelectorFactory('AgentChat', 'recent')(state),
  current:       state.IM.chats.get('current'),
  chating:       state.IM.chats.get('chating'),
  counts:        state.IM.messages.get('counts'),
  loadingCounts: state.IM.messages.get('loadingCounts'),
  loaded:        isLoadedCollectionSelectorFactory('AgentChat', 'recent')(state)
}))
export class List extends React.Component {

  static propTypes = {
    me:            PropTypes.object.isRequired,
    agents:        PropTypes.object.isRequired,
    teams:         PropTypes.object.isRequired,
    departments:   PropTypes.object.isRequired,
    recentChats:   PropTypes.object.isRequired,
    current:       PropTypes.object.isRequired,
    counts:        PropTypes.object.isRequired,
    loadingCounts: PropTypes.bool.isRequired,
    loaded:        PropTypes.bool.isRequired,
    dispatch:      PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      stickers: {}
    };
  }

  componentWillMount() {
    this.props.dispatch(loadFromApi(
      'AgentChat',
      'DP_API/agent_chats?order_by=date_last_message&order_dir=desc&count=5',
      'recent'
    ));

    this.refreshCounts();
  }

  refreshCounts() {
    if (this.props.loaded) {
      this.props.dispatch(messagesActions.refreshCounts());
    } else {
      setTimeout(this.refreshCounts.bind(this), 2000);
    }
  }

  startChat = (id, type, chatId) => {
    this.props.dispatch(actions.startChat(id, type, chatId));
  };

  render() {
    const { loaded, loadingCounts, chating } = this.props;
    const { agents, teams, departments, recentChats, me, dispatch, counts, current } = this.props;
    const sortedChats = recentChats.sort((first, second) => {
      const fDate = Date.parse(first.get('date_last_message'));
      const sDate = Date.parse(second.get('date_last_message'));
      return sDate - fDate;
    }).slice(0, 5);

    return (
      <Loader loaded={loaded} opacity={0} width={3} scale={0.5} left="125%" color="#fff" component="span">
        {sortedChats.toList().map((chat) =>
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
        )}
      </Loader>
    );
  }
}
