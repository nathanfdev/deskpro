import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import classNames from 'classnames';
import range from 'lodash/range';
import { Fieldset } from 'react-forms';
import { Input, Form, Field, Select, MultiSelect, Checkbox, RecordsChoiceWrapper } from 'DeskPRO/Component/Semantic/ReactForm';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import AccountChoiceWrapper from '../../Common/AccountChoiceWrapper';
import AudioWidgetFormContainer from '../../Common/AudioWidgetFormContainer';
import AgentsSelectContainer from '../../../../Common/Components/Select/AgentsSelectContainer';

class QueueForm extends BaseForm {

  static propTypes = {
    queueId:           PropTypes.number,
    queues:            PropTypes.object,
    accounts:          PropTypes.object,
    agents:            PropTypes.object,
    agentTeams:        PropTypes.object,
    ticketDepartments: PropTypes.object,
    onSubmit:          PropTypes.func.isRequired,
    onDelete:          PropTypes.func,
    onCancel:          PropTypes.func
  };

  onCancel = (event) => {
    event.preventDefault();
    this.props.onCancel();
  };

  onDelete = (event) => {
    event.preventDefault();
    this.props.onDelete();
  };

  getDefaultState() {
    const { queueId, queues, accounts } = this.props;

    let queue;
    if (queueId) {
      queue = queues.get(queueId);
    }

    const greetAsset = queue && queue.get('greet_asset');
    const loopAsset = queue && queue.get('loop_asset');
    const voicemailAsset = queue && queue.get('voicemail_asset');

    let account = null;
    if (queue) {
      account = queue.get('account');
    } else if (accounts && accounts.size === 1) {
      account = accounts.first().get('id');
    }

    return {
      account,
      name:                 queue ? queue.get('name') : '',
      agents:               queue ? queue.get('agents').toArray().map(voiceAgent => voiceAgent.toJS()) : [],
      routing_model:        queue ? queue.get('routing_model') : 'automatic',
      max_queue_size:       queue ? queue.get('max_queue_size') : 0,
      greet_asset:          greetAsset ? greetAsset.toJS() : null,
      loop_asset:           loopAsset ? loopAsset.toJS() : null,
      voicemail_asset:      voicemailAsset ? voicemailAsset.toJS() : null,
      voicemail_department: queue ? queue.get('voicemail_department') : null,
      voicemail_agent:      queue ? queue.get('voicemail_agent') : null,
      voicemail_agent_team: queue ? queue.get('voicemail_agent_team') : null,
      voicemail_timeout:    queue ? queue.get('voicemail_timeout') : 30
    };
  }

