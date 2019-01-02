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
    returnBack:               PropTypes.func,
    deleteQueue:              PropTypes.func,
    agents:                   PropTypes.object,
    agentTeams:               PropTypes.object,
    agentGroups:              PropTypes.object,
    chatDepartments:          PropTypes.object,
    queues:                   PropTypes.object,
    queueId:                  PropTypes.object,
    loadTeamAgentsList:       PropTypes.func,
    loadDepartmentAgentsList: PropTypes.func,
    loadGroupAgentsList:      PropTypes.func
  };

  getDefaultState() {
    const { queueId, queues } = this.props;
    let isAllAgents = 1;
    let queue = null;
    if (queues && queueId) {
      queue = queues.get(queueId);
      if (queue) {
        isAllAgents = queue.get('is_all_agents') ? 1 : 0;
      }
    }

    return {
      name:           queue ? queue.get('name') : '',
      routing_model:  queue ? queue.get('routing_model') : 'simulring',
      is_all_agents:  isAllAgents,
      answer_timeout: queue ? parseInt(queue.get('answer_timeout'), 10) : 10,
      max_queue_size: queue ? parseInt(queue.get('max_queue_size'), 10) : 1,
      targets:        queue
        ? queue.get('targets')
          .toOrderedMap()
          .sort((a, b) => a.get('sort') - b.get('sort'))
          .toArray()
          .map(target => ({
            type:   target.get('type'),
            target: target.get('target')
          }))
        : []
    };
  }

  transformSubmitData = (data) => {
    const submitData = { ...data };
    submitData.targets.map((target, i) => ({ ...target, sort: i * 10 }));

    return submitData;
  };

  render() {
    const { queueId, queues, returnBack, deleteQueue } = this.props;
    const { agents, agentTeams, agentGroups, chatDepartments } = this.props;
    const { loadTeamAgentsList, loadDepartmentAgentsList, loadGroupAgentsList } = this.props;
    const { formData, saving } = this.state;

    let queue = null;
    if (queues && queueId) {
      queue = queues.get(queueId);
    }

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
                <TargetsList
                  agents={agents}
                  agentTeams={agentTeams}
                  agentGroups={agentGroups}
                  chatDepartments={chatDepartments}
                  loadTeamAgentsList={loadTeamAgentsList}
                  loadDepartmentAgentsList={loadDepartmentAgentsList}
                  loadGroupAgentsList={loadGroupAgentsList}
                  isDraggable={formData.value.routing_model === 'round_robin'}
                />
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
    value:                    PropTypes.array,
    onChange:                 PropTypes.func,
    agents:                   PropTypes.object,
    agentTeams:               PropTypes.object,
    agentGroups:              PropTypes.object,
    chatDepartments:          PropTypes.object,
    loadTeamAgentsList:       PropTypes.func,
    loadDepartmentAgentsList: PropTypes.func,
    loadGroupAgentsList:      PropTypes.func,
    isDraggable:              PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      bulkChoice: null
    };
  }

  setBulkChoice = (bulkChoice) => {
    this.setState({ bulkChoice });
  };

  addBulkAgents = (event) => {
    event.preventDefault();
    event.stopPropagation();

    const { agents, value, onChange, loadTeamAgentsList, loadDepartmentAgentsList, loadGroupAgentsList } = this.props;
    const params = this.state.bulkChoice.split('.');
    const type = params[0];
    const id = params[1];

    let promise;
    if (type === 'team') {
      promise = loadTeamAgentsList(id);
    } else if (type === 'department') {
      promise = loadDepartmentAgentsList(id);
    } else if (type === 'group') {
      promise = loadGroupAgentsList(id);
    }

    if (promise) {
      promise.success(({ data }) => {
        const selectedAgentIds = [];
        value.forEach((target) => {
          if (target.type === 'agent') {
            const agent = agents.get(target.target);
            if (agent) {
              selectedAgentIds.push(target.target);
            }
          }
        });

        data.forEach((agent) => {
          if (selectedAgentIds.indexOf(agent.id) === -1) {
            value.push({ type: 'agent', target: agent.id });
          }
        });

        onChange(value);
      });
    }

    this.setState({
      bulkChoice: null
    });
  };

  render() {
    const { value, onChange, agents, agentTeams, agentGroups, chatDepartments, isDraggable } = this.props;
    const choices = [];
    const addAgentChoice = (agent, sortable) => {
      choices.push({
        sortable,
        value: { type: 'agent', target: agent.get('id') },
        label: (
          <div className="multi-select-label">
            <PersonAvatar person={agent} size={16} />
            <span>{agent.get('name')}</span>
          </div>
        )
      });
    };

    const selectedAgentIds = [];
    value.forEach((target) => {
      if (target.type === 'agent') {
        const agent = agents.get(target.target);
        if (agent) {
          selectedAgentIds.push(target.target);
          addAgentChoice(agent, isDraggable);
        }
      }
    });

    agents.forEach((agent) => {
      if (selectedAgentIds.indexOf(agent.get('id')) === -1) {
        addAgentChoice(agent);
      }
    });

    const bulkChoices = [];
    bulkChoices.push({
      value:    0,
      label:    'Agent Team',
      disabled: true
    });

    agentTeams.forEach((team) => {
      bulkChoices.push({
        value: `team.${team.get('id')}`,
        label: team.get('name')
      });
    });

    bulkChoices.push({
      value:    0,
      label:    'Departments',
      disabled: true
    });

    chatDepartments.forEach((departemnt) => {
      bulkChoices.push({
        value: `department.${departemnt.get('id')}`,
        label: departemnt.get('title')
      });
    });

    bulkChoices.push({
      value:    0,
      label:    'Permission Groups',
      disabled: true
    });

    agentGroups.forEach((group) => {
      bulkChoices.push({
        value: `group.${group.get('id')}`,
        label: group.get('title')
      });
    });

    return (
      <div>
        <MultiSelect
          {...this.props}
          value={value}
          choices={choices}
          onChange={onChange}
        />

        <div className="bulk-add-agents">
          <span className="help-block">
            Bulk add agents that are members of teams, departments or permission groups:
          </span>
          <Select
            choices={bulkChoices}
            value={this.state.bulkChoice}
            onChange={this.setBulkChoice}
            clearable={false}
          />
          <button className="ui basic button" onClick={this.addBulkAgents}>
            Add
          </button>
        </div>
      </div>
    );
  }
}

export default QueueForm;
