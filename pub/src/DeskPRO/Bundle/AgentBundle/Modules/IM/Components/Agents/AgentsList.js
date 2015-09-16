import React from 'react';
import { connect } from 'redux/react';
import AgentsListItem from './AgentsListItem'

const AgentsList = React.createClass(
    {
    getInitialState: function() {
        return {
            agents: this.props.agents
        }
    },
    render: function() {
        return (
            <div>
                <div className="show-offline-agents">
                    <input type="checkbox" id="checkbox-name" /><label for="checkbox-name"></label> Show offline agents?
                </div>
                <form>
                    <div>
                        <input type="text" onChange={this.onChange} placeholder="Filter agents by name" />
                    </div>
                </form>
                <div className="im-list-wrapper">
                    <ul className="im-list">
                        {this.props.agents.map((agent, index) => <AgentsListItem key={index} agent={agent} />)}
                    </ul>
                </div>
            </div>
        );
    },

    onChange: function()
    {
        this.setState({agents: this.filterAgents()});
    },

    filterAgents: function ()
    {
        return this.props.agents;
    }
});

module.exports = AgentsList;