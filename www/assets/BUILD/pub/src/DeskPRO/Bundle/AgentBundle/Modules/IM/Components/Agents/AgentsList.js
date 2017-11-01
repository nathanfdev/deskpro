import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { AgentsListItem } from './AgentsListItem';
import { connect } from 'react-redux';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

@connect(state => ({
  me:     meSelector(state),
  agents: agentsSelector(state)
}))
export class AgentsList extends Component {
  static propTypes = {
    me:       PropTypes.object.isRequired,
    agents:   PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      value:  false,
      agents: this.filterAgents.bind(this)
    };
  }

  onChange(event) {
    const oldState = this.state;
    const newState = {
      ...oldState,
      agents: this.filterAgents(event.target.value),
      value:  event.target.value
    };
    this.setState(newState);
  }

  filterAgents(value = '') {
    let newAgents = [];
    if (typeof value === 'string' && value.trim()) {
      this.props.agents.forEach((agent) => {
        const name = agent.get('name').toLowerCase();
        if (name.indexOf(value.toLowerCase()) >= 0) {
          newAgents.push(agent);
        }
      });
    } else {
      newAgents = this.props.agents.toArray();
    }

    return newAgents;
  }

  render() {
    return (
      <div className="bucket left">
        <h1>Agents</h1>

        <div className="show-offline-agents">
          <input type="checkbox" id="checkbox-name" /><label htmlFor="checkbox-name"></label> Show offline agents?
        </div>
        <form>
          <div>
            <input type="text" onChange={this.onChange.bind(this)} placeholder="Filter agents by name" />
          </div>
        </form>
        <div className="im-list-wrapper">
          <ul className="im-list">
            {
              this.state.agents.length > 0
                ? this.state.agents.map(
                (agent, index) => {
                  if (this.props.me.get('id') !== agent.get('id')) {
                    return (<AgentsListItem
                      key={index}
                      agent={agent}
                      highlight={this.state.value}
                    />);
                  }
                }
              )
                : this.props.agents.map(
                (agent, index) => {
                  if (this.props.me.get('id') !== agent.get('id')) {
                    return (<AgentsListItem
                      key={index}
                      agent={agent}
                      highlight={this.state.value}
                    />);
                  }
                }
              )
            }
          </ul>
        </div>
      </div>
    );
  }
}
