import React from 'react';

import * as TaskActions from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/Actions/TaskListActions';

import { connect } from 'react-redux';
import { createTicketRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/RecordStores/Selectors/ticketSelectors';
import { allProjectsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/projectSelectors';
import { createTaskListRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/taskListSelectors';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';
import { loadTickets } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/RecordStores/Actions/ticketActions';
import TaskGrouping from 'DeskPRO/Bundle/AgentBundle/Services/TaskGrouping';

import Immutable from 'immutable';

const requestId = 'taskListFrame';
const ticketsSelector = createTicketRequestSelectors(requestId);
const listsSelector = createTaskListRequestSelectors(requestId);

@connect(state => ({
  agents: agentsSelector(state),
  departments: allDepartmentsSelector(state),
  lists: listsSelector.recordsSel(state),
  projects: allProjectsSelector(state),
  teams: agentTeamsSelector(state),
  tickets: ticketsSelector.recordsSel(state),
  ticketsStatus: ticketsSelector.statusSel(state)
}))
export default class TaskViewConnector extends React.Component {
  static propTypes = {
    agents: React.PropTypes.object,
    children: React.PropTypes.any,
    departments: React.PropTypes.object,
    direction: React.PropTypes.string,
    dispatch: React.PropTypes.func,
    lists: React.PropTypes.object,
    order: React.PropTypes.string,
    projects: React.PropTypes.object,
    tasks: React.PropTypes.object,
    teams: React.PropTypes.object,
    tickets: React.PropTypes.object,
    ticketsStatus: React.PropTypes.object
  };

  componentDidMount() {
    if (this.props.projectId) {
      this.props.dispatch(TaskActions.loadLists(this.props.projectId));
    } else {
      console.log('No project found');
    }

    Promise.all([
      this.loadTaskLinkedTickets()
    ]).then(() => {
      console.log('Done');
      // this.setState({
      //   loadedAll: true
      // });
    });
  }

  loadTaskLinkedTickets() {
    if (this.props.tasks && this.props.tasks.size > 0 && !this.props.ticketsStatus.get('isDone')) {
      const linkedTicketIds = Immutable.List();
      const linkedToMerge = [];

      this.props.tasks.map(task => {
        if (task.has('linked_tickets') && task.get('linked_tickets').size > 0) {
          const newLinkedTicketIds = task.get('linked_tickets').toList();
          linkedToMerge.push(newLinkedTicketIds);
        }
      });

      const linkedToLoad = linkedTicketIds.merge(...linkedToMerge);
      const loadArray = linkedToLoad.toArray();

      if (linkedToLoad.size > 0) {
        return this.props.dispatch(loadTickets(requestId, loadArray));
      }
    }
  }

  massEdit(data) {
    const source = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameSource') : null;

    this.props.dispatch(TaskActions.massEditTasks(
      data,
      source
    ));
  }

  render() {
    const tasks = [];
    const sortedTasks = [];
    const grouping = new TaskGrouping(this.props.projects, this.props.departments, this.props.teams, this.props.agents, this.props.lists, this.props.tickets);
    const columnField = this.props.order;
    const rawGroupings = grouping.getRawGroupings(columnField, this.props.direction);

    // Split tasks up into the appropriate kanban columns
    if (this.props.tasks && this.props.tasks.size > 0) {
      this.props.tasks.forEach((object) => {
        const columnId = grouping.getGroup(object, columnField);

        if (typeof tasks[columnId] === 'undefined') {
          tasks[columnId] = [];
        }

        tasks[columnId].push(object);
      });

      const updateField = rawGroupings[0].updateField;

      // Sort within the groups
      Object.keys(tasks).map((key) => {
        sortedTasks[key] = tasks[key].sort((first, second) => {
          if (first.get(updateField) === second.get(updateField)) {
            return 0;
          }

          if (this.props.direction === 'desc') {
            return first.get(updateField) < second.get(updateField) ? 1 : -1;
          }

          return first.get(updateField) > second.get(updateField) ? 1 : -1;
        });
      });
    }

    const childProps = Object.assign({
      agents: this.props.agents,
      departments: this.props.departments,
      direction: this.props.direction,
      dispatch: this.props.dispatch,
      groupedTasks: sortedTasks,
      massEdit: this.props.massEdit,
      projects: this.props.projects,
      order: this.props.order,
      rawGroupings: rawGroupings,
      tasks: this.props.tasks,
      teams: this.props.teams,
      tickets: this.props.tickets,
      ticketsStatus: this.props.ticketsStatus
    }, this.props.children.props);
    return React.cloneElement(this.props.children, childProps);
  }
}
