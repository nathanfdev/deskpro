import { createReducer } from 'Ampliflux';
import { setFullPayload, setValue, async } from 'Ampliflux/reducers/handlers';
import * as TasksActions from '../Actions/tasksActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {
  listParams: {
    filter: null,
    sort: null,
    order: null
  },
  visibleFields: {
    [constants.VIEW_MODE_CARD]: [],
    [constants.VIEW_MODE_TABLE]: [],
    [constants.VIEW_MODE_KANBAN]: [],
    [constants.VIEW_MODE_CALENDAR]: []
  },
  elements: {},
  view: 'card',
  async: {
    done: null
  }
};

const toggleVisibleFields = (state, viewMode, field) => {
  let fields = state.getIn(['visibleFields', viewMode]);
  fields = fields.includes(field) ? fields.delete(fields.indexOf(field)) : fields.push(field);

  return state.setIn(['visibleFields', viewMode], fields);
};

export default createReducer(initialState, {
  [TasksActions.setListParamsFilter]: setFullPayload('listParams.filter'),
  [TasksActions.setListParamsSort]: setFullPayload('listParams.sort'),
  [TasksActions.setListParamsOrder]: setFullPayload('listParams.order'),
  [TasksActions.toggleCardFieldVisibility]: (state, payload) => {
    return toggleVisibleFields(state, constants.VIEW_MODE_CARD, payload);
  },
  [TasksActions.toggleTableFieldVisibility]: (state, payload) => {
    return toggleVisibleFields(state, constants.VIEW_MODE_TABLE, payload);
  },
  [TasksActions.toggleKanbanFieldVisibility]: (state, payload) => {
    return toggleVisibleFields(state, constants.VIEW_MODE_KANBAN, payload);
  },
  [TasksActions.toggleCalendarFieldVisibility]: (state, payload) => {
    return toggleVisibleFields(state, constants.VIEW_MODE_CALENDAR, payload);
  },
  [TasksActions.loadList]: async({
    success: setFullPayload('elements'),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  })
});
