import React, {Component, PropTypes} from 'react';
import { AgentsListItem } from './AgentsListItem';

import { connect } from 'react-redux';

import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { meSelector } from '../RecordStores/Selectors/meSelectors';

/**
 * TODO: find a way to avoid this really strong dark magic around porps.agents and state.agents. The point is that when
 * TODO: rendering this template at the very first time you have nothing in props.agents, cause ajax still on progress
 * TODO: and promise have no data yet.
 */
@connect(state => ({
  agents: agentsSelector(state),
  me: meSelector(state)
}))
export class AgentsList extends Component {

  static propTypes = {
    agents: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      value: false,
      agents: this.filterAgents.bind(this)
    };
  }

  componentWillMount() {
    "use strict";
    this.props.dispatch(loadAllAgents());
  }

  render() {
    return (
      <div className="bucket left">
        <h1>Agents</h1>

        <div className="show-offline-agents">
          <input type="checkbox" id="checkbox-name"/><label htmlFor="checkbox-name"></label> Show offline agents?
        </div>
        <form>
          <div>
            <input type="text" onChange={this.onChange.bind(this)} placeholder="Filter agents by name"/>
          </div>
        </form>
        <div className="im-list-wrapper">
          <ul className="im-list">
            {
              this.state.agents.length > 0
                ? this.state.agents.map(
                (agent, index) => {
                  "use strict";
                  if (this.props.me.get('id') !== agent.get('id')) {
                    return <AgentsListItem
                      handleClickParticipant={this.props.handleClickParticipant}
                      key={index}
                      agent={agent}
                      highlight={this.state.value}/>
                  }

                }
              )
                : this.props.agents.map(
                (agent, index) => {
                  "use strict";
                  if (this.props.me.get('id') !== agent.get('id')) {
                    return <AgentsListItem
                      handleClickParticipant={this.props.handleClickParticipant}
                      key={index}
                      agent={agent}
                      highlight={this.state.value}/>
                  }
                }
              )
            }
          </ul>
        </div>
      </div>
    );
  }


  filterAgents(value = '') {
    let newAgents = [];
    if (typeof value == 'string' && value.trim()) {
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


  onChange(event) {
    "use strict";
    const oldState = this.state;
    const newState = {
      ...oldState,
      agents: this.filterAgents(event.target.value),
      value: event.target.value
    };
    this.setState(newState);
  }
}