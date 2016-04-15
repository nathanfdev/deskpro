import React, { PropTypes } from 'react';
import { createProject, editProject, deleteProject } from '../../../../Actions/navActions';
import { FieldErrors } from 'DeskPRO/Component/Form/FormErrors';
import Immutable from 'immutable';
import Loader from 'react-loader';
import { FieldGroup, Popup, CollectionField } from '../../../../../Common/Components/Popup';
import { AssignAgentContainer } from '../../../../../Common/Components/Form/AssignAgentContainer';
import {
  BaseForm,
  Header,
  FullField,
  FloatField,
  ShowOnlySelected,
  Unassign
} from '../../../Form';
import { QuickFilter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/QuickFilter';
import { Notification } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/Notification';

export class ProjectForm extends BaseForm {

  static propTypes = {
    project: PropTypes.object,
    tasksCount: PropTypes.number,
    dispatch: PropTypes.func.isRequired
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
      agents: project.get('agents', emptyObject),
      agentTeams: project.get('teams', emptyObject),
      departments: project.get('departments', emptyObject)
    };
  }

  componentWillUnmount() {
    this.unmounted = true;
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
    const isNew = !project.get('id');
    const submitData = {
      title: this.state.title,
      departments: this.state.departments.toArray(),
      teams: this.state.agentTeams.toArray(),
      agents: this.state.agents.toArray()
    };

    let promise;
    if (!isNew) {
      promise = dispatch(editProject(project.get('id'), submitData));
    } else {
      promise = dispatch(createProject(submitData));
    }

    promise.then(
      () => {
        !this.unmounted && this.setState({ submit: false });
      },
        result => {
        !this.unmounted && this.setState({
          errors: result.getData().errors,
          submit: false
        });
      }
    );
  };

  onDeletePrompt = () => {
    this.refs.deleteModal.open();
  };

  onDeleteConfirm = () => {
    this.setState({ submit: true });
    const { project, dispatch } = this.props;
    dispatch(deleteProject(project.get('id'))).catch((result) => {
      !this.unmounted && this.setState({
        errors: result.getData().errors,
        submit: false
      });
    });
  };

  render() {
    const { project, tasksCount } = this.props;
    const isNew = !project.get('id');

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
                       onChange={this.onChangeTitle}/>

                <FieldErrors errors={this.state.errors} name="title"/>
              </FullField>
            </FieldGroup>

            <FieldGroup>
              <FloatField align="left">
                <QuickFilter value={this.state.quickFilter} onChange={this.onChangeQuickFilter}/>
              </FloatField>

              <FloatField align="right">
                <ShowOnlySelected value={this.state.showOnlySelected} onChange={this.onChangeFilterSelected}/>
                <Unassign onClick={this.onUnassignAll}/>
              </FloatField>
            </FieldGroup>

            <FieldGroup>
              <AssignAgentContainer selected={this.state.agents.toSet()}
                                    showOnlySelected={this.state.showOnlySelected}
                                    filter={this.state.quickFilter}
                                    onChange={this.onChange.bind(this, 'agents')} />

            </FieldGroup>

            <FieldGroup>
              <FullField>
                {!this.state.submit && <button type="submit"
                                               value="Save"
                                               className="dpw--popup-button"
                                               onClick={this.onSubmit}>
                  Save
                </button>
                }
                {!isNew && !this.state.submit && <button type="button"
                                                         className="dpw--popup-button"
                                                         onClick={this.onDeletePrompt}>
                  Delete
                </button>
                }
                {!isNew && <Notification ref="deleteModal"
                                  title="Delete this project?"
                                  onConfirm={this.onDeleteConfirm}>
                  Are you sure you want to delete "{project.get('title')}"?
                  <br />
                  {tasksCount > 0 && `All (${tasksCount}) tasks will be deleted too!`}
                </Notification>
                }
                <Loader opacity={0}
                        width={3}
                        loaded={!this.state.submit}/>
              </FullField>
            </FieldGroup>
          </div>
        </form>
      </Popup>
    );
  }
}
