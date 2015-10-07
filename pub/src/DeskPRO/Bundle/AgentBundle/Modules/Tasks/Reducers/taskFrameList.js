import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  taskFrameList: null,
  taskFrameSource: null,
  taskFrameProjects: null,
  taskFrameLinks: null,
  taskFrameAgents: null,
  taskFrameTeams: null,
  taskFrameDepartments: null,
  taskFrameTickets: null,
  taskFrameMeta: null
};

export default createReducer(initialState, {
  [TaskListActions.loadTaskList]: (state, payload) => {
    const projects = (typeof payload.projects !== 'undefined' && typeof payload.projects !== 'undefined') ? payload.projects : [];
    const links = (typeof payload.linked_items !== 'undefined') ? payload.linked_items : [];
    const agents = (typeof payload.people !== 'undefined') ? payload.people : [];
    const teams = (typeof payload.teams !== 'undefined') ? payload.teams : [];
    const departments = (typeof payload.departments !== 'undefined') ? payload.departments : [];
    const tickets = (typeof payload.tickets !== 'undefined') ? payload.tickets : [];

    return state.merge({
      taskFrameList: payload.data,
      taskFrameSource: payload.source,
      taskFrameProjects: projects,
      taskFrameLinks: links,
      taskFrameAgents: agents,
      taskFrameTeams: teams,
      taskFrameDepartments: departments,
      taskFrameTickets: tickets,
      taskFrameMeta: payload.meta
    });
  }
});
