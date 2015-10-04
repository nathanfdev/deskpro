import React from 'react';
import { AgentsList } from './Agents/AgentsList';
import { TeamsList } from './Teams/TeamsList';
import { DepartmentsList } from './Departments/DepartmentsList';
import * as actions from '../Actions/imListActions';

export class Overlay extends React.Component {
    render () {
        return (
            <div className="dropdown im-dropdown" id="im-dropdown">
                <header className="dropdown-header">Agent Instant Messages</header>
                <div className="wrapper">
                    <AgentsList handleClickParticipant={this.props.handleClickParticipant} />
                    <div className="bucket right">
                        <a href="#" className="broadcast-to-all"><i className="fa fa-bullhorn"></i> Broadcast to Everyone</a>
                        <div className="im-list-wrapper">
                            <h2>Teams</h2>
                            <TeamsList />
                            <h2>Departments</h2>
                            <DepartmentsList />
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}
