import { createReducer } from 'Ampliflux';
import { setFullPayload, setValue, async, togglePayloadInCollection } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/listActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {
  listParams: {
    nav: null,
    filters: {}
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

export default createReducer(initialState, {
  [actions.setListParamsNav]: setFullPayload('listParams.nav'),
  [actions.setListParamsFilters]: setFullPayload('listParams.filters'),
  [actions.toggleCardFieldVisibility]: togglePayloadInCollection(`visibleFields.${constants.VIEW_MODE_CARD}`),
  [actions.toggleTableFieldVisibility]: togglePayloadInCollection(`visibleFields.${constants.VIEW_MODE_TABLE}`),
  [actions.toggleKanbanFieldVisibility]: togglePayloadInCollection(`visibleFields.${constants.VIEW_MODE_KANBAN}`),
  [actions.toggleCalendarFieldVisibility]: togglePayloadInCollection(`visibleFields.${constants.VIEW_MODE_CALENDAR}`),
  [actions.unload]: setValue('elements', []),
  [actions.loadList]: async({
    success: setFullPayload('elements'),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  })
});
