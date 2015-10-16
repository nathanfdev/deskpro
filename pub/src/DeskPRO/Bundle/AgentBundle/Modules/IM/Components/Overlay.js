import React, { PropTypes } from 'react';
import { AgentsList } from './Agents/AgentsList';
import { TeamsList } from './Teams/TeamsList';
import { DepartmentsList } from './Departments/DepartmentsList';

export class Overlay extends React.Component {

  static propTypes = {
    me: PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    handleClickParticipant: PropTypes.func.isRequired
  };

  render() {
    return (
      <div className="dropdown im-dropdown" id="im-dropdown">
        <header className="dropdown-header">Agent Instant Messages</header>
        <div className="wrapper">
          <AgentsList
            me={this.props.me}
            agents={this.props.agents}
            dispatch={this.props.dispatch}
            handleClickParticipant={this.props.handleClickParticipant}
            />

          <div className="bucket right">
            <a href="#" className="broadcast-to-all"><i className="fa fa-bullhorn"></i> Broadcast to Everyone</a>

            <div className="im-list-wrapper">
              <h2>Teams</h2>
              <TeamsList
                agentTeams={this.props.teams}
                dispatch={this.props.dispatch}
                handleClickParticipant={this.props.handleClickParticipant}
                />
              <h2>Departments</h2>
              <DepartmentsList
                departments={this.props.departments}
                dispatch={this.props.dispatch}
                handleClickParticipant={this.props.handleClickParticipant}/>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
