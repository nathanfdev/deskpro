import React, { PropTypes } from 'react';
import { connect } from 'react-redux';

import { Chat } from './ChatWindow/Chat';
import { Recent } from './Recent';
// ui
import * as uiActions from '../Actions/uiActions';

// chats
import * as chatActions from '../RecordStores/Actions/imChatsActions';
import { recentChatsSelector, recentChatsStatusSelector } from '../RecordStores/Selectors/chats';

// agents
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions';
import { agentsSelector, agentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';

// teams
import { loadMyAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions';
import { myAgentTeamsSelector, myAgentTeamsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';

// departmetns
import { loadMyDepartments } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';
import { myDepartmentsSelector, myDepartmentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  me: state.Application.user,
  teams: myAgentTeamsSelector(state),
  agents: agentsSelector(state),
  current: state.IM.chats.get('current'),
  departments: myDepartmentsSelector(state),
  recentChats: recentChatsSelector(state),
  chating: state.IM.ui.get('chating'),
  teamsStatus: myAgentTeamsStatusSelector(state),
  agentsStatus: agentsStatusSelector(state),
  departmentsStatus: myDepartmentsStatusSelector(state),
  recentChatsStatus: recentChatsStatusSelector(state)
}))
export class HeaderWidget extends React.Component {
  static propTypes = {
    me: PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    current: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    recentChats: PropTypes.object.isRequired,
    recentChatsStatus: PropTypes.object.isRequired,
    teamsStatus: PropTypes.object.isRequired,
    departmentsStatus: PropTypes.object.isRequired,
    agentsStatus: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    chating: PropTypes.bool.isRequired
  };


  componentWillMount() {
    const { dispatch } = this.props;
    dispatch(chatActions.loadRecentChats());
    dispatch(loadAllAgents());
    dispatch(loadMyAgentTeams());
    dispatch(loadMyDepartments());
  }

  onClick = () => {
    const oldState = this.state;
    const newState = {...oldState};
    this.props.dispatch(uiActions.toggleOverlay());
    this.setState(newState);
  };

  handleCloseChat = () => {
    const oldState = this.state;
    const newState = {...oldState};
    newState.chating = false;
    this.setState(newState);
  };

  handleClickParticipant = () => {
    const oldState = this.state;
    const newState = {...oldState};
    newState.chating = true;
    this.setState(newState);
  };

  renderChat = () => {
    const {agents, teams, departments, current} = this.props;
    return this.props.chating && current.id
      ? (<Chat
      teams={teams}
      agents={agents}
      current={current}
      departments={departments}
      handleCloseChat={this.handleCloseChat}/>)
      : null;
  };

  renderRecent = () => {
    const { agents, teams, departments, recentChats, me } = this.props;
    const { recentChatsStatus, agentsStatus, teamsStatus, departmentsStatus } = this.props;
    return (
      recentChatsStatus.get('isDone')
      && agentsStatus.get('isDone')
      && teamsStatus.get('isDone')
      && departmentsStatus.get('isDone')
    )
      ? recentChats.map(
      (chat, index) => {
        return (<Recent
          me={me}
          key={index}
          chat={chat}
          teams={teams}
          agents={agents}
          departments={departments}
          handleClickParticipant={this.handleClickParticipant}
          />);
      }
    )
      : <span className="chat-avatar-loading"><img src="/web/spinner.gif" style={{width: 20 + 'px', height: 20 + 'px'}}/> Loading recent agents... </span>;
  };

  render() {
    return (
      <div className="agent-ims">
        <a href="#" id="im-button" onClick={this.onClick} className="show-more">
            <span>
                IMs <i className="fa fa-angle-down"></i>
            </span>
        </a>
        { this.renderRecent() }
        { this.renderChat() }
      </div>
    );
  }
}
