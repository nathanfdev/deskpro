import React from 'react';
import { connect } from 'react-redux';
import AgentsListItem from './AgentsListItem'

/**
 * TODO: find a way to avoid this really srong dark magic arount porps.agents and state.agents. The point is that when
 * TODO: rendering this template at the very first time you have nothing in props.agents, cause ajax still on progress
 * TODO: and promise have no data yet.
 */
const AgentsList = React.createClass(
    {
    getInitialState: function() {
        return {
            value: false,
            agents: this.filterAgents()
        };
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
                        {this.state.agents.length > 0 ? this.state.agents.map((agent, index) => <AgentsListItem key={index} agent={agent} highlight={this.state.value}/>) : this.props.agents.map((agent, index) => <AgentsListItem key={index} agent={agent} />)}
                    </ul>
                </div>
            </div>
        );
    },

    onChange: function(event)
    {
        const newState = {
            ...this.state,
            agents: this.filterAgents(event.target.value), value: event.target.value,
        }
        this.setState(newState);
    },

    filterAgents: function(value = '')
    {
        let newAgents = [];
        if(typeof value == 'string' && value.trim().length > 0) {
            this.props.agents.forEach((agent) => {
                const name = agent.name.toLowerCase();
                if(name.indexOf(value.toLowerCase()) >= 0) {
                    newAgents.push(agent);
                }
            });
        } else {
            newAgents = this.props.agents;
        }

        return newAgents;
    }
});

module.exports = AgentsList;