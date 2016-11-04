import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Fieldset, Input, createValue } from 'react-forms';
import { Form, Field, Select, MultiSelect } from 'DeskPRO/Component/Semantic/ReactForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';
import AgentChoiceListWrapper from '../../Common/AgentChoiceListWrapper';
import AccountChoiceWrapper from '../../Common/AccountChoiceWrapper';

const routingModels = [
  { value: 'round_robin', label: 'Round Robin' },
  { value: 'least_utilized', label: 'Least Utilized' },
  { value: 'least_idle', label: 'Least Idle' },
  { value: 'random', label: 'Random' }
];

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
              <Field select="routing_model" label="Routing model">
                <Select clearable={false} choices={routingModels} />
              </Field>
              <Field
                className="queue-size"
                select="max_queue_size"
                label="Maximum queue size"
                help="New calls exceeding this limit will be directed to voicemail."
              >
                <Input type="text" />
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

export default QueueForm;
