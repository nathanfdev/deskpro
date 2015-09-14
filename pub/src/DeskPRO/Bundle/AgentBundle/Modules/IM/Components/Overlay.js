import React from 'react';
import AgentsList from './AgentsList.js';
import TeamsList from './TeamsList.js';
import DepartmentsList from './DepartmentsList.js';

export default class Overlay extends React.Component {
    render() {
        return (
            <div className="dropdown im-dropdown" id="im-dropdown">
                <header className="dropdown-header">Agent Instant Messages</header>
                <div className="wrapper">
                    <div className="bucket left">
                        <h1>Agents</h1>
                        <div className="show-offline-agents">
                            <input type="checkbox" id="checkbox-name" /><label for="checkbox-name"></label> Show offline agents?
                        </div>

                        <form>
                            <div>
                                <input type="text" placeholder="Filter agents by name" />
                            </div>
                              </form>
                        <div className="im-list-wrapper">
                            <AgentsList />
                        </div>
                    </div>

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

