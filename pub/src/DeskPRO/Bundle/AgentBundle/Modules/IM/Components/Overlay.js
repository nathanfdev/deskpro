import React from 'react';
import AgentsList from './Agents/AgentsList';
import TeamsList from './Teams/TeamsList';
import DepartmentsList from './Departments/DepartmentsList';
import * as actions from '../Actions/imListActions';
import { connect } from 'react-redux';

@connect(state => ({
    agents: state.IM.list.get('agents'),
    teams: state.IM.list.get('teams'),
    departments: state.IM.list.get('departments')
}))
export class Overlay extends React.Component {
    constructor(props) {
        super(props);
        this.props.dispatch(actions.loadAgents());
        this.props.dispatch(actions.loadTeams());
        this.props.dispatch(actions.loadDepartments());
    }

    render () {
        return (
            <div className="dropdown im-dropdown" id="im-dropdown">
                <header className="dropdown-header">Agent Instant Messages</header>
                <div className="wrapper">
                    <AgentsList handler={this.props.agentClickHandler} agents={this.props.agents}/>
                    <div className="bucket right">
                        <a href="#" className="broadcast-to-all"><i className="fa fa-bullhorn"></i> Broadcast to Everyone</a>
                        <div className="im-list-wrapper">
                            <h2>Teams</h2>
                            <TeamsList teams={this.props.teams}/>
                            <h2>Departments</h2>
                            <DepartmentsList departments={this.props.departments} />
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}
