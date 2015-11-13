import { createReducer } from 'Ampliflux';
import { setFullPayload, setValue, async, togglePayloadInCollection } from 'Ampliflux/reducers/handlers';
import * as ListActions from '../Actions/listActions';
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
  [ListActions.setListParamsNav]: setFullPayload('listParams.nav'),
  [ListActions.setListParamsFilters]: setFullPayload('listParams.filters'),
  [ListActions.toggleCardFieldVisibility]: togglePayloadInCollection(`visibleFields.${constants.VIEW_MODE_CARD}`),
  [ListActions.toggleTableFieldVisibility]: togglePayloadInCollection(`visibleFields.${constants.VIEW_MODE_TABLE}`),
  [ListActions.toggleKanbanFieldVisibility]: togglePayloadInCollection(`visibleFields.${constants.VIEW_MODE_KANBAN}`),
  [ListActions.toggleCalendarFieldVisibility]: togglePayloadInCollection(`visibleFields.${constants.VIEW_MODE_CALENDAR}`),
  [ListActions.loadList]: async({
    success: setFullPayload('elements'),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  })
});