  render() {
    const { queueId, accounts, agents, agentTeams, ticketDepartments, onCancel } = this.props;
    const { formData, saving } = this.state;

    return (
      <div className="twilio-queue-form">
        <Form onSubmit={this.onSubmit} formValue={formData}>
          <Fieldset>
            {!queueId && accounts && accounts.size > 1 &&
              <Field select="account" label="Choose account *">
                <AccountChoiceWrapper accounts={accounts}>
                  <Select clearable={false} />
                </AccountChoiceWrapper>
              </Field>}
            <Field select="name" label="Queue Name *">
              <Input type="text" />
            </Field>
            {agents && agents.size > 0 &&
              <Field select="agents" label="Agents">
                <VoiceAgentChoiceList agents={agents} />
              </Field>}
            <Field select="routing_model" label="Routing model" className="routing-model">
              <RoutingModel />
            </Field>

            {formData.value.routing_model === 'least_utilized' &&
            <Field select="max_queue_size" className="queue-size">
              <MaxQueueSize />
            </Field>}
            <Field select="greet_asset" className="audio-asset" label="Greet">
              <AudioWidgetFormContainer />
            </Field>
            <Field select="loop_asset" className="audio-asset" label="Holding Music">
              <AudioWidgetFormContainer />
            </Field>
            <Field select="voicemail_asset" className="audio-asset" label="Voicemail">
              <AudioWidgetFormContainer />
            </Field>

            <Field select="voicemail_timeout" className="voice-voicemail-timeout" label="Voicemail timeout *">
              <Input type="number" />
            </Field>

            <div>
              Missed calls will ask the user to leave a message and a new voice ticket will be created with the following properties:

              <Field select="voicemail_department">
                <VoicemailDepartmentProperty ticketDepartments={ticketDepartments} />
              </Field>
              <Field select="voicemail_agent">
                <VoicemailAgentProperty agents={agents} />
              </Field>
              {agentTeams.size > 0 &&
              <Field select="voicemail_agent_team">
                <VoicemailAgentTeamProperty agentTeams={agentTeams} />
              </Field>}
            </div>

            <button className={classNames('ui button', { loading: saving })}>
              {queueId ? 'Update' : 'Create'}
            </button>
            {onCancel &&
            <button
              className={classNames('ui basic button cancel-button', { disabled: saving })}
              onClick={this.onCancel}
            >
              Cancel
            </button>}

            {queueId &&
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
    value:    PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
    onChange: PropTypes.func
  };

  render() {
    const { value, onChange } = this.props;
    const choices = [
      { value: 'automatic', label: 'Automatic', help: 'Call will be routed amongst all agents evenly. The system will automatically balance calls so that no single agent handles more or less than any other agent. For example, given three agents online, if AgentA and AgentB have both accepted a call, then they won\'t receive another call until AgentC has also accepted a call.' },
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
    const choices = range(1, 10).map(num => ({
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

class VoicemailDepartmentProperty extends React.Component {

  static propTypes = {
    ticketDepartments: PropTypes.object
  };

  render() {
    const { ticketDepartments } = this.props;
    const defaultValue = ticketDepartments && ticketDepartments.size ? ticketDepartments.first().get('id') : null;

    return (
      <VoicemailProperty{...this.props} label="Set Department" defaultValue={defaultValue}>
        <RecordsChoiceWrapper records={ticketDepartments} labelProp="title">
          <Select {...this.props} clearable={false} />
        </RecordsChoiceWrapper>
      </VoicemailProperty>
    );
  }
}

class VoicemailAgentProperty extends React.Component {

  static propTypes = {
    agents: PropTypes.object
  };

  render() {
    const { agents } = this.props;
    const defaultValue = agents && agents.size ? agents.first().get('id') : null;

    return (
      <VoicemailProperty{...this.props} label="Assign Agent" defaultValue={defaultValue}>
        <AgentsSelectContainer />
      </VoicemailProperty>
    );
  }
}

class VoicemailAgentTeamProperty extends React.Component {

  static propTypes = {
    agentTeams: PropTypes.object
  };

  render() {
    const { agentTeams } = this.props;
    const defaultValue = agentTeams && agentTeams.size ? agentTeams.first().get('id') : null;

    return (
      <VoicemailProperty{...this.props} label="Assign Team" defaultValue={defaultValue}>
        <RecordsChoiceWrapper records={agentTeams}>
          <Select {...this.props} clearable={false} />
        </RecordsChoiceWrapper>
      </VoicemailProperty>
    );
  }
}

class VoicemailProperty extends React.Component {

  static propTypes = {
    label:        PropTypes.string,
    value:        PropTypes.number,
    onChange:     PropTypes.func,
    children:     PropTypes.node,
    defaultValue: PropTypes.number
  };

  constructor(props) {
    super(props);
    this.state = {
      expanded: !!props.value
    };
  }

  onToggleExpanded = () => {
    const { onChange, defaultValue } = this.props;
    const expanded = !this.state.expanded;

    this.setState({ expanded });
    onChange(expanded ? defaultValue : null);
  };

  render() {
    const { expanded } = this.state;
    const { label, value, onChange, children } = this.props;

    return (
      <div className="voice-voicemail-property">
        <Checkbox label={label} value={expanded} onChange={this.onToggleExpanded} />
        {expanded && React.cloneElement(children, { ...children.props, value, onChange })}
      </div>
    );
  }
}

class VoiceAgentChoiceList extends React.Component {

  static propTypes = {
    value:    PropTypes.array,
    onChange: PropTypes.func,
    agents:   PropTypes.object
  };

  onChange = (data) => {
    const { value, onChange } = this.props;
    const oldAgentIds = value.map(voiceAgent => voiceAgent.agent);
    const newValue = value.filter(voiceAgent => data.indexOf(voiceAgent.agent) !== -1);

    data.forEach((agentId) => {
      if (oldAgentIds.indexOf(agentId) === -1) {
        newValue.push(({ agent: agentId, is_enabled: true }));
      }
    });

    onChange(newValue);
  };

  render() {
    const { value, agents = Immutable.fromJS([]) } = this.props;
    const agentIds = value.map(voiceAgent => voiceAgent.agent);
    const choices = agents.map(agent => ({
      value: agent.get('id'),
      label: (
        <div className="multi-select-label">
          <PersonAvatar person={agent} size={16} />
          <span className={classNames({ disabled: !agent.getIn(['agent_data', 'is_voice_enabled']) })}>
            {agent.get('name')}
          </span>
        </div>
      )
    })).toArray();

    return (
      <MultiSelect
        {...this.props}
        value={agentIds}
        choices={choices}
        onChange={this.onChange}
      />
    );
  }
}

export default QueueForm;
