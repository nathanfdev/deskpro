import React from 'react';
import { connect } from 'redux/react';
import AgentsListItem from './AgentsListItem'

const AgentsList = React.createClass(
    {
    getInitialState: function() {
        return {
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
                        {this.state.agents.length > 0 ? this.state.agents.map((agent, index) => <AgentsListItem key={index} agent={agent} />) : this.props.agents.map((agent, index) => <AgentsListItem key={index} agent={agent} />)}
                    </ul>
                </div>
            </div>
        );
    },

    onChange: function(event)
    {
        this.setState({agents: this.filterAgents(event.target.value)});
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
            console.log(this.props.agents);
            newAgents = this.props.agents;
        }

        return newAgents;
    }
});

module.exports = AgentsList;