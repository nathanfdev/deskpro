import React, { PropTypes } from 'react';
import { createProject, editProject } from '../../../../Actions/navActions';
import { FieldErrors } from 'DeskPRO/Component/Form/FormErrors';
import Immutable from 'immutable';
import Loader from 'react-loader';
import classNames from 'classnames';
import {
  BaseForm,
  Header,
  Popup,
  FieldGroup,
  FullField,
  FloatField,
  CollectionField,
  QuickFilter,
  ShowOnlySelected,
  Unassign,
  AgentsList,
  AgentTeamsList,
  DepartmentsList
} from '../../../Form';
import { Modal } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/Modal';

export class ProjectForm extends BaseForm {

  static propTypes = {
    project: PropTypes.object,
    tasksCount: PropTypes.number,
    dispatch: PropTypes.func.isRequired,
    agents: PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    onSubmit: PropTypes.func
  };

  constructor(props) {
    super(props);

    const localState = this.state;
    const emptyObject = Immutable.fromJS({});
    const project = props.project || emptyObject;

    this.state = {
      ...localState,

      title: project.get('title'),
      showOnlySelected: false,
      agents: project.get('agents', emptyObject).toArray(),
      agentTeams: project.get('teams', emptyObject).toArray(),
      departments: project.get('departments', emptyObject).toArray()
    };
  }

  onChangeTitle = event => {
    this.setState({
      title: event.target.value
    });
  };

  onChangeFilterSelected = value => {
    this.setState({
      showOnlySelected: value
    });
  };

  onSubmit = event => {
    event.preventDefault();
    this.setState({
      submit: true
    });

    const { project, dispatch } = this.props;
    const submitData = {
      title: this.state.title,
      departments: this.state.departments,
      teams: this.state.agentTeams,
      agents: this.state.agents
    };

    let promise;
    if (project) {
      promise = dispatch(editProject(project.get('id'), submitData));
    } else {
      promise = dispatch(createProject(submitData));
    }

    promise.then(
      () => {
        this.setState({submit: false});
        this.props.onSubmit && this.props.onSubmit();
      },
      result => this.setState({
        errors: result.getData().errors,
        submit: false
      })
    );
  };

  onDeletePrompt = () => {
    this.refs.deleteModal.open();
  };

  onDeleteConfirm = () => {

  };

  render() {
    const { agents, agentTeams, departments, project, tasksCount } = this.props;

    return (
      <Popup>
        <Header>
          Project - {project ? 'Edit' : 'Create New'}
        </Header>

        <form>
          <div className="dpw--popup-content">
            <FieldGroup>
              <FullField title="Title">
                <input name="title"
                       type="text"
                       placeholder="Title"
                       value={this.state.title}
                       onChange={this.onChangeTitle} />

                <FieldErrors errors={this.state.errors} name="title" />
              </FullField>
            </FieldGroup>

            <FieldGroup>
              <FloatField align="left">
                <QuickFilter value={this.state.quickFilter}
                             onChange={this.onChangeQuickFilter} />
              </FloatField>

              <FloatField align="right">
                <ShowOnlySelected value={this.state.showOnlySelected}
                                  onChange={this.onChangeFilterSelected} />
                <Unassign onClick={this.onUnassignAll} />
              </FloatField>
            </FieldGroup>

            <FieldGroup>
              <CollectionField>
                <div part="title">
                  Agent <a href="#" onClick={this.onAssignSelf}>Assign to me</a>
                </div>
                <AgentsList multiple
                            values={agents}
                            selected={this.state.agents}
                            showOnlySelected={this.state.showOnlySelected}
                            filter={this.state.quickFilter}
                            onChange={this.onChangeAgents} />
              </CollectionField>

              <CollectionField title="Team">
                <AgentTeamsList multiple
                                values={agentTeams}
                                selected={this.state.agentTeams}
                                showOnlySelected={this.state.showOnlySelected}
                                filter={this.state.quickFilter}
                                onChange={this.onChangeAgentTeams} />
              </CollectionField>

              <CollectionField title="Department">
                <DepartmentsList multiple
                                 values={departments}
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
                {project &&
                <button type="button"
                        className={classNames('dpw--popup-button')}
                        onClick={this.onDeletePrompt}>
                  Delete
                </button>
                }
                {project &&
                <Modal ref="deleteModal"
                       title="Delete project?"
                       onConfirm={this.onDeleteConfirm}
                       confirmTitle="Delete">
                  Are you sure you want to delete "{project.get('title')}"?
                  <br />
                  {tasksCount > 0 && `All (${tasksCount}) tasks will be deleted too!`}
                </Modal>
                }
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
