import React, { PropTypes } from 'react';
import { Fieldset, Input, createValue } from 'react-forms';
import { Form, Field, Select, MultiSelect } from 'DeskPRO/Component/Semantic/ReactForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';
import { AgentChoiceListWrapper } from '../../../../Application/Components/AgentsContainer';

const routingModels = [
  { value: 'round_robin', label: 'Round Robin' },
  { value: 'least_utilized', label: 'Least Utilized' },
  { value: 'least_idle', label: 'Least Idle' },
  { value: 'random', label: 'Random' }
];

class QueueForm extends React.Component {

  static propTypes = {
    queue:        PropTypes.object,
    agents:       PropTypes.array,
    agentsLoaded: PropTypes.bool,
    onSubmit:     PropTypes.func.isRequired,
    onReturnBack: PropTypes.func.isRequired
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

  getDefaultState() {
    const queue = this.props.queue;

    return {
      formData: createValue({
        value: {
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
    const { queue, agents, agentsLoaded, onReturnBack } = this.props;

    return (
      <div className="page">
        <BackButton onClick={onReturnBack} />
        <SectionHeader title={queue ? 'Update queue' : 'Create new queue'} dividing />

        <div className="twilio-queue-form">
          <Form onSubmit={this.onSubmit} formValue={this.state.formData}>
            <Fieldset>
              <Field select="name" label="Queue Name">
                <Input type="text" placeholder="Queue Name" />
              </Field>
              <Field select="agents">
                <AgentChoiceListWrapper agents={agents}>
                  <MultiSelect loaded={agentsLoaded} />
                </AgentChoiceListWrapper>
              </Field>
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

              <input
                type="submit"
                className="ui button"
                value={queue ? 'Update' : 'Create'}
              />
              <input
                type="submit"
                className="ui basic button cancel-button"
                value="Cancel"
                onClick={this.onCancel}
              />
            </Fieldset>
          </Form>
        </div>
      </div>
    );
  }
}

export default QueueForm;
