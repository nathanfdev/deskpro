import PropTypes from 'prop-types';
import React from 'react';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Fieldset } from '@deskpro/react-forms';
import { Input, Form, Field, Select, Radio, MultiSelect } from 'DeskPRO/Component/Semantic/ReactForm';
import classNames from 'classnames';
import range from 'lodash/range';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';
import BackButton from '../../../../Common/Components/BackButton';
import SectionHeader from '../../../../Common/Components/SectionHeader';


class QueueForm extends BaseForm {

  static propTypes = {
    returnBack:  PropTypes.func,
    deleteQueue: PropTypes.func,
    queue:       PropTypes.object,
    agents:      PropTypes.object,
    agentTeams:  PropTypes.object
  };

  getDefaultState() {
    const { queue } = this.props;
    let isAllAgents = 1;
    if (queue) {
      isAllAgents = queue.get('is_all_agents') ? 1 : 0;
    }

    return {
      name:           queue ? queue.get('name') : '',
      routing_model:  queue ? queue.get('routing_model') : 'simulring',
      is_all_agents:  isAllAgents,
      answer_timeout: queue ? parseInt(queue.get('answer_timeout'), 10) : 10,
      max_queue_size: queue ? parseInt(queue.get('max_queue_size'), 10) : 1,
      targets:        queue ? queue.get('targets').toArray().map(target => ({
        type:   target.get('type'),
        target: target.get('target')
      })) : []
    };
  }

  transformSubmitData = (data) => {
    const submitData = { ...data };
    submitData.targets.map((target, i) => ({ ...target, sort: i * 10 }));

    return submitData;
  };

  render() {
    const { queue, agents, agentTeams, returnBack, deleteQueue } = this.props;
    const { formData, saving } = this.state;

    return (
      <div className="page user-chat">
        <BackButton onClick={returnBack} />
        <SectionHeader title={queue ? 'Update queue' : 'Create new queue'} dividing />

        <div className="page-form">
          <Form onSubmit={this.onSubmit} formValue={formData}>
            <Fieldset>
              <Field select="name" label="Queue Name *">
                <Input type="text" />
              </Field>
              <Field select="routing_model" label="Routing model" className="routing-model">
                <RoutingModel />
              </Field>
              {formData.value.routing_model === 'least_utilized' &&
              <Field select="max_queue_size" className="queue-size">
                <MaxQueueSize />
              </Field>}
              <Field select="answer_timeout" label="Answer Timeout">
                <AnswerTimeout />
              </Field>
              <Field select="is_all_agents" label="Agents">
                <AllAgents />
              </Field>
              {!formData.value.is_all_agents &&
              <Field select="targets">
                <TargetsList agents={agents} agentTeams={agentTeams} />
              </Field>}

              <button className={classNames('ui button', { loading: saving })}>
                {queue ? 'Update' : 'Create'}
              </button>
              {queue &&
              <span className="voice-delete-button" onClick={deleteQueue}>
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
    value:    PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
    onChange: PropTypes.func
  };

  render() {
    const { value, onChange } = this.props;
    const choices = [
      { value: 'simulring', label: 'Simulring', help: '' },
      { value: 'least_utilized', label: 'Least Utilized', help: '' },
      { value: 'round_robin', label: 'Round Robin', help: '' }
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
        <span>Notify</span>
        <div className="select-wrapper">
          <Select value={value} onChange={onChange} clearable={false} choices={choices} />
        </div>
        <span>agents at the same time. The first to answer will handle the chat.</span>
      </div>
    );
  }
}

class AnswerTimeout extends React.Component {

  render() {
    return (
      <div className="answer-timeout">
        <span>Agents have at most</span>
        <Input {...this.props} type="number" />
        <span>seconds before the chat gets re-routed</span>
      </div>
    );
  }
}

class AllAgents extends React.Component {

  static propTypes = {
    value:    PropTypes.array,
    onChange: PropTypes.func,
    disabled: PropTypes.bool
  };

  render() {
    const { value, disabled, onChange } = this.props;

    return (
      <div className="inline fields">
        <div className="field">
          <Radio
            name="is_all_agents"
            label="All Agents"
            choice={1}
            value={value}
            disabled={disabled}
            onChange={onChange}
          />
        </div>
        <div className="field">
          <Radio
            name="is_all_agents"
            label="Specify Agents & Teams"
            choice={0}
            value={value}
            disabled={disabled}
            onChange={onChange}
          />
        </div>
      </div>
    );
  }
}

class TargetsList extends React.Component {

  static propTypes = {
    value:      PropTypes.array,
    onChange:   PropTypes.func,
    agents:     PropTypes.object,
    agentTeams: PropTypes.object
  };

  render() {
    const { value, onChange, agents, agentTeams } = this.props;
    const choices = [];
    agents.forEach((agent) => {
      choices.push({
        value: { type: 'agent', target: agent.get('id') },
        label: (
          <div className="multi-select-label">
            <PersonAvatar person={agent} size={16} />
            <span>{agent.get('name')}</span>
          </div>
        )
      });
    });

    agentTeams.forEach((team) => {
      choices.push({
        value: { type: 'agent_team', target: team.get('id') },
        label: (
          <div className="multi-select-label">
            <PersonAvatar person={team} size={16} />
            <span>{team.get('name')}</span>
          </div>
        )
      });
    });

    return (
      <MultiSelect
        {...this.props}
        value={value}
        choices={choices}
        onChange={onChange}
      />
    );
  }
}

export default QueueForm;
