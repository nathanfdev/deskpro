import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { currentSortSelector } from '../../Selectors/list';
import { allProjectsSelector } from '../../RecordStores/Selectors/projectSelectors';
import { allTaskListsSelector } from '../../RecordStores/Selectors/taskListSelectors';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';
import { dateGroupsBuilder, recordGroupsBuilder, groupCollection } from 'Util/ListGroup';

@connect(state => ({
  sort: currentSortSelector(state),
  lists: allTaskListsSelector(state),
  projects: allProjectsSelector(state),
  agents: agentsSelector(state),
  agentTeams: agentTeamsSelector(state),
  departments: allDepartmentsSelector(state)
}))
export class ListGroupContainer extends React.Component {

  static propTypes = {
    tasks: PropTypes.object,
    children: PropTypes.node.isRequired
  };

  getGroups() {
    const { sort, lists, projects, agents, agentTeams, departments } = this.props;
    const groups = [];

    switch (sort) {
      case 'project':
        recordGroupsBuilder(groups, projects, 'title', sort, 'None');
        break;
      case 'date_due':
        dateGroupsBuilder(groups, sort, [
          'hour',
          'today',
          'tomorrow',
          'thisWeek',
          'nextWeek',
          'thisMonth',
          'nextMonth',
          'thisYear',
          'other'
        ]);
        break;
      case 'date_done':
      case 'date_created':
        dateGroupsBuilder(groups, sort, [
          'hour',
          'today',
          'yesterday',
          'thisWeek',
          'lastWeek',
          'thisMonth',
          'lastMonth',
          'thisYear',
          'older'
        ]);
        break;
      case 'assignee':
        recordGroupsBuilder(groups, departments, 'title', 'department');
        recordGroupsBuilder(groups, agentTeams, 'name', 'team');
        recordGroupsBuilder(groups, agents, 'name', 'agent', 'None');
        break;
      default:
      case 'list':
        recordGroupsBuilder(groups, lists, 'title', 'list', 'Tasks not in any list');
        break;
    }

    return groups;
  }

  render() {
    const { tasks, children } = this.props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,
      tasks: tasks,
      taskGroups: groupCollection(this.getGroups(), tasks)
    });
  }
}
