import React from 'react';
import ProjectCreateHover from '../Components/ProjectCreateHover';
import TaskNavItemProject from '../Components/TaskNavItemProject';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { connect } from 'react-redux';
import jQuery from 'jquery';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';

import Immutable from 'immutable';

import * as TaskActions from '../Actions/TaskListActions';

import { updateProject, loadAllProjects } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Actions/projectActions';

@connect(state => ({
  failedProject: state.OldTasks.failedProject
}))
export default class TasksNavProjects extends React.Component {
  static propTypes = {
    projectList: React.PropTypes.object,
    agentList: React.PropTypes.object,
    labelList: React.PropTypes.object,
    departmentList: React.PropTypes.object,
    teamList: React.PropTypes.object,
    createdProject: React.PropTypes.object,
    dpWindow: React.PropTypes.object,
    failedProject: React.PropTypes.object,
    user: React.PropTypes.object,
    dispatch: React.PropTypes.func
  }

  constructor(props) {
    super(props);

    this.state = {
      showWindow: false,
      projectData: Immutable.Map(),
      position: {}
    };
  }

  toggleWindow(project = false, event) {
    this.setState({
      projectData: Immutable.Map()
    });

    const projectData = project ? project : Immutable.Map();

    if (this.state.showWindow === false) {
      this.setState({
        projectData: projectData
      });
    }

    let target = jQuery(event.target).closest('li.project-list-item');
    let modifier = 12;

    if (typeof target[0] === 'undefined') {
      target = jQuery(event.target).closest('.list-sidebar-title');
      modifier = 13;
    }

    this.setState({
      showWindow: !this.state.showWindow,
      position: {
        x: event.clientX,
        y: target[0].getBoundingClientRect().top + modifier
      }
    });
  }

  closeWindow() {
    this.setState({
      projectData: Immutable.Map(),
      showWindow: false
    });
  }

  createProject(model) {
    if (typeof model.projectId !== 'undefined' && model.projectId !== false) {
      this.props.dispatch(TaskActions.editProject({
        projectId: model.projectId,
        title: model.title,
        departments: model.departments,
        teams: model.teams,
        agents: model.agents
      }));
    } else {
      this.props.dispatch(TaskActions.createProject({
        title: model.title,
        departments: model.departments,
        teams: model.teams,
        agents: model.agents
      }));
    }

    model.id = model.projectId;
    delete model.projectId;

    // This doesn't work.
    this.props.dispatch(updateProject('updateProjectDetails', [model], undefined));
    this.props.dispatch(loadAllProjects('all'));
  }

  render() {
    const {projectList, createdProject, departmentList, agentList, teamList} = this.props;
    // Workaround to bind toggleWindow to every edit link
    const _this = this;
    const agents = [];
    const teams = [];
    const departments = [];

    if (typeof departmentList !== 'undefined' && departmentList !== null) {
      departmentList.map((object) => {
        departments.push({value: object.get('id'), label: object.get('title'), name: object.get('title')});
      });
    }

    if (typeof teamList !== 'undefined' && teamList !== null) {
      teamList.map((object) => {
        teams.push({value: object.get('id'), label: object.get('name'), name: object.get('name')});
      });
    }

    if (typeof agentList !== 'undefined' && agentList !== null) {
      agentList.map((object) => {
        const label = (<span>
                      <span style={{position: 'relative'}}><PersonAvatar person={object} size="16" /></span>
                      {object.get('name')}
                    </span>
        );
        agents.push({value: object.get('id'), label: label, name: object.get('name')});
      });
    }

    return (<section className="sidebar-list tasks-nav-projects">
        <div>
          <Positioned isOpen={this.state.showWindow}>
            <ProjectCreateHover
              position={this.state.position}
              createProject={this.createProject.bind(this)}
              createdProject={createdProject}
              agentList={agents}
              teamList={teams}
              departmentList={departments}
              projectData={this.state.projectData}
              closeWindow={this.closeWindow.bind(this)}
              user={this.props.user}
            />
          </Positioned>
        </div>
        <div className="list-sidebar-title">Projects <a href="#" onClick={this.toggleWindow.bind(this, {})}><i className="fa fa-plus"/></a></div>
        <ul>{projectList ? projectList.map((object) => {
          return (<TaskNavItemProject key={object.get('id')}
                                      project={object}
                                      filterTasks={_this.props.filterTasks.bind(this)}
                                      toggleWindow={_this.toggleWindow.bind(_this)}
                                      dispatch={_this.props.dispatch.bind(_this)}
                />);
        }) : ''}
        </ul>
      </section>);
  }
}
