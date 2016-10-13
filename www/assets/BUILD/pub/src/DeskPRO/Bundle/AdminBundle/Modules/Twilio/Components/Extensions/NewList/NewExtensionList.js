import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import _ from 'lodash';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import BackButton from '../../../../Common/Components/BackButton';
import ExtensionsHeader from '../ExtensionsHeader';
import NumberTargetList from '../../Common/NumberTarget/NumberTargetList';

const minNumber = 1001;
const maxNumber = 9999;
const rangeNumbers = _.range(minNumber, maxNumber);

class NewExtensionList extends React.Component {

  static propTypes = {
    agents:            PropTypes.object,
    queues:            PropTypes.object,
    loading:           PropTypes.bool,
    onClickBack:       PropTypes.func,
    onAddExtension:    PropTypes.func,
    onAddAllSuggested: PropTypes.func
  };

  onAddAllSuggested = () => {
    const { onAddAllSuggested } = this.props;
    const extensions = [];
    Object.keys(this.rows).forEach((agentId) => {
      const extensionNumber = this.rows[agentId].state.extension;
      if (extensionNumber) {
        extensions.push({ agentId, extensionNumber });
      }
    });

    onAddAllSuggested(extensions);
  };

  getExtensionNumber = agent => agent.getIn(['agent_data', 'extension_number']);

  renderEmpty() {
    const { onClickBack } = this.props;

    return (
      <div className="page">
        <BackButton onClick={onClickBack} />
        <ExtensionsHeader />

        All agents have an extension number.
      </div>
    );
  }

  renderTable() {
    const { agents = Immutable.fromJS({}), queues, loading } = this.props;
    const { onClickBack, onAddExtension } = this.props;

    const newAgents = [];
    const existingNumbers = [];

    agents.forEach((agent) => {
      const extensionNumber = this.getExtensionNumber(agent);
      if (extensionNumber) {
        existingNumbers.push(extensionNumber);
      } else {
        newAgents.push(agent);
      }
    });

    const availableNumbers = rangeNumbers.filter(number => existingNumbers.indexOf(number) === -1);
    this.rows = {};

    return (
      <div className="page">
        {loading &&
        <div className="ui active inverted dimmer">
          <div className="ui text loader">Loading</div>
        </div>}

        <BackButton onClick={onClickBack} />
        <ExtensionsHeader />

        <button className="ui right floated primary button" onClick={this.onAddAllSuggested}>
          Add all suggested
        </button>
        <h3>Add some extensions for your team members:</h3>
        <br />

        <table className="twilio-extensions-table">
          <thead>
            <tr>
              <th className="agent">Agent</th>
              <th>Queues</th>
              <th className="extension">Extension</th>
              <th />
            </tr>
          </thead>
          <tbody>
            {newAgents.map((agent, index) =>
              <NewExtensionRow
                ref={(c) => { this.rows[agent.get('id')] = c; }}
                key={index}
                agent={agent}
                queues={queues}
                extension={availableNumbers.splice(0, 1)[0]}
                onAddExtension={onAddExtension}
              />
            )}
          </tbody>
        </table>
      </div>
    );
  }

  render() {
    const { agents } = this.props;
    const newAgents = agents.filter(agent => !this.getExtensionNumber(agent));

    return newAgents.size > 0 ? this.renderTable() : this.renderEmpty();
  }
}

class NewExtensionRow extends React.Component {

  static propTypes = {
    agent:          PropTypes.object,
    queues:         PropTypes.object,
    extension:      PropTypes.number,
    onAddExtension: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      extension: props.extension
    };
  }

  componentWillReceiveProps(newProps) {
    this.setState({
      extension: newProps.extension
    });
  }

  onChangeExtension = (event) => {
    this.setState({
      extension: event.target.value
    });
  };

  onSubmit = () => {
    const { agent, onAddExtension } = this.props;

    onAddExtension(agent, this.state.extension);
  };

  render() {
    const { agent, queues = Immutable.fromJS([]) } = this.props;
    const { extension } = this.state;

    const involvedQueues = [];
    queues.filter(queue => queue.get('agents').contains(agent.get('id'))).forEach((queue) => {
      involvedQueues.push({ name: queue.get('name') });
    });

    return (
      <tr>
        <td className="agent">
          <PersonAvatar person={agent} size={36} />
          <span className="agent-name">{agent.get('name')}</span>
        </td>
        <td className="queue">
          <NumberTargetList targets={involvedQueues} displayCount={2} />
        </td>
        <td className="extension">
          <form className="ui form">
            <input type="number" min={minNumber} max={maxNumber} value={extension} onChange={this.onChangeExtension} />
          </form>
        </td>
        <td className="add-button">
          <button className="ui right floated basic button" onClick={this.onSubmit}>
            Add extension
          </button>
        </td>
      </tr>
    );
  }
}

export default NewExtensionList;
