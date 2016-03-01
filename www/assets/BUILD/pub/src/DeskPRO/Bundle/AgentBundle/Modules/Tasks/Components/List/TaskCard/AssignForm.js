import React, { PropTypes } from 'react';
import { editTask } from '../../../Actions/listActions';
import classNames from 'classnames';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import Immutable from 'immutable';
import {
  BaseForm,
  Header,
  Popup,
  FieldGroup,
  FullField,
  FloatField,
  CollectionField,
  QuickFilter,
  Unassign,
  AgentsList,
  AgentTeamsList,
  DepartmentsList
} from '../../Form';

export class AssignForm extends BaseForm {
  static propTypes = {
    task: PropTypes.object.isRequired,
    onSubmit: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    const localState = this.state;
    const emptyObject = Immutable.fromJS({});
    const task = props.task;

    this.state = {
      ...localState,

      agents: task.get('agents', emptyObject).toArray(),
      agentTeams: task.get('teams', emptyObject).toArray(),
      departments: task.get('departments', emptyObject).toArray()
    };
  }

  componentWillReceiveProps(nextProps) {
    const task = nextProps.task;
    const emptyObject = Immutable.fromJS({});

    this.setState({
      agents: task.get('agents', emptyObject).toArray(),
      agentTeams: task.get('teams', emptyObject).toArray(),
      departments: task.get('departments', emptyObject).toArray()
    });
  }

  onSubmit = event => {
    event.preventDefault();

    const submitData = Immutable.fromJS({
      agents: this.state.agents,
      teams: this.state.agentTeams,
      departments: this.state.departments
    });

    this.props.onSubmit(submitData);
  };

  onChange(prop, value) {
    this.setState({
      [prop]: value
    });
  }

  render() {
    const { agents, agentTeams, departments, task } = this.props;

    return (
      <Popup additionalClassNames="assign-form">
        <Header>Assign to Task</Header>

        <form>
          <div className="dpw--popup-content">
            <FieldGroup>
              <FloatField align="left">
                <QuickFilter value={this.state.quickFilter} onChange={this.onChangeQuickFilter} />
              </FloatField>

              <FloatField align="right">
                <Unassign onClick={this.onUnassignAll} />
              </FloatField>
            </FieldGroup>

            <FieldGroup>
              <CollectionField>
                <div part="title">
                  Agent <a href="#" onClick={this.onAssignSelf}>Assign to me</a>
                </div>
                <AgentsList values={agents}
                            selected={this.state.agents}
                            filter={this.state.quickFilter}
                            onChange={this.onChange.bind(this, 'agents')} />
              </CollectionField>

              <CollectionField title="Team">
                <AgentTeamsList values={agentTeams}
                                selected={this.state.teams}
                                filter={this.state.quickFilter}
                                onChange={this.onChange.bind(this, 'teams')} />
              </CollectionField>

              <CollectionField title="Department">
                <DepartmentsList values={departments}
                                 selected={this.state.departments}
                                 filter={this.state.quickFilter}
                                 onChange={this.onChange.bind(this, 'departments')} />
              </CollectionField>
            </FieldGroup>

            <FieldGroup>
              <FullField>
                <button type="submit"
                        value="Save"
                        className="dpw--popup-button"
                        onClick={this.onSubmit}>
                  {task.get('id') ? 'Save' : 'Ok'}
                </button>
              </FullField>
            </FieldGroup>
          </div>
        </form>
      </Popup>
    );
  }
}
