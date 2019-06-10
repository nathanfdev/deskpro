import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import range from 'lodash/range';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Input, Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import BackButton from '../../../../Common/Components/BackButton';
import ExtensionsHeader from '../ExtensionsHeader';
import NumberTargetList from '../../Common/NumberTarget/NumberTargetList';

const minNumber = 1001;
const maxNumber = 9999;
const rangeNumbers = range(minNumber, maxNumber);

class NewExtensionList extends React.Component {

  static propTypes = {
    agents:          PropTypes.object,
    queues:          PropTypes.object,
    errors:          PropTypes.object,
    loading:         PropTypes.bool,
    onClickBack:     PropTypes.func,
    addExtension:    PropTypes.func,
    addAllSuggested: PropTypes.func
  };

  componentWillReceiveProps(newProps) {
    const { onClickBack } = this.props;

    let hasNewAgents = false;
    newProps.agents.forEach((agent) => {
      if (!this.getExtensionNumber(agent)) {
        hasNewAgents = true;
      }
    });

    // last agent was added, redirect to main page
    if (!hasNewAgents) {
      onClickBack();
    }
  }

  getExtensionNumber = agent => agent.getIn(['agent_data', 'extension_number']);

  addAllSuggested = () => {
    const { addAllSuggested } = this.props;
    const extensions = [];
    Object.keys(this.rows).forEach((agentId) => {
      const extensionNumber = this.rows[agentId].state.formData.value.agent_data.extension_number;
      if (extensionNumber) {
        extensions.push({ agentId, extensionNumber });
      }
    });

    addAllSuggested(extensions);
  };

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
    const { agents = Immutable.fromJS({}), queues, errors, loading } = this.props;
    const { onClickBack, addExtension } = this.props;

    const newAgents = [];
    const existingNumbers = [];

    agents.forEach((agent) => {
      const extensionNumber = this.getExtensionNumber(agent);
      if (extensionNumber) {
        existingNumbers.push(parseInt(extensionNumber, 10));
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

        <button className="ui right floated primary button" onClick={this.addAllSuggested}>
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
                errors={errors[agent.get('id')]}
                extension={availableNumbers.splice(0, 1)[0]}
                addExtension={addExtension}
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
    agent:        PropTypes.object,
    queues:       PropTypes.object,
    addExtension: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value:     this.getDefaultValue(props),
        errorList: {},
        onChange:  this.onChange
      })
    };
  }

  componentWillReceiveProps(newProps) {
    this.setState({
      formData: createValue({
        value:     this.getDefaultValue(newProps),
        errorList: newProps.errors || {},
        onChange:  this.onChange
      })
    });
  }

  onChange = (formData) => {
    this.setState({ formData });
  };

  onSubmit = () => {
    const { agent, addExtension } = this.props;
    const { formData } = this.state;

    addExtension(agent, formData.value.agent_data.extension_number);
  };


  getDefaultValue = props => ({
    agent_data: {
      extension_number: props.extension
    }
  });

  render() {
    const { agent, queues = Immutable.fromJS([]) } = this.props;
    const { formData } = this.state;
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
          <Form onSubmit={this.onSubmit} formValue={formData}>
            <Fieldset>
              <Field select="agent_data">
                <Field select="extension_number">
                  <Input type="number" min={minNumber} max={maxNumber} />
                </Field>
              </Field>
            </Fieldset>
          </Form>
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
