import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';
import { async, composeHandlers } from 'DeskPRO/Component/Ampliflux/reducers/handlers';

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
  // [TaskListActions.loadTaskList]: composeHandlers(
  //   async({
  //     success: (state, payload) => {
  //       return state.merge({
  //         taskFrameList: (typeof payload !== 'undefined' && typeof payload.data !== 'undefined') ? payload.data : {},
  //         taskFrameSource: (typeof payload !== 'undefined' && typeof payload.source !== 'undefined') ? payload.source : {},
  //         taskFrameProjects: (typeof payload !== 'undefined' && typeof payload.projects !== 'undefined') ? payload.projects : {},
  //         taskFrameLinks: (typeof payload !== 'undefined' && typeof payload.linked_items !== 'undefined') ? payload.linked_items : {},
  //         taskFrameAgents: (typeof payload !== 'undefined' && typeof payload.people !== 'undefined') ? payload.people : {},
  //         taskFrameTeams: (typeof payload !== 'undefined' && typeof payload.teams !== 'undefined') ? payload.teams : {},
  //         taskFrameDepartments: (typeof payload !== 'undefined' && typeof payload.departments !== 'undefined') ? payload.departments : {},
  //         taskFrameTickets: (typeof payload !== 'undefined' && typeof payload.tickets !== 'undefined') ? payload.tickets : {},
  //         taskFrameMeta: (typeof payload !== 'undefined' && typeof payload.meta !== 'undefined') ? payload.meta : {},
  //       });
  //     }
  //   })
  // )
});
