import React from "react";
import { connect } from 'redux/react';

import * as TaskActions from "../Actions/TaskListActions";
import * as AppActions from "../../Application/Actions/AppActions";
import TaskNavGroups from "../Components/TaskNavGroups";
import TaskNavProjects from "../Components/TaskNavProjects";
import TaskNavPeople from "../Components/TaskNavPeople";
import TaskNavLabels from "../Components/TaskNavLabels";
import { NavFrameHeader, NavFrame } from "../../Application/Components/NavFrame/index";
import $ from "jquery";

@connect(state => ({
  taskList: state.taskList,
  projectList: state.projectList,
  agentList: state.agentList,
  labelList: state.labelList,
  teamList: state.teamList,
  departmentList: state.departmentList,
  user: state.user,
  createdProject: state.createdProject,
  dp_window: state.dp_window
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

  filterTasks(filter, event) {
    this.props.dispatch(TaskActions.setFilter(filter));
    $('.sidebar-list a.item, .sidebar-list a.item-label').removeClass('active');
    $(event.target).closest('a').addClass('active');
  }

  render() {
    const { taskList, projectList, agentList, labelList, departmentList,
            teamList, createdProject, dp_window, dispatch } = this.props;

    const className = dp_window.collapseNav ? "sidebar-wrapper sidebar-collapsed" : "sidebar-wrapper";
    const expandNav = () => dispatch(AppActions.expandNav());

    return (
      <NavFrame dispatch={dispatch.bind(this)} dp_window={dp_window}>
        <div part="inner">
          <NavFrameHeader dispatch={dispatch.bind(this)} icon="fa-check-square-o">Tasks</NavFrameHeader>

          <div className="sidebar-list sidebar-list-filters">
            <TaskNavGroups taskList={taskList} filterTasks={this.filterTasks.bind(this)} />

            <TaskNavProjects projectList={projectList}
                             agentList={agentList}
                             teamList={teamList}
                             departmentList={departmentList}
                             createdProject={createdProject}
                             filterTasks={this.filterTasks.bind(this)}
                             user={this.props.user}
              />

            <TaskNavPeople agentList={agentList} filterTasks={this.filterTasks.bind(this)} />

            <TaskNavLabels labelList={labelList} filterTasks={this.filterTasks.bind(this)} />
          </div>
        </div>
      </NavFrame>
    );
  }
}
