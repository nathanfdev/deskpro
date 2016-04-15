import React, { PropTypes } from 'react';
import { createProject, editProject, deleteProject } from '../../../../Actions/navActions';
import { FieldErrors } from 'DeskPRO/Component/Form/FormErrors';
import Immutable from 'immutable';
import Loader from 'react-loader';
import { Popup, CollectionField } from '../../../../../Common/Components/Popup';
import { AssignAgentContainer, AssignTeamContainer, AssignDepartmentContainer } from '../../../../../Common/Components/Form';
import {
  BaseForm,
  ShowOnlySelected,
  Unassign
} from '../../../Form';
import { QuickFilter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/QuickFilter';
import { Notification } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/Notification';
import { connect } from 'react-redux';

@connect()

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
      teams: project.get('teams', emptyObject),
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
      teams: this.state.teams.toArray(),
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
        <div className="dpw--popup-header">
          <i className="fa fa-tags"/>
          Project - {isNew ? 'Create New' : 'Edit'}
        </div>

        <div className="dpw--popup-content">

          <div className="dpw--popup-content-line">
            <div className="dpw--popup-content-full">
              <h2 className="dpw--popup-item-section-title">
                Project Title
              </h2>
              <div className="dpw--popup-form-container">
                <input type="text" placeholder="Example Project" value={this.state.title}
                       onChange={this.onChangeTitle} />
              </div>
            </div>
          </div>

          <div className="dpw--popup-content-line">
            <div className="dpw--popup-content-left">
              <h2 className="dpw--popup-item-section-title">
                Project Permissions
              </h2>
              <QuickFilter value={this.state.quickFilter} onChange={this.onChangeQuickFilter} />
            </div>

            <div className="dpw--popup-content-right">
              <div className="dpw--popup-content-item">
                <ShowOnlySelected value={this.state.showOnlySelected} onChange={this.onChangeFilterSelected} />
              </div>
              <div className="dpw--popup-content-item">
                <Unassign onClick={this.onUnassignAll} />
              </div>
            </div>
          </div>



          <div className="dpw--popup-content-line">

            <AssignAgentContainer selected={this.state.agents.toSet()}
                                  showOnlySelected={this.state.showOnlySelected}
                                  filter={this.state.quickFilter}
                                  onChange={this.onChange.bind(this, 'agents')} />

            <AssignTeamContainer selected={this.state.teams.toSet()}
                                 showOnlySelected={this.state.showOnlySelected}
                                 filter={this.state.quickFilter}
                                 onChange={this.onChange.bind(this, 'teams')} />

            <AssignDepartmentContainer selected={this.state.departments.toSet()}
                                       showOnlySelected={this.state.showOnlySelected}
                                       filter={this.state.quickFilter}
                                       onChange={this.onChange.bind(this, 'departments')} />
          </div>

          <div className="dpw--popup-content-line">
            <div className="dpw--popup-content-left">
              <a href="#" className="dpw--popup-button" onClick={this.onSubmit} style={{minWidth: 175}}>
                {isNew ? 'Save new project' : 'Update project'}
              </a>
              {!isNew ?
                <a href="#" className="dpw--popup-button" onClick={this.onDeletePrompt}
                   style={{minWidth: 175, background: '#ff5460'}}>
                  Delete project and all tasks
                </a>
              : null}
              {!isNew ?
                <Notification ref="deleteModal" title="Delete this project?" onConfirm={this.onDeleteConfirm}>
                  Are you sure you want to delete "{project.get('title')}"?
                  <br />
                  {tasksCount > 0 && `All (${tasksCount}) tasks will be deleted too!`}
                </Notification>
              : null}
            </div>
          </div>



        </div>

      </Popup>
    );
  }
}
