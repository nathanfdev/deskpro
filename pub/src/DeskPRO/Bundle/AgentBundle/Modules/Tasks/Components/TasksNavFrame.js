import React from 'react';
import { connect } from 'react-redux';

import * as TaskActions from '../Actions/TaskListActions';
import TaskNavGroups from '../Components/TaskNavGroups';
import TaskNavProjects from '../Components/TaskNavProjects';
import TaskNavPeople from '../Components/TaskNavPeople';
import TaskNavLabels from '../Components/TaskNavLabels';
import { NavFrameHeader, NavFrame } from '../../Common/Components/NavFrame/index';
import $ from 'jquery';

import { loadProjects } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/RecordStores/Actions/taskActions';
import { createProjectRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/RecordStores/Selectors/projectSelectors';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';

const projectRequestId = 'projectNavView';
const projectSel = createProjectRequestSelectors(projectRequestId);

@connect(state => ({
  taskList: state.Tasks.taskList,
  projectList: state.Tasks.projectList,
  agentList: state.Tasks.agentList,
  labelList: state.Tasks.labelList,
  teamList: state.Tasks.teamList,
  departmentList: state.Tasks.departmentList,
  user: meSelector(state),
  createdProject: state.Tasks.createdProject,
  dpWindow: state.Application.dpWindow,
  projects: projectSel.recordsSel(state),
  status: projectSel.statusSel(state)
}))
export default class TasksNavFrame extends React.Component {
  static propTypes = {
    taskList: React.PropTypes.object,
    projectList: React.PropTypes.object,
    agentList: React.PropTypes.object,
    labelList: React.PropTypes.object,
    departmentList: React.PropTypes.object,
    teamList: React.PropTypes.object,
    createdProject: React.PropTypes.object,
    dpWindow: React.PropTypes.object,
    user: React.PropTypes.object,
    dispatch: React.PropTypes.func,
    children: React.PropTypes.any
  }

  constructor(props) {
    super(props);

    const { status, dispatch } = this.props;

    dispatch(loadProjects(projectRequestId, [1,2,3]));

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
            teamList, createdProject, dpWindow, dispatch, projects, status } = this.props;

    // if (!status.get('isLoading')) {
      console.log('Projects');
      console.log(projects.toJS());
    // }

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <div part="inner">
          <NavFrameHeader icon="icon-dp-streamline-check-circle-2">
            Tasks
          </NavFrameHeader>

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
