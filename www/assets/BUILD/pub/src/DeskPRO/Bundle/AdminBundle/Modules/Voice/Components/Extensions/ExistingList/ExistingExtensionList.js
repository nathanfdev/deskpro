import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import ExtensionsHeader from '../ExtensionsHeader';
import NumberTargetList from '../../Common/NumberTarget/NumberTargetList';

class ExistingExtensionList extends React.Component {

  static propTypes = {
    agents:          PropTypes.object,
    queues:          PropTypes.object,
    onAddExtensions: PropTypes.func,
    onEditExtension: PropTypes.func
  };

  renderEmpty() {
    const { onAddExtensions } = this.props;

    return (
      <div className="page">
        <ExtensionsHeader />
        <button className="ui right floated basic button" onClick={onAddExtensions}>
          <i className="icon plus" />
          Add extensions
        </button>

        No agents have an extension number.
      </div>
    );
  }

  renderTable() {
    const { agents = Immutable.fromJS({}), queues, onAddExtensions, onEditExtension } = this.props;
    const existingAgents = agents.filter(agent => agent.getIn(['agent_data', 'extension_number']));

    return (
      <div className="page">
        <ExtensionsHeader />
        <button className="ui right floated basic button" onClick={onAddExtensions}>
          <i className="icon plus" />
          Add extensions
        </button>
        <h3>Existing agent extensions</h3>
        <br />

        <div className="admin-list-table">
          <div className="row header">
            <div className="column agent">Agent</div>
            <div className="column extension">Extension</div>
            <div className="column targets">Queues</div>
          </div>
          {existingAgents.toArray().map((agent, index) =>
            <ExistingExtensionRow
              key={index}
              agent={agent}
              queues={queues}
              onEditExtension={onEditExtension}
            />
          )}
        </div>
      </div>
    );
  }

  render() {
    const { agents } = this.props;
    const existingAgents = agents.filter(agent => agent.getIn(['agent_data', 'extension_number']));

    return existingAgents.size > 0 ? this.renderTable() : this.renderEmpty();
  }
}

class ExistingExtensionRow extends React.Component {

  static propTypes = {
    agent:           PropTypes.object,
    queues:          PropTypes.object,
    onEditExtension: PropTypes.func
  };

  onToggleOptions = (event) => {
    event.preventDefault();

    const { onEditExtension, agent } = this.props;
    onEditExtension(agent);
  };

  render() {
    const { agent, queues = Immutable.fromJS([]) } = this.props;
    const involvedQueues = [];
    queues.filter(queue => queue.get('agents').contains(agent.get('id'))).forEach((queue) => {
      involvedQueues.push({ name: queue.get('name') });
    });

    return (
      <div className="row">
        <div className="info">
          <div className="column agent">
            <PersonAvatar person={agent} size={36} />
            <span className="agent-name">{agent.get('name')}</span>
          </div>
          <div className="column extension">{agent.getIn(['agent_data', 'extension_number'])}</div>
          <div className="column targets">
            <NumberTargetList targets={involvedQueues} displayCount={2} />
          </div>
          <div className="column options-button">
            <a onClick={this.onToggleOptions}>
              <i className="fas fa-cog" />
            </a>
          </div>
        </div>
      </div>
    );
  }
}

export default ExistingExtensionList;
