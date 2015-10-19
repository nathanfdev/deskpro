import React from 'react';

import { connect } from 'react-redux';
import { createTicketRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/RecordStores/Selectors/ticketSelectors';
import { allProjectsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/RecordStores/Selectors/projectSelectors';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';
import { loadTickets } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/RecordStores/Actions/ticketActions';
import TaskGrouping from 'DeskPRO/Bundle/AgentBundle/Services/TaskGrouping';

import Immutable from 'immutable';

const requestId = 'taskListFrame';
const ticketsSelector = createTicketRequestSelectors(requestId);

@connect(state => ({
  projects: allProjectsSelector(state),
  agents: agentsSelector(state),
  teams: agentTeamsSelector(state),
  departments: allDepartmentsSelector(state),
  tickets: ticketsSelector.recordsSel(state),
  ticketsStatus: ticketsSelector.statusSel(state)
}))
export default class TaskViewConnector extends React.Component {
  static propTypes = {
    agents: React.PropTypes.object,
    children: React.PropTypes.any,
    departments: React.PropTypes.object,
    dispatch: React.PropTypes.func,
    projects: React.PropTypes.object,
    order: React.PropTypes.string,
    tasks: React.PropTypes.object,
    teams: React.PropTypes.object,
    tickets: React.PropTypes.object,
    ticketsStatus: React.PropTypes.object
  };

  componentDidMount() {
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

  render() {
    const tasks = [];
    const grouping = new TaskGrouping(this.props.projects, this.props.departments, this.props.teams, this.props.agents, {}, this.props.tickets);
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
    }

    const childProps = Object.assign({
      agents: this.props.agents,
      departments: this.props.departments,
      dispatch: this.props.dispatch,
      groupedTasks: tasks,
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
