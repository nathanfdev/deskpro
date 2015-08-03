import React from "react";
import { connect } from 'redux/react';

import * as TaskActions from "../Actions/TaskListActions";
import TaskNavGroups from "../Components/TaskNavGroups";
import TaskNavProjects from "../Components/TaskNavProjects";
import TaskNavPeople from "../Components/TaskNavPeople";
import TaskNavLabels from "../Components/TaskNavLabels";
import $ from "jquery";

@connect(state => ({
  taskList: state.taskList,
  projectList: state.projectList,
  agentList:state.agentList,
  labelList:state.labelList,
  teamList:state.teamList,
  departmentList:state.departmentList,
  user: state.user,
  createdProject: state.createdProject
}))
export default class TasksNavFrame extends React.Component {
  constructor(props) {
    super(props);

    const { dispatch } = this.props;

    dispatch(TaskActions.loadTasks());
    dispatch(TaskActions.loadMyTasks());
    dispatch(TaskActions.loadProjects());
    dispatch(TaskActions.loadTeamTasks());
    dispatch(TaskActions.loadDepartmentTasks());
    dispatch(TaskActions.loadDelegatedTasks());
    dispatch(TaskActions.loadUnassignedTasks());
    dispatch(TaskActions.loadDepartments());
    dispatch(TaskActions.loadAgents());
    dispatch(TaskActions.loadTeams());
    dispatch(TaskActions.loadLabels());
  }

  switchTaskList(identifier, event) {
    this.props.dispatch(TaskActions.loadTaskList(identifier));
    $('.sidebar-list a.item, .sidebar-list a.item-label').removeClass('active');
    $(event.target).closest('a').addClass('active');
  }

  render() {
    const { taskList, projectList, agentList, labelList, departmentList, teamList, createdProject } = this.props;

    return (
      <section className="task-nav-frame dp-nav-frame">
        <div className="sidebar-wrapper" id="sidebar-wrapper">
          <a className="collapse-button" href="#" onclick="resizePanels('hide_filters');"><i
            className="fa fa-angle-right"/></a>

          <span className="collapse-controls" onclick="resizePanels('hide_filters');">
            <span className="disc"/>
            <span className="disc"/>
            <i className="fa fa-caret-right"/>
            <span className="disc"/>
            <span className="disc"/>
          </span>
          <aside className="sidebar has-tabs" id="sidebar">

            <div className="sidebar-title">
              <span className="sidebar-type-icon">
                <i className="fa fa-check-square-o"/>
                <span className="help"><i className="fa fa-question"/></span>
              </span>

              <h1>Tasks</h1>
              <hr/>
              <a href="#" className="slider-control"></a>
            </div>

            <div className="sidebar-list sidebar-list-filters">
              <TaskNavGroups taskList={taskList} switchTaskList={this.switchTaskList.bind(this)} />

              <TaskNavProjects projectList={projectList} agentList={agentList} teamList={teamList} departmentList={departmentList} createdProject={createdProject} switchTaskList={this.switchTaskList.bind(this)} />

              <TaskNavPeople agentList={agentList} switchTaskList={this.switchTaskList.bind(this)} />

              <TaskNavLabels labelList={labelList} switchTaskList={this.switchTaskList.bind(this)} />
            </div>
          </aside>
        </div>
      </section>
    );
  }
}
