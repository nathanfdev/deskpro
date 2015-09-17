import React from 'react';
import AgentsList from './Agents/AgentsList';
import TeamsList from './Teams/TeamsList';
import DepartmentsList from './Departments/DepartmentsList';
import * as actions from '../Actions/imListActions';
import { connect } from 'react-redux';

@connect(state => ({
    agents: state.IMList.agents,
    teams: state.IMList.teams,
    departments: state.IMList.departments
}))
export default class Overlay extends React.Component {

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
                    <div className="bucket left">
                        <h1>Agents</h1>
                        <AgentsList agents={this.props.agents}/>
                    </div>

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
