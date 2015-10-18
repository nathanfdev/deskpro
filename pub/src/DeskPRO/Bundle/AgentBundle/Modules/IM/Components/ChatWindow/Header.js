import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import * as ui from '../../Actions/uiActions';

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
  agents: agentsSelector(state),
  agentsStatus: agentsStatusSelector(state),
  teams: myAgentTeamsSelector(state),
  teamsStatus: myAgentTeamsStatusSelector(state),
  departments: myDepartmentsSelector(state),
  departmentsStatus: myDepartmentsStatusSelector(state),
  current: state.IM.chats.get('current')

}))
export class Header extends React.Component {


  static propTypes = {
    me: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    agentsStatus: PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired,
    teamsStatus: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    departmentsStatus: PropTypes.object.isRequired,
    current: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(loadAllAgents());
    dispatch(loadMyAgentTeams());
    dispatch(loadMyDepartments());
  }

  closeChat = () => {
    this.props.dispatch(ui.closeChat());
  };

  calculateAgentText = () => {
    const { agents, current, me } = this.props;
    if (agents && agents.size > 0) {
      const filteredAgents = current.agents.filter(agent => agent !== me.get('id') );
      let text = agents.getIn([filteredAgents[0], 'name']);
      if (filteredAgents.length > 1) {
        text = ' and ' + (filteredAgents.length - 1) + ' more';
      }
      return text;
    }
  };

  renderHeader() {
    const { current, teams, departments } = this.props;
    let text;
    switch (current.chat_type) {
      case 'agent':
        text = this.calculateAgentText();
        break;
      case 'team':
        text = teams.getIn([current.agent_teams[0], 'name']);
        break;
      case 'department':
        text = departments.getIn([current.departments[0], 'title']);
        break;
      default:
        text = 'Unknown chat. ALARM!!!';
    }

    return <h1>Your IM with <span>{text}</span> {this.renderOnline()}</h1>;
  }

  renderOnline() {
    if (this.props.current.chat_type === 'agent') {
      return <b className="user-status online"></b>;
    }
  }

  render() {
    return (
      <header>
        <div className="header-controls">
          <a href="#"><i className="fa fa-search"></i> Search IM</a>
                    <span className="close">
                      <a href="#" onClick={this.closeChat}><i className="fa fa-times"></i></a>
                    </span>
        </div>
        { this.renderHeader() }
      </header>
    );
  }

}
