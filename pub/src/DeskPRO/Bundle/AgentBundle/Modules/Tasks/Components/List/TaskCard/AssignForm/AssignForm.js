import React, { PropTypes } from 'react';
import { editTask } from '../../../../Actions/listActions';
import classNames from 'classnames';
import Loader from 'react-loader';
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
} from '../../../Form/index';

export class AssignForm extends BaseForm {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    task: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    const localState = this.state;
    const emptyObject = Immutable.fromJS({});
    const task = props.task || emptyObject;

    this.state = {
      ...localState,

      agents: task.get('agents', emptyObject).toArray(),
      agentTeams: task.get('teams', emptyObject).toArray(),
      departments: task.get('departments', emptyObject).toArray()
    };
  }

  onSubmit = event => {
    event.preventDefault();

    const { task, dispatch } = this.props;
    const submitData = {
      agents: this.state.agents,
      teams: this.state.agentTeams,
      departments: this.state.departments
    };

    dispatch(editTask(task.get('id'), submitData));
  };

  render() {
    const { agents, agentTeams, departments } = this.props;

    return (
      <Popup additionalClassNames="assign-form">
        <Header>Assign to Task</Header>

        <form>
          <div className="dpw--popup-content">
            <FieldGroup>
              <FloatField align="left">
                <QuickFilter value={this.state.quickFilter}
                             onChange={this.onChangeQuickFilter} />
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
                            showOnlySelected={this.state.showOnlySelected}
                            filter={this.state.quickFilter}
                            onChange={this.onChangeAgents} />
              </CollectionField>

              <CollectionField title="Team">
                <AgentTeamsList values={agentTeams}
                                selected={this.state.agentTeams}
                                showOnlySelected={this.state.showOnlySelected}
                                filter={this.state.quickFilter}
                                onChange={this.onChangeAgentTeams} />
              </CollectionField>

              <CollectionField title="Department">
                <DepartmentsList values={departments}
                                 selected={this.state.departments}
                                 showOnlySelected={this.state.showOnlySelected}
                                 filter={this.state.quickFilter}
                                 onChange={this.onChangeDepartments} />
              </CollectionField>
            </FieldGroup>

            <FieldGroup>
              <FullField>
                <button type="submit"
                        value="Save"
                        className={classNames('dpw--popup-button', {'hidden': this.state.submit})}
                        onClick={this.onSubmit}>Save</button>
                <Loader opacity={0}
                        width={3}
                        loaded={!this.state.submit} />
              </FullField>
            </FieldGroup>
          </div>
        </form>
      </Popup>
    );
  }
}
