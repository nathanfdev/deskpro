import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Overlay } from './Overlay';
import { Chat } from './ChatWindow/Chat';
import { Recent } from './Recent';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';

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

  teamsStatus: recentChatsStatusSelector(state),
  agentsStatus: agentsStatusSelector(state),
  departmentsStatus: myDepartmentsStatusSelector(state),
  recentChatsStatus: myAgentTeamsStatusSelector(state)
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
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      overlayShown: false,
      chating: false
    };
  }

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
    newState.overlayShown = !this.state.overlayShown;
    newState.chating = this.state.chating;
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
    newState.overlayShown = false;
    this.setState(newState);
  };

  renderChat = () => {
    const {agents, teams, departments, current} = this.props;
    const {agentsStatus, teamsStatus, departmentsStatus} = this.props;
    return this.state.chating && current.id && agentsStatus.get('isDone') && teamsStatus.get('isDone') && departmentsStatus.get('isDone')
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

  renderOverlay = () => {
    if (this.state.overlayShown) {
      return (
        <Overlay
          me={this.props.me}
          agents={this.props.agents}
          teams={this.props.teams}
          departments={this.props.departments}
          dispatch={this.props.dispatch}
          handleClickParticipant={this.handleClickParticipant}
          />
      );
    }
  };

  render() {
    return (
      <div className="agent-ims">
        <a href="#" onClick={this.onClick} ref="imListButton" className="show-more">
            <span>
                IMs <i className="fa fa-angle-down"></i>
            </span>
        </a>
        { this.renderRecent() }
        { this.renderOverlay() }
        { this.renderChat() }
      </div>
    );
  }
}
