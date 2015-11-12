import { createSelector } from 'reselect';
import { createTaskRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/taskSelectors';
import { ticketNamesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/ticketSelectors';
import Immutable from 'immutable';

export const taskListSelector = createSelector(
  createTaskRequestSelectors('loadTaskList').recordsSel,
  ticketNamesSelector,
  (taskList, tickets) => {
    const tasks = [];

    taskList.forEach(task => {
      const attachedTickets = task.get('linked_tickets');

      const ticketData = attachedTickets.map((ticketId) => {
        return tickets[ticketId];
      });

      tasks.push(Immutable.Map({
        id: task.get('id'),
        title: task.get('title'),
        is_done: task.get('is_done'),
        percent_complete: task.get('percent_complete'),
        date_created: task.get('date_created'),
        task_type: task.get('task_type'),
        date_due: task.get('date_due'),
        date_event_start: task.get('date_event_start'),
        date_event_end: task.get('date_event_end'),
        creator: task.get('creator'),
        visibility: task.get('visibility'),
        project: task.get('project'),
        list: task.get('list'),
        urgency: task.get('urgency'),
        linked_items: task.get('linked_items'),
        date_done: task.get('date_done'),
        display_order: task.get('display_order'),
        departments: task.get('departments'),
        teams: task.get('teams'),
        agents: task.get('agents'),
        labels: task.get('labels'),
        linked_tickets: task.get('linked_tickets'),
        comment_count: task.get('comment_count'),
        subtasks_total: task.get('subtasks_total'),
        subtasks_done: task.get('subtasks_done'),
        tickets: ticketData || []
      }));
    });

    return Immutable.Set(tasks);
  }
);

export const statusFilteredTasksSelector = createSelector(
  createTaskRequestSelectors('loadTaskList').statusSel,
  tasks => tasks
);
