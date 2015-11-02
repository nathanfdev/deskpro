import React from 'react';
import { connect } from 'react-redux';

import * as TaskActions from '../Actions/TaskListActions';
import TaskNavGroups from '../Components/TaskNavGroups';
import TaskNavProjects from '../Components/TaskNavProjects';
import TaskNavPeople from '../Components/TaskNavPeople';
import TaskNavLabels from '../Components/TaskNavLabels';
import { NavFrameHeader, NavFrame } from '../../Common/Components/NavFrame/index';
import jQuery from 'jquery';

import { loadAllAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions';
import { loadAllDepartments } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';
import { loadAllProjects } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Actions/projectActions';
import { loadAllTaskLabels } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Actions/taskLabelActions';

import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';
import { allProjectsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/projectSelectors';
import { allTaskLabelsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/taskLabelSelectors';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';

// const projectRequestId = 'projectNavView';

@connect(state => ({
  taskList: state.OldTasks.taskList,
  agentList: state.OldTasks.agentList,
  labelList: state.OldTasks.labelList,
  teamList: state.OldTasks.teamList,
  departmentList: state.OldTasks.departmentList,
  user: meSelector(state),
  createdProject: state.OldTasks.createdProject,
  dpWindow: state.Application.dpWindow,
  projects: allProjectsSelector(state),
  agents: agentsSelector(state),
  agentTeams: agentTeamsSelector(state),
  departments: allDepartmentsSelector(state),
  labels: allTaskLabelsSelector(state)
}))
export default class TasksNavFrame extends React.Component {
  static propTypes = {
    taskList: React.PropTypes.object,
    agentList: React.PropTypes.object,
    labelList: React.PropTypes.object,
    departmentList: React.PropTypes.object,
    teamList: React.PropTypes.object,
    createdProject: React.PropTypes.object,
    dpWindow: React.PropTypes.object,
    user: React.PropTypes.object,
    dispatch: React.PropTypes.func,
    children: React.PropTypes.any,
    projects: React.PropTypes.object,
    tasks: React.PropTypes.object,
    agents: React.PropTypes.object,
    agentTeams: React.PropTypes.object,
    departments: React.PropTypes.object,
    labels: React.PropTypes.object
  }

  constructor(props) {
    super(props);

    const { dispatch } = this.props;

    dispatch(loadAllProjects());
    dispatch(loadAllAgentTeams());
    dispatch(loadAllDepartments());
    dispatch(loadAllTaskLabels());

    dispatch(TaskActions.loadTasks());
    dispatch(TaskActions.loadMyTasks());
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
    jQuery('.sidebar-list a.item, .sidebar-list a.item-label').removeClass('active');
    jQuery(event.target).closest('a').addClass('active');
  }

  render() {
    const { taskList, agents, labels, departments,
            agentTeams, createdProject, dpWindow, dispatch, projects } = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <div part="inner">
          <NavFrameHeader icon="icon-dp-streamline-check-circle-2">
            Tasks
          </NavFrameHeader>

          <div className="sidebar-list sidebar-list-filters">
            <TaskNavGroups taskList={taskList} filterTasks={this.filterTasks.bind(this)} />

            <TaskNavProjects projectList={projects}
                             agentList={agents}
                             teamList={agentTeams}
                             departmentList={departments}
                             createdProject={createdProject}
                             filterTasks={this.filterTasks.bind(this)}
                             user={this.props.user}
              />

            <TaskNavPeople agentList={agents} filterTasks={this.filterTasks.bind(this)} />

            <TaskNavLabels labelList={labels} filterTasks={this.filterTasks.bind(this)} />
          </div>
        </div>
      </NavFrame>
    );
  }
}
