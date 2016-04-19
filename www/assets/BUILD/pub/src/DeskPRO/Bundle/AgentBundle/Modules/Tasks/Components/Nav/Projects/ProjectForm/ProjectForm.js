import React, { Component, PropTypes } from 'react';
import { createProject, editProject, deleteProject } from '../../../../Actions/navActions';
import Immutable from 'immutable';
import { Popup } from '../../../../../Common/Components/Popup';
import { AssignForm, AssignAgentContainer, AssignTeamContainer, AssignDepartmentContainer } from '../../../../../Common/Components/Form';
import { Notification } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/Notification';
import { connect } from 'react-redux';

@connect()

export class ProjectForm extends Component {

  static propTypes = {
    project:    PropTypes.object,
    tasksCount: PropTypes.number,
    dispatch:   PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    const project = props.project || Immutable.fromJS({});
    const set = Immutable.Set();

    this.state = {
      title:       project.get('title'),
      agents:      project.get('agents', set),
      teams:       project.get('teams', set),
      departments: project.get('departments', set)
    };
  }

  componentWillReceiveProps(props) {
    const { project = Immutable.fromJS({}) } = props;
    const set = Immutable.Set([]);
    this.setState({
      title:       project.get('title'),
      agents:      project.get('agents', set),
      teams:       project.get('teams', set),
      departments: project.get('departments', set)
    });
  }

  shouldComponentUpdate(props, state) {
    const { title, agents, teams, departments } = state;

    return title !== this.state.title
      || !Immutable.is(this.state.agents, agents)
      || !Immutable.is(this.state.teams, teams)
      || !Immutable.is(this.state.departments, departments)
      ;
  }

  onChange = (prop, value) => {
    this.setState({ [prop]: value });
  };

  onSubmit = event => {
    event.preventDefault();
    this.setState({
      submit: true
    });

    const { project, dispatch } = this.props;
    const isNew = !project.get('id');
    const submitData = {
      title:       this.state.title,
      departments: this.state.departments.toArray(),
      teams:       this.state.teams.toArray(),
      agents:      this.state.agents.toArray()
    };

    let promise;
    if (!isNew) {
      promise = dispatch(editProject(project.get('id'), submitData));
    } else {
      promise = dispatch(createProject(submitData));
    }

    promise.then(
      () => {
        if (!this.unmounted) {
          this.setState({submit: false});
        }
      },
      result => {
        if (!this.unmounted) {
          this.setState({
            errors: result.getData().errors,
            submit: false
          });
        }
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
      if (!this.unmounted) {
        this.setState({
          errors: result.getData().errors,
          submit: false
        });
      }
    });
  };

  render() {
    const { project, tasksCount } = this.props;
    const isNew = !project.get('id');
    const { title, agents, teams, departments } = this.state;

    return (
      <Popup additionalClassNames="m-100">
        <div className="dpw--popup-header">
          <i className="fa fa-tags" />
          Project - {isNew ? 'Create New' : 'Edit'}
        </div>

        <div className="dpw--popup-content">

          <div className="dpw--popup-content-line">
            <div className="dpw--popup-content-full">
              <h2 className="dpw--popup-item-section-title">
                Project Title
              </h2>
              <div className="dpw--popup-form-container">
                <input type="text" placeholder="Example Project" value={title}
                  onChange={(value) => this.onChange('title', value)}
                  />
              </div>
            </div>
          </div>

          <AssignForm title="Project Permissions">
            <AssignAgentContainer selected={agents.toSet()}
              onChange={(value) => this.onChange('agents', value)}
              />

            <AssignTeamContainer selected={teams.toSet()}
              onChange={(value) => this.onChange('teams', value)}
              />

            <AssignDepartmentContainer selected={departments.toSet()}
              onChange={(value) => this.onChange('departments', value)}
              />
          </AssignForm>

          <div className="dpw--popup-content-line">
            <div className="dpw--popup-content-left">
              <a href="#" className="dpw--popup-button" onClick={this.onSubmit} style={{ minWidth: 175 }}>
                {isNew ? 'Save new project' : 'Update project'}
              </a>
              {!isNew ?
                <a href="#" className="dpw--popup-button" onClick={this.onDeletePrompt}
                  style={{ minWidth: 175, background: '#ff5460' }}
                  >
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
