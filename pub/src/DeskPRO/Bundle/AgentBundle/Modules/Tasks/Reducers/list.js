import { createReducer } from 'Ampliflux';
import { setFullPayload, setValue, async } from 'Ampliflux/reducers/handlers';
import * as ListActions from '../Actions/listActions';
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
  [ListActions.setListParamsFilter]: setFullPayload('listParams.filter'),
  [ListActions.setListParamsSort]: setFullPayload('listParams.sort'),
  [ListActions.setListParamsOrder]: setFullPayload('listParams.order'),
  [ListActions.toggleCardFieldVisibility]: (state, payload) => {
    return toggleVisibleFields(state, constants.VIEW_MODE_CARD, payload);
  },
  [ListActions.toggleTableFieldVisibility]: (state, payload) => {
    return toggleVisibleFields(state, constants.VIEW_MODE_TABLE, payload);
  },
  [ListActions.toggleKanbanFieldVisibility]: (state, payload) => {
    return toggleVisibleFields(state, constants.VIEW_MODE_KANBAN, payload);
  },
  [ListActions.toggleCalendarFieldVisibility]: (state, payload) => {
    return toggleVisibleFields(state, constants.VIEW_MODE_CALENDAR, payload);
  },
  [ListActions.loadList]: async({
    success: setFullPayload('elements'),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  })
});
