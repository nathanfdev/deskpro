import PropTypes from 'prop-types';
import React, { Component } from 'react';
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
    dispatch:   PropTypes.func.isRequired,
    onSubmit:   PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    const project = props.project || Immutable.Map();
    const list = Immutable.List();

    this.state = {
      title:       project.get('title'),
      agents:      project.get('agents', list),
      teams:       project.get('teams', list),
      departments: project.get('departments', list)
    };
  }

  componentWillReceiveProps(props) {
    const { project = Immutable.Map() } = props;
    const list = Immutable.List();
    this.setState({
      title:       project.get('title'),
      agents:      project.get('agents', list),
      teams:       project.get('teams', list),
      departments: project.get('departments', list)
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

    const { project, dispatch } = this.props;
    const isNew = !project.get('id');
    const { title, departments, teams, agents } = this.state;

    const submitData = {
      title,
      departments: departments.toArray(),
      teams:       teams.toArray(),
      agents:      agents.toArray()
    };

    if (isNew) {
      dispatch(createProject(submitData));
      this.props.onSubmit();
    } else {
      dispatch(editProject(project.get('id'), submitData));
      this.props.onSubmit(project.merge(Immutable.Map({ title, departments, teams, agents })));
    }
  };

  onDeletePrompt = () => {
    this.refs.deleteModal.open();
  };

  onDeleteConfirm = () => {
    const { project, dispatch } = this.props;
    dispatch(deleteProject(project.get('id')));
    if (this.props.onSubmit) {
      this.props.onSubmit();
    }
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
                  onChange={(event) => this.onChange('title', event.target.value)}
                />
              </div>
            </div>
          </div>

          <AssignForm title="Project Permissions">
            <AssignAgentContainer selected={agents}
              onChange={(value) => this.onChange('agents', value)}
            />

            <AssignTeamContainer selected={teams}
              onChange={(value) => this.onChange('teams', value)}
            />

            <AssignDepartmentContainer selected={departments}
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
