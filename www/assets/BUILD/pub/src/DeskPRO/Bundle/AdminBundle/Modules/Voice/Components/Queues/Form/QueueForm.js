import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Fieldset, Input, createValue } from 'react-forms';
import { Form, Field, Select, MultiSelect, Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';
import AgentChoiceListWrapper from '../../Common/AgentChoiceListWrapper';
import AccountChoiceWrapper from '../../Common/AccountChoiceWrapper';

class QueueForm extends React.Component {

  static propTypes = {
    queue:        PropTypes.object,
    accounts:     PropTypes.object,
    agents:       PropTypes.object,
    onSubmit:     PropTypes.func.isRequired,
    onReturnBack: PropTypes.func.isRequired,
    onDelete:     PropTypes.func,
    saving:       PropTypes.bool
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
          routing_model:  queue ? queue.get('routing_model') : 'round_robin',
          max_queue_size: queue ? queue.get('max_queue_size') : 0
        },
        errorList: {},
        onChange:  this.onChange
      })
    };
  }

  render() {
    const { queue, accounts, agents, onReturnBack, saving } = this.props;

    return (
      <div className="page">
        <BackButton onClick={onReturnBack} />
        <SectionHeader title={queue ? 'Update queue' : 'Create new queue'} dividing />

        <div className="twilio-queue-form">
          <Form onSubmit={this.onSubmit} formValue={this.state.formData}>
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
                <Field select="agents">
                  <AgentChoiceListWrapper agents={agents}>
                    <MultiSelect />
                  </AgentChoiceListWrapper>
                </Field>}
              <Field select="routing_model" label="Routing model" className="routing-model">
                <RoutingModel />
              </Field>
              <Field select="max_queue_size" className="queue-size">
                <MaxQueueSize />
              </Field>

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
      { value: 'round_robin', label: 'Round Robin', help: 'Assign a phone call to agent by "Round Robin".' },
      { value: 'least_utilized', label: 'Least Utilized', help: 'Assign a phone call to least utilized agent.' },
      { value: 'least_idle', label: 'Least Idle', help: 'Assign a phone call to least idle agent.' },
      { value: 'random', label: 'Random', help: 'Assign a phone call to random agent.' }
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

  onToggleExpand = () => {
    const { value, onChange } = this.props;
    onChange(value > 0 ? 0 : 1);
  };

  render() {
    const { value, onChange } = this.props;
    const expanded = value > 0;

    return (
      <div>
        <Checkbox label="Enable a max queue size" value={expanded} onChange={this.onToggleExpand} />
        {expanded && <Input type="text" value={value} onChange={onChange} />}
      </div>
    );
  }
}

export default QueueForm;
