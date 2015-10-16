import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import SimplePositioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Simple';
import { Overlay } from './Overlay';
import jQuery from 'jquery';
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
  chating: state.IM.chats.get('chating'),
  overlayShown: state.IM.ui.get('overlayShown'),
  teamsStatus: myAgentTeamsStatusSelector(state),
  agentsStatus: agentsStatusSelector(state),
  departmentsStatus: myDepartmentsStatusSelector(state),
  recentChatsStatus: myAgentTeamsStatusSelector(state)
}))
export class IMContainer extends React.Component {

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
    chating: PropTypes.bool.isRequired,
    overlayShown: PropTypes.bool.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      chating: this.props.chating,
      overlayShown: this.props.overlayShown
    };
  }

  componentWillMount() {
    const { dispatch } = this.props;
    dispatch(loadAllAgents());
    dispatch(loadMyAgentTeams());
    dispatch(loadMyDepartments());
  }

  renderOverlay = () => {
    return (
      <SimplePositioned
        positionMy="left-15 top"
        positionAt="center bottom"
        collision="none"
        positionTarget={document.getElementById('#im-button')}
        isOpen={this.props.overlayShown}
        >
        <Overlay
          me={this.props.me}
          agents={this.props.agents}
          teams={this.props.teams}
          departments={this.props.departments}
          dispatch={this.props.dispatch}
          handleClickParticipant={this.handleClickParticipant}
          />
      </SimplePositioned>
    );
  };

  render() {
    return (
     <div id="im-container">
       {this.renderOverlay()}
     </div>
    );
  }
}