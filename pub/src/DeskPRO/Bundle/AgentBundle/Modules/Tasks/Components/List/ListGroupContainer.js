import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { currentSortSelector } from '../../Selectors/list';
import { allProjectsSelector } from '../../RecordStores/Selectors/projectSelectors';
import { allTaskListsSelector } from '../../RecordStores/Selectors/taskListSelectors';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';
import { groupCollection } from 'Util/ListGroup';

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

  render() {
    const { sort, lists, projects, agents, agentTeams, departments } = this.props;
    const { tasks, children } = this.props;
    const childProps = children.props;

    const groupConfig = {
      groupKey: sort,
      options: {
        project: {
          type: 'record',
          records: projects,
          titleField: 'title',
          refField: 'project',
          emptyGroup: 'None'
        },
        date_due: {
          type: 'date',
          refField: 'date_due',
          dateGroupKeys: 'future'
        },
        date_done: {
          type: 'date',
          refField: 'date_done',
          dateGroupKeys: 'past'
        },
        date_created: {
          type: 'date',
          refField: 'date_done',
          dateGroupKeys: 'past'
        },
        assignee: {
          type: 'record',
          emptyGroup: 'None',
          records: [
            {
              records: departments,
              titleField: 'title',
              refField: 'departments'
            },
            {
              records: agentTeams,
              titleField: 'name',
              refField: 'teams'
            },
            {
              records: agents,
              titleField: 'name',
              refField: 'agents'
            }
          ]
        },
        list: {
          type: 'record',
          records: lists,
          titleField: 'title',
          refField: 'list',
          emptyGroup: 'Tasks not in any list'
        }
      }
    };

    return React.cloneElement(children, {
      ...childProps,

      tasks: tasks,
      taskGroups: groupCollection(groupConfig, tasks)
    });
  }
}
