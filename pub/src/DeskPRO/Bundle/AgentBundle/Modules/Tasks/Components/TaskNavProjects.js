import React from 'react';
import ProjectCreateHover from '../Components/ProjectCreateHover';
import TaskNavItemProject from '../Components/TaskNavItemProject';
import ComponentRootWrapper from 'DeskPRO/Component/ComponentRootWrapper';
import { connect } from 'react-redux';
import $ from 'jquery';

import * as TaskActions from '../Actions/TaskListActions';

@connect(state => ({
  failedProject: state.failedProject
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
      projectData: {},
      position: {}
    };
  }

  toggleWindow(project = {}) {
    this.setState({
      projectData: {}
    });

    if (this.state.showWindow === false) {
      this.setState({
        projectData: project
      });
    }

    let target = $(event.target).closest('li.project-list-item');
    let modifier = 12;

    if (typeof target[0] === 'undefined') {
      target = $(event.target).closest('.list-sidebar-title');
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
      projectData: {},
      showWindow: false
    });
  }

  createProject(model) {
    if (typeof model.projectId !== 'undefined') {
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
  }

  render() {
    const {projectList, createdProject} = this.props;
    // Workaround to bind toggleWindow to every edit link
    const _this = this;
    const agents = [];
    const teams = [];
    const departments = [];

    const departmentList = (this.props.departmentList && typeof this.props.departmentList.get === 'function') ? this.props.departmentList.get('departmentList', []) : [];
    const teamList = (this.props.teamList && typeof this.props.teamList.get === 'function') ? this.props.teamList.get('teamList', []) : [];
    const agentList = (this.props.agentList && typeof this.props.agentList.get === 'function') ? this.props.agentList.get('agentList', []) : [];

    if (typeof departmentList !== 'undefined' && departmentList !== null) {
      departmentList.forEach((object) => {
        departments.push({value: object.id, label: object.title, name: object.title});
      });
    }

    if (typeof teamList !== 'undefined' && teamList !== null) {
      teamList.forEach((object) => {
        teams.push({value: object.id, label: object.name, name: object.name});
      });
    }

    if (typeof agentList !== 'undefined' && agentList !== null) {
      agentList.forEach((object) => {
        const label = (<span>
                      {object.picture_blob ? <span className="chat-avatar" style={{backgroundImage: 'url(' + object.picture_blob.download_url + ')'}}/> : '' }
                      {object.name}
                    </span>
        );
        agents.push({value: object.id, label: label, name: object.name});
      });
    }

    const projects = projectList.getIn(['projects'], false);

    return (<section className="sidebar-list tasks-nav-projects">
        <div>
          <ComponentRootWrapper open={this.state.showWindow}>
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
          </ComponentRootWrapper>
        </div>
        <div className="list-sidebar-title">Projects <a href="#" onClick={this.toggleWindow.bind(this)}><i className="fa fa-plus"/></a></div>
        <ul>{projects.projectList ? projects.projectList.map((object) => {
          return (<TaskNavItemProject key={object.id}
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
