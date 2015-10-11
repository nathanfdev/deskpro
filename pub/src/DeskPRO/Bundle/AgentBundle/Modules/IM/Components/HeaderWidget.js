import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Overlay } from './Overlay';
import { Chat } from './ChatWindow/Chat';
import { Recent } from './Recent';

// chats
import * as chatActions from '../RecordStores/Actions/imChatsActions';
import { recentChatsSelector } from '../RecordStores/Selectors/chats';

// agents
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions'
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';

//teams
import { loadMyAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions'
import { myAgentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';

// departmetns
import { loadMyDepartments } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';
import { myDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  recentChats: recentChatsSelector(state),
  current: state.IM.chats.get('current'),
  agents: agentsSelector(state),
  departments: myDepartmentsSelector(state),
  teams: myAgentTeamsSelector(state),
  me: state.Application.user
}))
export class HeaderWidget extends React.Component {
  static propTypes = {
    recentChats: PropTypes.array.isRequired,
    current: PropTypes.object.isRequired,
    me: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      overlayShown: false,
      chating: false,
    };
  }

  componentWillMount() {
    const { dispatch } = this.props;
    dispatch(chatActions.loadRecentChats());
    dispatch(loadAllAgents());
    dispatch(loadMyAgentTeams());
    dispatch(loadMyDepartments());
  }

  render() {
    const {agents, teams, departments, recentChats, current, me} = this.props;
    const {overlayShown, chating} = this.state;

    return (
      <div className="agent-ims">
        <a href="#" onClick={this.onClick} className="show-more">
            <span>
                IMs <i className="fa fa-angle-down"></i>
            </span>
        </a>
        { recentChats.map(
            (chat, index) => {
              return <Recent
                me                     = {me}
                key                    = {index}
                chat                   = {chat}
                teams                  = {teams}
                agents                 = {agents}
                departments            = {departments}
                handleClickParticipant = {this.handleClickParticipant}
                />
            }
          )
        }
        { overlayShown ? <Overlay handleClickParticipant={this.handleClickParticipant}/> : null }
        { chating && current.id
          ? <Chat
              teams           = {teams}
              agents          = {agents}
              current         = {current}
              departments     = {departments}
              handleCloseChat = {this.handleCloseChat}/>
          : null
        }
      </div>
    );
  }

  handleCloseChat = () => {
    const oldState = this.state;
    let newState = {...oldState};
    newState.chating = false;
    this.setState(newState);
  };

  onClick = () => {
    const oldState = this.state;
    let newState = {...oldState};
    newState.overlayShown = !this.state.overlayShown,
      newState.chating = this.state.chating
    this.setState(newState);
  };

  handleClickParticipant = () => {
    const oldState = this.state;
    let newState = {...oldState};
    newState.chating = true;
    newState.overlayShown = false;
    this.setState(newState);
  };

}