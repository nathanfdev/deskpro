import React, { PropTypes } from 'react';
import classNames from 'classnames';
import _ from 'lodash';
import { Fieldset, Input, createValue } from 'react-forms';
import { Form, Field, Select, MultiSelect } from 'DeskPRO/Component/Semantic/ReactForm';
import AgentChoiceListWrapper from '../../Common/AgentChoiceListWrapper';
import AccountChoiceWrapper from '../../Common/AccountChoiceWrapper';

class QueueForm extends React.Component {

  static propTypes = {
    queue:    PropTypes.object,
    accounts: PropTypes.object,
    agents:   PropTypes.object,
    onSubmit: PropTypes.func.isRequired,
    onDelete: PropTypes.func,
    saving:   PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = this.getDefaultState();
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      formData: createValue({
        value:     this.state.formData.value,
        errorList: nextProps.errors,
        onChange:  this.onChange
      })
    });
  }

  onChange = (formData) => {
    this.setState({ formData });
  };

  onSubmit = (event) => {
    event.preventDefault();
    this.props.onSubmit(this.state.formData.value);
  };

  onCancel = (event) => {
    event.preventDefault();
    this.setState(this.getDefaultState());
  };

  onDelete = (event) => {
    event.preventDefault();
    this.props.onDelete();
  };

  getDefaultState() {
    const { queue, accounts } = this.props;

    let account = null;
    if (queue) {
      account = queue.get('account');
    } else if (accounts && accounts.size === 1) {
      account = accounts.first().get('id');
    }

    return {
      formData: createValue({
        value: {
          account,
          name:           queue ? queue.get('name') : '',
          agents:         queue ? queue.get('agents').toArray() : [],
          routing_model:  queue ? queue.get('routing_model') : 'automatic',
          max_queue_size: queue ? queue.get('max_queue_size') : 0
        },
        errorList: {},
        onChange:  this.onChange
      })
    };
  }

  render() {
    const { queue, accounts, agents, saving } = this.props;
    const { formData } = this.state;

    return (
      <div className="twilio-queue-form">
        <Form onSubmit={this.onSubmit} formValue={formData}>
          <Fieldset>
            {!queue && accounts && accounts.size > 1 &&
              <Field select="account" label="Choose account *">
                <AccountChoiceWrapper accounts={accounts}>
                  <Select clearable={false} />
                </AccountChoiceWrapper>
              </Field>}
            <Field select="name" label="Queue Name">
              <Input type="text" placeholder="Queue Name" />
            </Field>
            {agents && agents.size > 0 &&
              <Field select="agents" label="Agents">
                <AgentChoiceListWrapper agents={agents}>
                  <MultiSelect />
                </AgentChoiceListWrapper>
              </Field>}
            <Field select="routing_model" label="Routing model" className="routing-model">
              <RoutingModel />
            </Field>

            {formData.value.routing_model === 'least_utilized' &&
            <Field select="max_queue_size" className="queue-size">
              <MaxQueueSize />
            </Field>}

            <button className={classNames('ui button', { loading: saving })}>
              {queue ? 'Update' : 'Create'}
            </button>
            <button
              className={classNames('ui basic button cancel-button', { disabled: saving })}
              onClick={this.onCancel}
            >
              Cancel
            </button>

            {queue &&
              <span className="voice-delete-button" onClick={this.onDelete}>
                Delete this queue
              </span>}
          </Fieldset>
        </Form>
      </div>
    );
  }
}

class RoutingModel extends React.Component {

  static propTypes = {
    value:    PropTypes.number,
    onChange: PropTypes.func
  };

  render() {
    const { value, onChange } = this.props;
    const choices = [
      { value: 'automatic', label: 'Automatic', help: 'Call will be routed amongst all agents evenly. The system will automatically balance calls so that no single agent handles more or less than any other agent. For example, given three agents online, if AgentA and AgentB have both accepted a call, then they won\'t recieve another call until AgentC has also accepted a call.' },
      { value: 'least_utilized', label: 'Least Utilized', help: 'Calls will be routed towards agents who have handled the fewest calls.' },
      { value: 'simulring', label: 'Simulring', help: 'Any incoming call will ring ALL agents at the same time. The first to answer wil handle the call.' }
    ];

    const help = {};
    choices.forEach((choice) => {
      help[choice.value] = choice.help;
    });

    return (
      <div>
        <Select
          value={value}
          onChange={onChange}
          clearable={false}
          choices={choices}
        />
        <div className="help">
          {help[value]}
        </div>
      </div>
    );
  }
}

class MaxQueueSize extends React.Component {

  static propTypes = {
    value:    PropTypes.number,
    onChange: PropTypes.func
  };

  render() {
    const { value, onChange } = this.props;
    const choices = _.range(1, 10).map(num => ({
      value: num,
      label: num
    }));

    return (
      <div>
        <span>Ring</span>
        <div className="select-wrapper">
          <Select value={value} onChange={onChange} clearable={false} choices={choices} />
        </div>
        <span>agents at the same time. The first to answer will handle the call.</span>
      </div>
    );
  }
}

export default QueueForm;
